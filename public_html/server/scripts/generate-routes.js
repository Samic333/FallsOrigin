#!/usr/bin/env node

/**
 * Script to generate all API route files
 * Run with: node scripts/generate-routes.js
 */

const fs = require('fs');
const path = require('path');

const routesDir = path.join(__dirname, '..', 'src', 'routes');

// Ensure routes directory exists
if (!fs.existsSync(routesDir)) {
    fs.mkdirSync(routesDir, { recursive: true });
}

const routes = {
    'products.ts': `import { Router, Request, Response } from 'express';
import { body } from 'express-validator';
import { db } from '../config/database';
import { authenticateToken } from '../middleware/auth';
import { validate } from '../middleware/validation';
import { logger } from '../utils/logger';

const router = Router();

// Get all products
router.get('/', async (req: Request, res: Response) => {
  try {
    const products = await db.query(
      'SELECT * FROM products ORDER BY created_at DESC'
    );
    res.json(products);
  } catch (error) {
    logger.error('Failed to fetch products', { error: (error as Error).message });
    res.status(500).json({ error: 'Failed to fetch products' });
  }
});

// Get single product
router.get('/:id', async (req: Request, res: Response) => {
  try {
    const products = await db.query(
      'SELECT * FROM products WHERE id = $1',
      [req.params.id]
    );
    
    if (products.length === 0) {
      res.status(404).json({ error: 'Product not found' });
      return;
    }
    
    res.json(products[0]);
  } catch (error) {
    logger.error('Failed to fetch product', { error: (error as Error).message });
    res.status(500).json({ error: 'Failed to fetch product' });
  }
});

// Create product (admin only)
router.post(
  '/',
  authenticateToken,
  validate([
    body('name').trim().notEmpty().withMessage('Name is required'),
    body('price').isFloat({ min: 0 }).withMessage('Price must be a positive number'),
    body('image_url').trim().notEmpty().withMessage('Image URL is required'),
  ]),
  async (req: Request, res: Response) => {
    try {
      const { name, description, price, weight, image_url, origin, roast_intensity, roast_notes, type } = req.body;
      const id = 'PROD-' + Date.now();
      
      const result = await db.query(
        \`INSERT INTO products (id, name, description, price, weight, image_url, origin, roast_intensity, roast_notes, type)
         VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)
         RETURNING *\`,
        [id, name, description || '', price, weight || '500g', image_url, origin || 'Ethiopia', roast_intensity || 3, JSON.stringify(roast_notes || []), type || 'Single Origin']
      );
      
      logger.info('Product created', { productId: id });
      res.status(201).json(result[0]);
    } catch (error) {
      logger.error('Failed to create product', { error: (error as Error).message });
      res.status(500).json({ error: 'Failed to create product' });
    }
  }
);

// Update product (admin only)
router.put('/:id', authenticateToken, async (req: Request, res: Response) => {
  try {
    const { name, description, price, weight, image_url, origin, roast_intensity, roast_notes, type } = req.body;
    
    const result = await db.query(
      \`UPDATE products 
       SET name = $1, description = $2, price = $3, weight = $4, image_url = $5, 
           origin = $6, roast_intensity = $7, roast_notes = $8, type = $9
       WHERE id = $10
       RETURNING *\`,
      [name, description, price, weight, image_url, origin, roast_intensity, JSON.stringify(roast_notes), type, req.params.id]
    );
    
    if (result.length === 0) {
      res.status(404).json({ error: 'Product not found' });
      return;
    }
    
    logger.info('Product updated', { productId: req.params.id });
    res.json(result[0]);
  } catch (error) {
    logger.error('Failed to update product', { error: (error as Error).message });
    res.status(500).json({ error: 'Failed to update product' });
  }
});

// Delete product (admin only)
router.delete('/:id', authenticateToken, async (req: Request, res: Response) => {
  try {
    await db.query('DELETE FROM products WHERE id = $1', [req.params.id]);
    logger.info('Product deleted', { productId: req.params.id });
    res.json({ message: 'Product deleted successfully' });
  } catch (error) {
    logger.error('Failed to delete product', { error: (error as Error).message });
    res.status(500).json({ error: 'Failed to delete product' });
  }
});

export default router;`,

    'admin.ts': `import { Router, Request, Response } from 'express';
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
router.get('/orders', authenticateToken, async (req: Request, res: Response) => {
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
router.get('/messages', authenticateToken, async (req: Request, res: Response) => {
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
router.put('/messages/:id/read', authenticateToken, async (req: Request, res: Response) => {
  try {
    await db.query('UPDATE contact_messages SET read = true WHERE id = $1', [req.params.id]);
    res.json({ message: 'Message marked as read' });
  } catch (error) {
    logger.error('Failed to mark message as read', { error: (error as Error).message });
    res.status(500).json({ error: 'Failed to update message' });
  }
});

// Get all reviews (admin only)
router.get('/reviews', authenticateToken, async (req: Request, res: Response) => {
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
router.get('/analytics', authenticateToken, async (req: Request, res: Response) => {
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

export default router;`,
};

// Write all route files
Object.entries(routes).forEach(([filename, content]) => {
    const filePath = path.join(routesDir, filename);
    fs.writeFileSync(filePath, content, 'utf8');
    console.log(\`Created: \${filePath}\`);
});

console.log('All route files generated successfully!');
