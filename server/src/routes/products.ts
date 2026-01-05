import { Router } from 'express';
import { body } from 'express-validator';
import { db } from '../config/database';
import { authenticateToken } from '../middleware/auth';
import { validate } from '../middleware/validation';
import { logger } from '../utils/logger';

const router = Router();

// Get all products
router.get('/', async (_req: any, res: any) => {
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
router.get('/:id', async (req: any, res: any) => {
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
    async (req: any, res: any) => {
        try {
            const { name, description, price, weight, image_url, origin, roast_intensity, roast_notes, type } = req.body;
            const id = 'PROD-' + Date.now();

            const result = await db.query(
                `INSERT INTO products (id, name, description, price, weight, image_url, origin, roast_intensity, roast_notes, type)
         VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)
         RETURNING *`,
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
router.put('/:id', authenticateToken, async (req: any, res: any) => {
    try {
        const { name, description, price, weight, image_url, origin, roast_intensity, roast_notes, type } = req.body;

        const result = await db.query(
            `UPDATE products 
       SET name = $1, description = $2, price = $3, weight = $4, image_url = $5, 
           origin = $6, roast_intensity = $7, roast_notes = $8, type = $9
       WHERE id = $10
       RETURNING *`,
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
router.delete('/:id', authenticateToken, async (req: any, res: any) => {
    try {
        await db.query('DELETE FROM products WHERE id = $1', [req.params.id]);
        logger.info('Product deleted', { productId: req.params.id });
        res.json({ message: 'Product deleted successfully' });
    } catch (error) {
        logger.error('Failed to delete product', { error: (error as Error).message });
        res.status(500).json({ error: 'Failed to delete product' });
    }
});

export default router;
