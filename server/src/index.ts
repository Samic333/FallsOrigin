import express, { Request, Response, NextFunction } from 'express';
import cors from 'cors';
import helmet from 'helmet';
import compression from 'compression';
import path from 'path';
import { env, isDevelopment } from './config/env';
import { db } from './config/database';
import { logger } from './utils/logger';
import { apiLimiter } from './middleware/rate-limit';

// Import routes
import productsRouter from './routes/products';
import checkoutRouter from './routes/checkout';
import ordersRouter from './routes/orders';
import adminRouter from './routes/admin';
import contactRouter from './routes/contact';
import reviewsRouter from './routes/reviews';
import webhooksRouter from './routes/webhooks';

const app = express();

// Trust proxy (required for Cloud Run)
app.set('trust proxy', 1);

// Security middleware
app.use(helmet({
    contentSecurityPolicy: isDevelopment ? false : undefined,
}));

// CORS configuration
app.use(cors({
    origin: env.FRONTEND_URL,
    credentials: true,
}));

// Compression
app.use(compression());

// Body parsing - IMPORTANT: webhooks need raw body
app.use('/api/webhooks/stripe', express.raw({ type: 'application/json' }));
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));

// Request logging
app.use((req: Request, res: Response, next: NextFunction) => {
    const start = Date.now();
    res.on('finish', () => {
        const duration = Date.now() - start;
        logger.info('HTTP Request', {
            method: req.method,
            path: req.path,
            status: res.statusCode,
            duration,
            ip: req.ip,
        });
    });
    next();
});

// Health check endpoint
app.get('/health', async (_req: any, res: any) => {
    const dbHealthy = await db.healthCheck();

    if (dbHealthy) {
        res.json({
            status: 'healthy',
            timestamp: new Date().toISOString(),
            environment: env.NODE_ENV,
        });
    } else {
        res.status(503).json({
            status: 'unhealthy',
            timestamp: new Date().toISOString(),
            error: 'Database connection failed',
        });
    }
});

// API routes
app.use('/api/products', apiLimiter, productsRouter);
app.use('/api/checkout', checkoutRouter);
app.use('/api/orders', ordersRouter);
app.use('/api/admin', adminRouter);
app.use('/api/contact', contactRouter);
app.use('/api/reviews', reviewsRouter);
app.use('/api/webhooks', webhooksRouter);

// Serve static frontend files in production
if (!isDevelopment) {
    // In Docker: /app/dist-frontend. In Dev: ../../dist-frontend (or similar)
    // We check absolute path first for Docker
    let frontendPath = path.join(process.cwd(), 'dist-frontend');
    if (!require('fs').existsSync(frontendPath)) {
        frontendPath = path.join(__dirname, '../../dist-frontend');
    }
    app.use(express.static(frontendPath));

    // SPA fallback - serve index.html for all non-API routes
    app.get('*', (_req: Request, res: Response) => {
        res.sendFile(path.join(frontendPath, 'index.html'));
    });
}

// 404 handler for API routes
app.use('/api/*', (_req: Request, res: Response) => {
    res.status(404).json({ error: 'API endpoint not found' });
});

// Global error handler
app.use((err: Error, req: Request, res: Response, _next: NextFunction) => {
    logger.error('Unhandled error', {
        error: err.message,
        stack: err.stack,
        path: req.path,
        method: req.method,
    });

    res.status(500).json({
        error: isDevelopment ? err.message : 'Internal server error',
        ...(isDevelopment && { stack: err.stack }),
    });
});

// Start server
const PORT = env.PORT;

app.listen(PORT, () => {
    logger.info(`Server started listening`, {
        environment: env.NODE_ENV,
        port: PORT,
        url: `http://localhost:${PORT}`
    });

    // Test database connection
    db.healthCheck().then((healthy) => {
        if (healthy) {
            logger.info('Database connection established');
        } else {
            logger.error('Database connection failed');
        }
    });
});

// Graceful shutdown
process.on('SIGTERM', async () => {
    logger.info('SIGTERM received, shutting down gracefully');
    await db.close();
    process.exit(0);
});

process.on('SIGINT', async () => {
    logger.info('SIGINT received, shutting down gracefully');
    await db.close();
    process.exit(0);
});

export default app;
