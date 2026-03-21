import nodemailer, { Transporter } from 'nodemailer';
import { env } from '../config/env';
import { logger } from '../utils/logger';

interface EmailParams {
    to: string;
    subject: string;
    html: string;
    text?: string;
}

class EmailService {
    private transporter: Transporter;

    constructor() {
        this.transporter = this.createTransporter();
    }

    private createTransporter(): Transporter {
        switch (env.EMAIL_PROVIDER) {
            case 'sendgrid':
                return nodemailer.createTransport({
                    host: 'smtp.sendgrid.net',
                    port: 587,
                    auth: {
                        user: 'apikey',
                        pass: env.SENDGRID_API_KEY,
                    },
                });

            case 'mailgun':
                return nodemailer.createTransport({
                    host: 'smtp.mailgun.org',
                    port: 587,
                    auth: {
                        user: `postmaster@${env.MAILGUN_DOMAIN}`,
                        pass: env.MAILGUN_API_KEY,
                    },
                });

            case 'ses':
                return nodemailer.createTransport({
                    host: `email-smtp.${env.AWS_REGION}.amazonaws.com`,
                    port: 587,
                    auth: {
                        user: env.AWS_ACCESS_KEY_ID,
                        pass: env.AWS_SECRET_ACCESS_KEY,
                    },
                });

            default:
                throw new Error(`Unsupported email provider: ${env.EMAIL_PROVIDER}`);
        }
    }

    private async sendEmail(params: EmailParams): Promise<void> {
        try {
            await this.transporter.sendMail({
                from: `${env.EMAIL_FROM_NAME} <${env.EMAIL_FROM}>`,
                to: params.to,
                subject: params.subject,
                html: params.html,
                text: params.text || params.html.replace(/<[^>]*>/g, ''),
            });

            logger.info('Email sent successfully', {
                to: params.to,
                subject: params.subject,
            });
        } catch (error) {
            logger.error('Failed to send email', {
                error: (error as Error).message,
                to: params.to,
                subject: params.subject,
            });
            throw error;
        }
    }

    /**
     * Send order confirmation email to customer
     */
    async sendOrderConfirmation(params: {
        email: string;
        customerName: string;
        orderId: string;
        total: number;
        items: any[];
        trackingToken: string;
    }): Promise<void> {
        const trackingUrl = `${env.FRONTEND_URL}/track?token=${params.trackingToken}`;

        const itemsHtml = params.items
            .map(
                (item) => `
        <tr>
          <td style="padding: 10px; border-bottom: 1px solid #eee;">${item.product.name}</td>
          <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: center;">${item.quantity}</td>
          <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: right;">$${(item.product.price * item.quantity).toFixed(2)}</td>
        </tr>
      `
            )
            .join('');

        const html = `
      <!DOCTYPE html>
      <html>
      <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
      </head>
      <body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); padding: 40px 20px; text-align: center; border-radius: 10px 10px 0 0;">
          <h1 style="color: #d4a574; margin: 0; font-size: 28px; font-weight: 700;">Falls Origin Coffee</h1>
          <p style="color: #fff; margin: 10px 0 0 0; font-size: 12px; text-transform: uppercase; letter-spacing: 2px;">Order Confirmation</p>
        </div>
        
        <div style="background: #fff; padding: 40px 30px; border: 1px solid #eee; border-top: none;">
          <h2 style="color: #1a1a1a; margin: 0 0 20px 0;">Thank you, ${params.customerName}!</h2>
          <p style="color: #666; margin: 0 0 30px 0;">Your order has been confirmed and will be processed shortly.</p>
          
          <div style="background: #f8f8f8; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
            <p style="margin: 0 0 10px 0; color: #999; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Order Number</p>
            <p style="margin: 0; font-size: 20px; font-weight: 700; color: #1a1a1a;">${params.orderId}</p>
          </div>
          
          <h3 style="color: #1a1a1a; margin: 0 0 20px 0; font-size: 18px;">Order Details</h3>
          <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
            <thead>
              <tr style="background: #f8f8f8;">
                <th style="padding: 10px; text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #999;">Product</th>
                <th style="padding: 10px; text-align: center; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #999;">Qty</th>
                <th style="padding: 10px; text-align: right; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #999;">Price</th>
              </tr>
            </thead>
            <tbody>
              ${itemsHtml}
              <tr>
                <td colspan="2" style="padding: 20px 10px 10px 10px; text-align: right; font-weight: 700; font-size: 16px;">Total:</td>
                <td style="padding: 20px 10px 10px 10px; text-align: right; font-weight: 700; font-size: 16px; color: #d4a574;">$${params.total.toFixed(2)} CAD</td>
              </tr>
            </tbody>
          </table>
          
          <div style="text-align: center; margin: 40px 0;">
            <a href="${trackingUrl}" style="display: inline-block; background: #d4a574; color: #fff; padding: 15px 40px; text-decoration: none; border-radius: 5px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; font-size: 14px;">Track Your Order</a>
          </div>
          
          <p style="color: #999; font-size: 14px; margin: 30px 0 0 0; padding-top: 30px; border-top: 1px solid #eee;">
            Questions? Reply to this email or contact us at ${env.ADMIN_EMAIL}
          </p>
        </div>
        
        <div style="background: #f8f8f8; padding: 20px; text-align: center; border-radius: 0 0 10px 10px;">
          <p style="color: #999; font-size: 12px; margin: 0;">© ${new Date().getFullYear()} Falls Origin Coffee. All rights reserved.</p>
        </div>
      </body>
      </html>
    `;

        await this.sendEmail({
            to: params.email,
            subject: `Order Confirmed - ${params.orderId}`,
            html,
        });
    }

    /**
     * Send new order notification to admin
     */
    async sendAdminOrderNotification(params: {
        orderId: string;
        customerName: string;
        email: string;
        total: number;
        deliveryMethod: string;
    }): Promise<void> {
        const html = `
      <!DOCTYPE html>
      <html>
      <body style="font-family: sans-serif; padding: 20px;">
        <h2>🎉 New Order Received</h2>
        <p><strong>Order ID:</strong> ${params.orderId}</p>
        <p><strong>Customer:</strong> ${params.customerName} (${params.email})</p>
        <p><strong>Total:</strong> $${params.total.toFixed(2)} CAD</p>
        <p><strong>Delivery Method:</strong> ${params.deliveryMethod}</p>
        <p><a href="${env.FRONTEND_URL}/admin">View in Admin Dashboard</a></p>
      </body>
      </html>
    `;

        await this.sendEmail({
            to: env.ADMIN_EMAIL,
            subject: `New Order: ${params.orderId}`,
            html,
        });
    }

    /**
     * Send ETA update email
     */
    async sendETAUpdate(params: {
        email: string;
        customerName: string;
        orderId: string;
        eta: string;
        trackingToken: string;
    }): Promise<void> {
        const trackingUrl = `${env.FRONTEND_URL}/track?token=${params.trackingToken}`;

        const html = `
      <!DOCTYPE html>
      <html>
      <body style="font-family: sans-serif; padding: 20px; max-width: 600px; margin: 0 auto;">
        <h2 style="color: #d4a574;">Delivery Update</h2>
        <p>Hi ${params.customerName},</p>
        <p>Great news! Your order <strong>${params.orderId}</strong> is on its way.</p>
        <div style="background: #f8f8f8; padding: 20px; border-radius: 8px; margin: 20px 0;">
          <p style="margin: 0; color: #999; font-size: 12px;">Estimated Delivery</p>
          <p style="margin: 10px 0 0 0; font-size: 20px; font-weight: 700; color: #1a1a1a;">${params.eta}</p>
        </div>
        <p><a href="${trackingUrl}" style="color: #d4a574;">Track your order</a></p>
      </body>
      </html>
    `;

        await this.sendEmail({
            to: params.email,
            subject: `Delivery Update - ${params.orderId}`,
            html,
        });
    }

    /**
     * Send tracking number email
     */
    async sendTrackingNumber(params: {
        email: string;
        customerName: string;
        orderId: string;
        trackingNumber: string;
        carrier: string;
        trackingToken: string;
    }): Promise<void> {
        const trackingUrl = `${env.FRONTEND_URL}/track?token=${params.trackingToken}`;
        const carrierUrls: Record<string, string> = {
            'Canada Post': `https://www.canadapost-postescanada.ca/track-reperage/en#/search?searchFor=${params.trackingNumber}`,
            'Purolator': `https://www.purolator.com/en/shipping/tracker?pin=${params.trackingNumber}`,
            'UPS': `https://www.ups.com/track?tracknum=${params.trackingNumber}`,
            'FedEx': `https://www.fedex.com/fedextrack/?tracknumbers=${params.trackingNumber}`,
        };

        const carrierTrackingUrl = carrierUrls[params.carrier] || '#';

        const html = `
      <!DOCTYPE html>
      <html>
      <body style="font-family: sans-serif; padding: 20px; max-width: 600px; margin: 0 auto;">
        <h2 style="color: #d4a574;">Your Order Has Shipped!</h2>
        <p>Hi ${params.customerName},</p>
        <p>Your order <strong>${params.orderId}</strong> has been shipped via ${params.carrier}.</p>
        <div style="background: #f8f8f8; padding: 20px; border-radius: 8px; margin: 20px 0;">
          <p style="margin: 0; color: #999; font-size: 12px;">Tracking Number</p>
          <p style="margin: 10px 0 0 0; font-size: 20px; font-weight: 700; color: #1a1a1a;">${params.trackingNumber}</p>
        </div>
        <p>
          <a href="${carrierTrackingUrl}" style="display: inline-block; background: #d4a574; color: #fff; padding: 15px 40px; text-decoration: none; border-radius: 5px; font-weight: 700; margin-right: 10px;">Track with ${params.carrier}</a>
          <a href="${trackingUrl}" style="color: #d4a574;">View Order Details</a>
        </p>
      </body>
      </html>
    `;

        await this.sendEmail({
            to: params.email,
            subject: `Shipped - ${params.orderId}`,
            html,
        });
    }

    /**
     * Send review request email (48 hours after delivery)
     */
    async sendReviewRequest(params: {
        email: string;
        customerName: string;
        orderId: string;
    }): Promise<void> {
        const reviewUrl = `${env.FRONTEND_URL}/review?order=${params.orderId}`;

        const html = `
      <!DOCTYPE html>
      <html>
      <body style="font-family: sans-serif; padding: 20px; max-width: 600px; margin: 0 auto;">
        <h2 style="color: #d4a574;">How Was Your Coffee?</h2>
        <p>Hi ${params.customerName},</p>
        <p>We hope you're enjoying your Falls Origin Coffee! We'd love to hear your thoughts.</p>
        <p>Your feedback helps us continue to deliver exceptional coffee experiences.</p>
        <div style="text-align: center; margin: 30px 0;">
          <a href="${reviewUrl}" style="display: inline-block; background: #d4a574; color: #fff; padding: 15px 40px; text-decoration: none; border-radius: 5px; font-weight: 700;">Leave a Review</a>
        </div>
        <p style="color: #999; font-size: 14px;">This should only take a minute. Thank you for your support!</p>
      </body>
      </html>
    `;

        await this.sendEmail({
            to: params.email,
            subject: 'How was your coffee? ☕',
            html,
        });
    }

    /**
     * Forward contact message to admin
     */
    async forwardContactMessage(params: {
        name: string;
        email: string;
        subject: string;
        message: string;
    }): Promise<void> {
        const html = `
      <!DOCTYPE html>
      <html>
      <body style="font-family: sans-serif; padding: 20px;">
        <h2>📧 New Contact Form Submission</h2>
        <p><strong>From:</strong> ${params.name} (${params.email})</p>
        <p><strong>Subject:</strong> ${params.subject}</p>
        <div style="background: #f8f8f8; padding: 20px; border-radius: 8px; margin: 20px 0;">
          <p style="white-space: pre-wrap;">${params.message}</p>
        </div>
        <p><a href="${env.FRONTEND_URL}/admin">View in Admin Dashboard</a></p>
      </body>
      </html>
    `;

        await this.sendEmail({
            to: env.ADMIN_EMAIL,
            subject: `Contact Form: ${params.subject}`,
            html,
        });
    }
}

export const emailService = new EmailService();
