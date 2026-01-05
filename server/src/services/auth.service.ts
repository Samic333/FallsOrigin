import jwt from 'jsonwebtoken';
import bcrypt from 'bcrypt';
import { env } from '../config/env';
import { db } from '../config/database';
import { logger } from '../utils/logger';

interface AdminUser {
    id: number;
    username: string;
    email: string;
}

interface JWTPayload {
    userId: number;
    username: string;
    email: string;
}

class AuthService {
    /**
     * Authenticate admin user
     */
    async authenticateAdmin(username: string, password: string): Promise<string> {
        try {
            const users = await db.query<AdminUser & { password_hash: string }>(
                'SELECT id, username, email, password_hash FROM admin_users WHERE username = $1',
                [username]
            );

            if (users.length === 0) {
                logger.warn('Admin login attempt with invalid username', { username });
                throw new Error('Invalid credentials');
            }

            const user = users[0];
            const isValidPassword = await bcrypt.compare(password, user.password_hash);

            if (!isValidPassword) {
                logger.warn('Admin login attempt with invalid password', { username });
                throw new Error('Invalid credentials');
            }

            // Update last login
            await db.query(
                'UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE id = $1',
                [user.id]
            );

            // Generate JWT token
            const token = this.generateToken({
                userId: user.id,
                username: user.username,
                email: user.email,
            });

            logger.info('Admin logged in successfully', {
                userId: user.id,
                username: user.username,
            });

            return token;
        } catch (error) {
            logger.error('Admin authentication failed', {
                error: (error as Error).message,
                username,
            });
            throw error;
        }
    }

    /**
     * Generate JWT token
     */
    generateToken(payload: JWTPayload): string {
        return jwt.sign(payload, env.JWT_SECRET, {
            expiresIn: env.JWT_EXPIRATION,
        });
    }

    /**
     * Verify JWT token
     */
    verifyToken(token: string): JWTPayload {
        try {
            const decoded = jwt.verify(token, env.JWT_SECRET) as JWTPayload;
            return decoded;
        } catch (error) {
            logger.error('JWT verification failed', {
                error: (error as Error).message,
            });
            throw new Error('Invalid or expired token');
        }
    }

    /**
     * Hash password
     */
    async hashPassword(password: string): Promise<string> {
        return await bcrypt.hash(password, 10);
    }

    /**
     * Create admin user (for initial setup)
     */
    async createAdminUser(username: string, email: string, password: string): Promise<void> {
        try {
            const passwordHash = await this.hashPassword(password);

            await db.query(
                'INSERT INTO admin_users (username, email, password_hash) VALUES ($1, $2, $3)',
                [username, email, passwordHash]
            );

            logger.info('Admin user created', { username, email });
        } catch (error) {
            logger.error('Failed to create admin user', {
                error: (error as Error).message,
                username,
            });
            throw error;
        }
    }
}

export const authService = new AuthService();
