import { Request, Response, NextFunction } from 'express';
import { authService } from '../services/auth.service';
import { logger } from '../utils/logger';

export interface AuthRequest extends Request {
    user?: {
        userId: number;
        username: string;
        email: string;
    };
}

/**
 * Middleware to verify JWT token and attach user to request
 */
export const authenticateToken = (
    req: AuthRequest,
    res: Response,
    next: NextFunction
): void => {
    try {
        const authHeader = req.headers.authorization;
        const token = authHeader && authHeader.split(' ')[1]; // Bearer TOKEN

        if (!token) {
            res.status(401).json({ error: 'Access token required' });
            return;
        }

        const decoded = authService.verifyToken(token);
        req.user = decoded;
        next();
    } catch (error) {
        logger.warn('Authentication failed', {
            error: (error as Error).message,
            path: req.path,
        });
        res.status(403).json({ error: 'Invalid or expired token' });
    }
};

/**
 * Optional authentication - attaches user if token is valid, but doesn't require it
 */
export const optionalAuth = (
    req: any,
    _res: any,
    next: NextFunction
): void => {
    try {
        const authHeader = req.headers.authorization;
        const token = authHeader && authHeader.split(' ')[1];

        if (token) {
            const decoded = authService.verifyToken(token);
            req.user = decoded;
        }
        next();
    } catch (error) {
        // Token invalid, but continue without user
        next();
    }
};
