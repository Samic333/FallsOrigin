import { Router, Request, Response } from 'express';
import { stripeService } from '../services/stripe.service';
import { db } from '../config/database';
import { logger } from '../utils/logger';
import Stripe from 'stripe';

const router = Router();

// Stripe webhook handler
router.post(
    '/stripe',
    async (req: Request, res: Response) => {
        const signature = req.headers['stripe-signature'] as string;

        if (!signature) {
            res.status(400).json({ error: 'Missing stripe-signature header' });
            return;
        }

        try {
            // Verify webhook signature
            const event = stripeService.verifyWebhookSignature(
                req.body,
                signature
            );

            // Check for duplicate webhook (idempotency)
            const existing = await db.query(
                'SELECT id FROM webhook_events WHERE id = $1',
                [event.id]
            );

            if (existing.length > 0) {
                logger.info('Duplicate webhook event ignored', { eventId: event.id });
                res.json({ received: true, duplicate: true });
                return;
            }

            // Store webhook event
            await db.query(
                'INSERT INTO webhook_events (id, event_type, payload, processed) VALUES ($1, $2, $3, $4)',
                [event.id, event.type, JSON.stringify(event.data.object), false]
            );

            // Handle different event types
            switch (event.type) {
                case 'payment_intent.succeeded':
                    await handlePaymentSuccess(event.data.object as Stripe.PaymentIntent);
                    break;

                case 'payment_intent.payment_failed':
                    await handlePaymentFailure(event.data.object as Stripe.PaymentIntent);
                    break;

                case 'charge.refunded':
                    await handleRefund(event.data.object as Stripe.Charge);
                    break;

                default:
                    logger.info('Unhandled webhook event type', { type: event.type });
            }

            // Mark as processed
            await db.query(
                'UPDATE webhook_events SET processed = true WHERE id = $1',
                [event.id]
            );

            logger.info('Webhook processed successfully', {
                eventId: event.id,
                type: event.type,
            });

            res.json({ received: true });
        } catch (error) {
            logger.error('Webhook processing failed', {
                error: (error as Error).message,
            });
            res.status(400).json({ error: 'Webhook processing failed' });
        }
    }
);

async function handlePaymentSuccess(paymentIntent: Stripe.PaymentIntent): Promise<void> {
    logger.info('Payment succeeded', {
        paymentIntentId: paymentIntent.id,
        orderId: paymentIntent.metadata.orderId,
    });

    // Order creation is handled in the checkout/confirm endpoint
    // This webhook is mainly for logging and monitoring
}

async function handlePaymentFailure(paymentIntent: Stripe.PaymentIntent): Promise<void> {
    logger.warn('Payment failed', {
        paymentIntentId: paymentIntent.id,
        orderId: paymentIntent.metadata.orderId,
    });

    // Optionally: send notification to admin about failed payment
}

async function handleRefund(charge: Stripe.Charge): Promise<void> {
    logger.info('Charge refunded', {
        chargeId: charge.id,
        amount: charge.amount_refunded,
    });

    // Update order status to Refunded
    await db.query(
        "UPDATE orders SET status = 'Refunded' WHERE stripe_charge_id = $1",
        [charge.id]
    );

    await db.query(
        "INSERT INTO order_audit_log (order_id, status, notes) SELECT id, 'Refunded', 'Payment refunded via Stripe' FROM orders WHERE stripe_charge_id = $1",
        [charge.id]
    );
}

export default router;
