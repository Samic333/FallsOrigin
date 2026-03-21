import Stripe from 'stripe';
import { env } from '../config/env';
import { logger } from '../utils/logger';

class StripeService {
    private stripe: Stripe;

    constructor() {
        this.stripe = new Stripe(env.STRIPE_SECRET_KEY, {
            apiVersion: '2023-10-16', // Updated to match type definition
            typescript: true,
        });
    }

    /**
     * Create a Payment Intent for checkout
     */
    async createPaymentIntent(params: {
        amount: number; // in cents
        currency: string;
        metadata: {
            orderId: string;
            customerEmail: string;
            customerName: string;
        };
    }): Promise<Stripe.PaymentIntent> {
        try {
            const paymentIntent = await this.stripe.paymentIntents.create({
                amount: Math.round(params.amount * 100), // Convert to cents
                currency: params.currency.toLowerCase(),
                metadata: params.metadata,
                automatic_payment_methods: {
                    enabled: true,
                },
            });

            logger.info('Payment Intent created', {
                paymentIntentId: paymentIntent.id,
                orderId: params.metadata.orderId,
                amount: params.amount,
            });

            return paymentIntent;
        } catch (error) {
            logger.error('Failed to create Payment Intent', {
                error: (error as Error).message,
                orderId: params.metadata.orderId,
            });
            throw error;
        }
    }

    /**
     * Verify Stripe webhook signature
     */
    verifyWebhookSignature(payload: string | Buffer, signature: string): Stripe.Event {
        try {
            const event = this.stripe.webhooks.constructEvent(
                payload,
                signature,
                env.STRIPE_WEBHOOK_SECRET
            );
            return event;
        } catch (error) {
            logger.error('Webhook signature verification failed', {
                error: (error as Error).message,
            });
            throw new Error('Invalid webhook signature');
        }
    }

    /**
     * Retrieve Payment Intent
     */
    async getPaymentIntent(paymentIntentId: string): Promise<Stripe.PaymentIntent> {
        try {
            return await this.stripe.paymentIntents.retrieve(paymentIntentId);
        } catch (error) {
            logger.error('Failed to retrieve Payment Intent', {
                error: (error as Error).message,
                paymentIntentId,
            });
            throw error;
        }
    }

    /**
     * Create a refund
     */
    async createRefund(paymentIntentId: string, reason?: string): Promise<Stripe.Refund> {
        try {
            const refund = await this.stripe.refunds.create({
                payment_intent: paymentIntentId,
                reason: reason as Stripe.RefundCreateParams.Reason,
            });

            logger.info('Refund created', {
                refundId: refund.id,
                paymentIntentId,
                amount: refund.amount,
            });

            return refund;
        } catch (error) {
            logger.error('Failed to create refund', {
                error: (error as Error).message,
                paymentIntentId,
            });
            throw error;
        }
    }

    /**
     * Get publishable key for frontend
     */
    getPublishableKey(): string {
        return env.STRIPE_PUBLISHABLE_KEY;
    }
}

export const stripeService = new StripeService();
