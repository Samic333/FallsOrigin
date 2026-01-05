import { Router, Request, Response } from 'express';
import { body } from 'express-validator';
import { db } from '../config/database';
import { reviewLimiter } from '../middleware/rate-limit';
import { validate } from '../middleware/validation';
import { logger } from '../utils/logger';

const router = Router();

// Get approved reviews (public)
router.get('/approved', async (req: Request, res: Response) => {
    try {
        const reviews = await db.query(
            "SELECT id, customer_name, rating, comment, created_at FROM reviews WHERE status = 'approved' ORDER BY created_at DESC LIMIT 50"
        );
        res.json(reviews);
    } catch (error) {
        logger.error('Failed to fetch approved reviews', { error: (error as Error).message });
        res.status(500).json({ error: 'Failed to fetch reviews' });
    }
});

// Submit review
router.post(
    '/',
    reviewLimiter,
    validate([
        body('orderId').trim().notEmpty().withMessage('Order ID is required'),
        body('customerName').trim().notEmpty().withMessage('Name is required'),
        body('rating').isInt({ min: 1, max: 5 }).withMessage('Rating must be between 1 and 5'),
        body('comment').trim().isLength({ min: 10 }).withMessage('Comment must be at least 10 characters'),
    ]),
    async (req: Request, res: Response) => {
        try {
            const { orderId, customerName, rating, comment } = req.body;

            // Verify order exists and is delivered
            const orders = await db.query(
                "SELECT id FROM orders WHERE id = $1 AND status = 'Delivered'",
                [orderId]
            );

            if (orders.length === 0) {
                res.status(400).json({ error: 'Order not found or not yet delivered' });
                return;
            }

            // Check if review already exists for this order
            const existingReviews = await db.query(
                'SELECT id FROM reviews WHERE order_id = $1',
                [orderId]
            );

            if (existingReviews.length > 0) {
                res.status(400).json({ error: 'Review already submitted for this order' });
                return;
            }

            // Create review with pending status
            await db.query(
                'INSERT INTO reviews (order_id, customer_name, rating, comment, status) VALUES ($1, $2, $3, $4, $5)',
                [orderId, customerName, rating, comment, 'pending']
            );

            logger.info('Review submitted', { orderId, rating });
            res.json({ message: 'Review submitted successfully. It will be published after approval.' });
        } catch (error) {
            logger.error('Failed to submit review', { error: (error as Error).message });
            res.status(500).json({ error: 'Failed to submit review' });
        }
    }
);

export default router;
