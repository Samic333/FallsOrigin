import { Router, Request, Response } from 'express';
import { body } from 'express-validator';
import { deliveryService } from '../services/delivery.service';
import { stripeService } from '../services/stripe.service';
import { db } from '../config/database';
import { emailService } from '../services/email.service';
import { checkoutLimiter } from '../middleware/rate-limit';
import { validate } from '../middleware/validation';
import { logger } from '../utils/logger';
import crypto from 'crypto';

const router = Router();

// Calculate delivery method and cost
router.post(
    '/calculate-delivery',
    validate([
        body('address').trim().notEmpty(),
        body('city').trim().notEmpty(),
        body('province').trim().notEmpty(),
        body('postalCode').trim().notEmpty(),
    ]),
    async (req: Request, res: Response) => {
        try {
            const { address, city, province, postalCode } = req.body;

            if (!deliveryService.validatePostalCode(postalCode)) {
                res.status(400).json({ error: 'Invalid Canadian postal code format' });
                return;
            }

            const result = await deliveryService.calculateDelivery({
                address,
                city,
                province,
                postalCode,
            });

            res.json(result);
        } catch (error) {
            logger.error('Delivery calculation failed', { error: (error as Error).message });
            res.status(500).json({ error: 'Failed to calculate delivery' });
        }
    }
);

// Create Stripe Payment Intent
router.post(
    '/create-payment-intent',
    checkoutLimiter,
    validate([
        body('amount').isFloat({ min: 0.5 }),
        body('email').isEmail(),
        body('customerName').trim().notEmpty(),
    ]),
    async (req: Request, res: Response) => {
        try {
            const { amount, email, customerName } = req.body;
            const orderId = 'ORD-' + crypto.randomBytes(6).toString('hex').toUpperCase();

            const paymentIntent = await stripeService.createPaymentIntent({
                amount,
                currency: 'cad',
                metadata: {
                    orderId,
                    customerEmail: email,
                    customerName,
                },
            });

            res.json({
                clientSecret: paymentIntent.client_secret,
                orderId,
            });
        } catch (error) {
            logger.error('Failed to create payment intent', { error: (error as Error).message });
            res.status(500).json({ error: 'Failed to create payment intent' });
        }
    }
);

// Confirm order after successful payment (idempotent)
router.post(
    '/confirm',
    validate([
        body('paymentIntentId').trim().notEmpty(),
        body('email').isEmail(),
        body('customerName').trim().notEmpty(),
        body('address').trim().notEmpty(),
        body('city').trim().notEmpty(),
        body('province').trim().notEmpty(),
        body('postalCode').trim().notEmpty(),
        body('items').isArray({ min: 1 }),
        body('total').isFloat({ min: 0 }),
        body('deliveryMethod').isIn(['Local Delivery', 'Postal Shipping']),
    ]),
    async (req: Request, res: Response) => {
        try {
            const {
                paymentIntentId,
                email,
                customerName,
                address,
                city,
                province,
                postalCode,
                items,
                total,
                deliveryMethod,
            } = req.body;

            // Check if order already exists (idempotency)
            const existing = await db.query(
                'SELECT id FROM orders WHERE stripe_payment_intent_id = $1',
                [paymentIntentId]
            );

            if (existing.length > 0) {
                logger.info('Order already exists (idempotent)', { orderId: existing[0].id });
                res.json({ orderId: existing[0].id, message: 'Order already confirmed' });
                return;
            }

            // Verify payment with Stripe
            const paymentIntent = await stripeService.getPaymentIntent(paymentIntentId);

            if (paymentIntent.status !== 'succeeded') {
                res.status(400).json({ error: 'Payment not completed' });
                return;
            }

            const orderId = paymentIntent.metadata.orderId;
            const trackingToken = crypto.randomBytes(16).toString('hex');

            // Create order in database
            await db.transaction(async (client) => {
                await client.query(
                    `INSERT INTO orders (
            id, email, customer_name, address, city, province, postal_code,
            items, total, status, delivery_method, stripe_payment_intent_id,
            tracking_token
          ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13)`,
                    [
                        orderId,
                        email,
                        customerName,
                        address,
                        city,
                        province,
                        postalCode,
                        JSON.stringify(items),
                        total,
                        'Paid',
                        deliveryMethod,
                        paymentIntentId,
                        trackingToken,
                    ]
                );

                // Create audit log entry
                await client.query(
                    'INSERT INTO order_audit_log (order_id, status, notes) VALUES ($1, $2, $3)',
                    [orderId, 'Paid', 'Order created and payment confirmed']
                );

                // Track analytics
                await client.query(
                    "INSERT INTO analytics_events (event_type, metadata) VALUES ('order_created', $1)",
                    [JSON.stringify({ orderId, total, deliveryMethod })]
                );
            });

            // Send emails asynchronously
            Promise.all([
                emailService.sendOrderConfirmation({
                    email,
                    customerName,
                    orderId,
                    total,
                    items,
                    trackingToken,
                }),
                emailService.sendAdminOrderNotification({
                    orderId,
                    customerName,
                    email,
                    total,
                    deliveryMethod,
                }),
            ]).catch((error) => {
                logger.error('Failed to send confirmation emails', {
                    error: error.message,
                    orderId,
                });
            });

            logger.info('Order confirmed', { orderId, email, total });
            res.json({ orderId, trackingToken });
        } catch (error) {
            logger.error('Order confirmation failed', { error: (error as Error).message });
            res.status(500).json({ error: 'Failed to confirm order' });
        }
    }
);

export default router;
