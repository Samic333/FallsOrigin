import { Router, Request, Response } from 'express';
import { body } from 'express-validator';
import { authService } from '../services/auth.service';
import { db } from '../config/database';
import { authenticateToken } from '../middleware/auth';
import { authLimiter } from '../middleware/rate-limit';
import { validate } from '../middleware/validation';
import { logger } from '../utils/logger';

const router = Router();

// Admin login
router.post(
    '/login',
    authLimiter,
    validate([
        body('username').trim().notEmpty().withMessage('Username is required'),
        body('password').notEmpty().withMessage('Password is required'),
    ]),
    async (req: Request, res: Response) => {
        try {
            const { username, password } = req.body;
            const token = await authService.authenticateAdmin(username, password);
            res.json({ token });
        } catch (error) {
            logger.warn('Admin login failed', { username: req.body.username });
            res.status(401).json({ error: 'Invalid credentials' });
        }
    }
);

// Get all orders (admin only)
router.get('/orders', authenticateToken, async (_req: any, res: any) => {
    try {
        const orders = await db.query(
            'SELECT * FROM orders ORDER BY created_at DESC'
        );
        res.json(orders);
    } catch (error) {
        logger.error('Failed to fetch orders', { error: (error as Error).message });
        res.status(500).json({ error: 'Failed to fetch orders' });
    }
});

// Get all contact messages (admin only)
router.get('/messages', authenticateToken, async (_req: any, res: any) => {
    try {
        const messages = await db.query(
            'SELECT * FROM contact_messages ORDER BY created_at DESC'
        );
        res.json(messages);
    } catch (error) {
        logger.error('Failed to fetch messages', { error: (error as Error).message });
        res.status(500).json({ error: 'Failed to fetch messages' });
    }
});

// Mark message as read (admin only)
router.put('/messages/:id/read', authenticateToken, async (req: any, res: any) => {
    try {
        await db.query('UPDATE contact_messages SET read = true WHERE id = $1', [req.params.id]);
        res.json({ message: 'Message marked as read' });
    } catch (error) {
        logger.error('Failed to mark message as read', { error: (error as Error).message });
        res.status(500).json({ error: 'Failed to update message' });
    }
});

// Get all reviews (admin only)
router.get('/reviews', authenticateToken, async (_req: any, res: any) => {
    try {
        const reviews = await db.query(
            'SELECT * FROM reviews ORDER BY created_at DESC'
        );
        res.json(reviews);
    } catch (error) {
        logger.error('Failed to fetch reviews', { error: (error as Error).message });
        res.status(500).json({ error: 'Failed to fetch reviews' });
    }
});

// Approve review (admin only)
router.put('/reviews/:id/approve', authenticateToken, async (req: Request, res: Response) => {
    try {
        await db.query('UPDATE reviews SET status = $1 WHERE id = $2', ['approved', req.params.id]);
        logger.info('Review approved', { reviewId: req.params.id });
        res.json({ message: 'Review approved' });
    } catch (error) {
        logger.error('Failed to approve review', { error: (error as Error).message });
        res.status(500).json({ error: 'Failed to approve review' });
    }
});

// Reject review (admin only)
router.put('/reviews/:id/reject', authenticateToken, async (req: Request, res: Response) => {
    try {
        await db.query('UPDATE reviews SET status = $1 WHERE id = $2', ['rejected', req.params.id]);
        logger.info('Review rejected', { reviewId: req.params.id });
        res.json({ message: 'Review rejected' });
    } catch (error) {
        logger.error('Failed to reject review', { error: (error as Error).message });
        res.status(500).json({ error: 'Failed to reject review' });
    }
});

// Get analytics (admin only)
router.get('/analytics', authenticateToken, async (_req: any, res: any) => {
    try {
        const views = await db.query(
            "SELECT COUNT(*) as count FROM analytics_events WHERE event_type = 'page_view'"
        );
        const stats = await db.query('SELECT * FROM order_statistics');

        res.json({
            views: parseInt(views[0]?.count || '0'),
            ...stats[0],
        });
    } catch (error) {
        logger.error('Failed to fetch analytics', { error: (error as Error).message });
        res.status(500).json({ error: 'Failed to fetch analytics' });
    }
});

export default router;
