import { Router, Request, Response } from 'express';
import { body } from 'express-validator';
import { db } from '../config/database';
import { emailService } from '../services/email.service';
import { contactLimiter } from '../middleware/rate-limit';
import { validate } from '../middleware/validation';
import { logger } from '../utils/logger';

const router = Router();

// Submit contact form
router.post(
    '/',
    contactLimiter,
    validate([
        body('name').trim().notEmpty().withMessage('Name is required'),
        body('email').isEmail().withMessage('Valid email is required'),
        body('subject').trim().notEmpty().withMessage('Subject is required'),
        body('message').trim().isLength({ min: 10 }).withMessage('Message must be at least 10 characters'),
    ]),
    async (req: Request, res: Response) => {
        try {
            const { name, email, subject, message } = req.body;

            // Save to database
            await db.query(
                'INSERT INTO contact_messages (name, email, subject, message) VALUES ($1, $2, $3, $4)',
                [name, email, subject, message]
            );

            // Forward to admin email
            emailService.forwardContactMessage({
                name,
                email,
                subject,
                message,
            }).catch((error) => {
                logger.error('Failed to forward contact message', { error: error.message });
            });

            logger.info('Contact message received', { email, subject });
            res.json({ message: 'Message sent successfully' });
        } catch (error) {
            logger.error('Failed to save contact message', { error: (error as Error).message });
            res.status(500).json({ error: 'Failed to send message' });
        }
    }
);

export default router;
