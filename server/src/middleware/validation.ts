import { Request, Response, NextFunction } from 'express';
import { validationResult, ValidationChain } from 'express-validator';
import { logger } from '../utils/logger';

/**
 * Middleware to handle validation errors
 */
export const handleValidationErrors = (
    req: Request,
    res: Response,
    next: NextFunction
): void => {
    const errors = validationResult(req);

    if (!errors.isEmpty()) {
        logger.warn('Validation failed', {
            path: req.path,
            errors: errors.array(),
        });

        res.status(400).json({
            error: 'Validation failed',
            details: errors.array().map(err => ({
                field: err.type === 'field' ? err.path : undefined,
                message: err.msg,
            })),
        });
        return;
    }

    next();
};

/**
 * Wrapper to run validation chains and handle errors
 */
export const validate = (validations: ValidationChain[]) => {
    return async (req: Request, res: Response, next: NextFunction) => {
        await Promise.all(validations.map(validation => validation.run(req)));
        handleValidationErrors(req, res, next);
    };
};
