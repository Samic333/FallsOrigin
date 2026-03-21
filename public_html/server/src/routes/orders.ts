import { Router, Request, Response } from 'express';
import { body, query } from 'express-validator';
import { db } from '../config/database';
import { emailService } from '../services/email.service';
import { authenticateToken } from '../middleware/auth';
import { validate } from '../middleware/validation';
import { logger } from '../utils/logger';
import multer from 'multer';

const router = Router();
const upload = multer({ storage: multer.memoryStorage(), limits: { fileSize: 2 * 1024 * 1024 } });

// Track order by token (public)
router.get(
  '/track',
  validate([query('token').trim().notEmpty()]),
  async (req: Request, res: Response) => {
    try {
      const { token } = req.query;

      const orders = await db.query(
        `SELECT o.*, 
          (SELECT json_agg(json_build_object('status', status, 'timestamp', timestamp, 'notes', notes))
           FROM order_audit_log WHERE order_id = o.id ORDER BY timestamp ASC) as audit_log
         FROM orders o
         WHERE tracking_token = $1`,
        [token]
      );

      if (orders.length === 0) {
        res.status(404).json({ error: 'Order not found' });
        return;
      }

      res.json(orders[0]);
    } catch (error) {
      logger.error('Failed to track order', { error: (error as Error).message });
      res.status(500).json({ error: 'Failed to track order' });
    }
  }
);

// Track order by order number + email (public)
router.post(
  '/track',
  validate([
    body('orderId').trim().notEmpty(),
    body('email').isEmail(),
  ]),
  async (req: Request, res: Response) => {
    try {
      const { orderId, email } = req.body;

      const orders = await db.query(
        `SELECT o.*,
          (SELECT json_agg(json_build_object('status', status, 'timestamp', timestamp, 'notes', notes))
           FROM order_audit_log WHERE order_id = o.id ORDER BY timestamp ASC) as audit_log
         FROM orders o
         WHERE id = $1 AND email = $2`,
        [orderId, email.toLowerCase()]
      );

      if (orders.length === 0) {
        res.status(404).json({ error: 'Order not found' });
        return;
      }

      res.json(orders[0]);
    } catch (error) {
      logger.error('Failed to track order', { error: (error as Error).message });
      res.status(500).json({ error: 'Failed to track order' });
    }
  }
);

// Update order status (admin only)
router.put(
  '/:id/status',
  authenticateToken,
  validate([body('status').isIn(['Paid', 'Accepted', 'Preparing', 'Out for Delivery', 'Shipped', 'Delivered', 'Cancelled', 'Refunded'])]),
  async (req: Request, res: Response) => {
    try {
      const { status } = req.body;
      const { id } = req.params;

      await db.transaction(async (client) => {
        await client.query(
          'UPDATE orders SET status = $1 WHERE id = $2',
          [status, id]
        );

        await client.query(
          'INSERT INTO order_audit_log (order_id, status, notes) VALUES ($1, $2, $3)',
          [id, status, `Status updated to ${status}`]
        );
      });

      logger.info('Order status updated', { orderId: id, status });
      res.json({ message: 'Status updated successfully' });
    } catch (error) {
      logger.error('Failed to update order status', { error: (error as Error).message });
      res.status(500).json({ error: 'Failed to update status' });
    }
  }
);

// Set ETA for local delivery (admin only)
router.put(
  '/:id/eta',
  authenticateToken,
  validate([body('eta').trim().notEmpty()]),
  async (req: Request, res: Response) => {
    try {
      const { eta } = req.body;
      const { id } = req.params;

      const orders = await db.query(
        'SELECT email, customer_name, tracking_token FROM orders WHERE id = $1',
        [id]
      );

      if (orders.length === 0) {
        res.status(404).json({ error: 'Order not found' });
        return;
      }

      await db.query('UPDATE orders SET eta = $1 WHERE id = $2', [eta, id]);

      // Send ETA update email
      const order = orders[0];
      emailService.sendETAUpdate({
        email: order.email,
        customerName: order.customer_name,
        orderId: id,
        eta,
        trackingToken: order.tracking_token,
      }).catch((error) => {
        logger.error('Failed to send ETA email', { error: error.message, orderId: id });
      });

      logger.info('ETA set', { orderId: id, eta });
      res.json({ message: 'ETA updated successfully' });
    } catch (error) {
      logger.error('Failed to set ETA', { error: (error as Error).message });
      res.status(500).json({ error: 'Failed to set ETA' });
    }
  }
);

// Add tracking number (admin only)
router.put(
  '/:id/tracking',
  authenticateToken,
  validate([
    body('trackingNumber').trim().notEmpty(),
    body('carrier').trim().notEmpty(),
  ]),
  async (req: Request, res: Response) => {
    try {
      const { trackingNumber, carrier } = req.body;
      const { id } = req.params;

      const orders = await db.query(
        'SELECT email, customer_name, tracking_token FROM orders WHERE id = $1',
        [id]
      );

      if (orders.length === 0) {
        res.status(404).json({ error: 'Order not found' });
        return;
      }

      await db.query(
        'UPDATE orders SET tracking_number = $1, carrier = $2 WHERE id = $3',
        [trackingNumber, carrier, id]
      );

      // Send tracking email
      const order = orders[0];
      emailService.sendTrackingNumber({
        email: order.email,
        customerName: order.customer_name,
        orderId: id,
        trackingNumber,
        carrier,
        trackingToken: order.tracking_token,
      }).catch((error) => {
        logger.error('Failed to send tracking email', { error: error.message, orderId: id });
      });

      logger.info('Tracking number added', { orderId: id, trackingNumber, carrier });
      res.json({ message: 'Tracking information updated successfully' });
    } catch (error) {
      logger.error('Failed to add tracking', { error: (error as Error).message });
      res.status(500).json({ error: 'Failed to add tracking information' });
    }
  }
);

// Upload signature (admin only)
router.post(
  '/:id/signature',
  authenticateToken,
  upload.single('signature'),
  async (req: Request, res: Response) => {
    try {
      if (!req.file) {
        res.status(400).json({ error: 'No signature file provided' });
        return;
      }

      const { id } = req.params;
      const signatureBase64 = `data:${req.file.mimetype};base64,${req.file.buffer.toString('base64')}`;

      await db.query(
        'UPDATE orders SET signature_url = $1, delivered_at = CURRENT_TIMESTAMP, status = $2 WHERE id = $3',
        [signatureBase64, 'Delivered', id]
      );

      await db.query(
        'INSERT INTO order_audit_log (order_id, status, notes) VALUES ($1, $2, $3)',
        [id, 'Delivered', 'Signature captured and order marked as delivered']
      );

      logger.info('Signature uploaded', { orderId: id });
      res.json({ message: 'Signature uploaded successfully' });
    } catch (error) {
      logger.error('Failed to upload signature', { error: (error as Error).message });
      res.status(500).json({ error: 'Failed to upload signature' });
    }
  }
);

export default router;
