import dotenv from 'dotenv';

dotenv.config();

interface EnvConfig {
    NODE_ENV: 'development' | 'staging' | 'production';
    PORT: number;
    DATABASE_URL: string;

    // Stripe
    STRIPE_SECRET_KEY: string;
    STRIPE_PUBLISHABLE_KEY: string;
    STRIPE_WEBHOOK_SECRET: string;

    // Email
    EMAIL_PROVIDER: 'sendgrid' | 'mailgun' | 'ses';
    EMAIL_FROM: string;
    EMAIL_FROM_NAME: string;
    SENDGRID_API_KEY?: string;
    MAILGUN_API_KEY?: string;
    MAILGUN_DOMAIN?: string;
    AWS_ACCESS_KEY_ID?: string;
    AWS_SECRET_ACCESS_KEY?: string;
    AWS_REGION?: string;

    // Admin
    ADMIN_EMAIL: string;
    JWT_SECRET: string;
    JWT_EXPIRATION: string;
    ADMIN_PASSWORD_HASH: string;

    // Delivery
    BASE_CITY: string;
    BASE_LATITUDE: number;
    BASE_LONGITUDE: number;
    DELIVERY_RADIUS_KM: number;

    // Geocoding
    GEOCODING_PROVIDER: 'google' | 'opencage';
    GOOGLE_MAPS_API_KEY?: string;
    OPENCAGE_API_KEY?: string;

    // Frontend
    FRONTEND_URL: string;

    // Review
    REVIEW_EMAIL_DELAY_HOURS: number;

    // Rate Limiting
    RATE_LIMIT_WINDOW_MS: number;
    RATE_LIMIT_MAX_REQUESTS: number;
}

function getEnv(key: string, defaultValue?: string): string {
    const value = process.env[key] || defaultValue;
    if (!value) {
        console.warn(`[WARN] Missing environment variable: ${key}. Using fallback.`);
        return 'MISSING_ENV_VAR';
    }
    return value;
}

function getEnvNumber(key: string, defaultValue?: number): number {
    const value = process.env[key];
    if (!value && defaultValue === undefined) {
        console.warn(`[WARN] Missing environment variable: ${key}. Using default 0.`);
        return 0;
    }
    return value ? parseInt(value, 10) : defaultValue!;
}

// Validation functions now just log warnings instead of throwing
function validateEmailProvider(provider: string): void {
    const validProviders = ['sendgrid', 'mailgun', 'ses'];
    if (!validProviders.includes(provider)) {
        console.warn(`[WARN] Invalid EMAIL_PROVIDER: ${provider}. Defaulting to sendgrid.`);
    }
}

function validateGeocodingProvider(provider: string): void {
    const validProviders = ['google', 'opencage'];
    if (!validProviders.includes(provider)) {
        console.warn(`[WARN] Invalid GEOCODING_PROVIDER: ${provider}. Defaulting to google.`);
    }
}

// Load and validate environment variables
const emailProvider = getEnv('EMAIL_PROVIDER', 'sendgrid');
const geocodingProvider = getEnv('GEOCODING_PROVIDER', 'google');

validateEmailProvider(emailProvider);
validateGeocodingProvider(geocodingProvider);

export const env: EnvConfig = {
    NODE_ENV: (getEnv('NODE_ENV', 'development') as EnvConfig['NODE_ENV']),
    PORT: getEnvNumber('PORT', 8080),
    DATABASE_URL: getEnv('DATABASE_URL'),

    STRIPE_SECRET_KEY: getEnv('STRIPE_SECRET_KEY'),
    STRIPE_PUBLISHABLE_KEY: getEnv('STRIPE_PUBLISHABLE_KEY'),
    STRIPE_WEBHOOK_SECRET: getEnv('STRIPE_WEBHOOK_SECRET'),

    EMAIL_PROVIDER: emailProvider as EnvConfig['EMAIL_PROVIDER'],
    EMAIL_FROM: getEnv('EMAIL_FROM'),
    EMAIL_FROM_NAME: getEnv('EMAIL_FROM_NAME', 'Falls Origin Coffee'),
    SENDGRID_API_KEY: process.env.SENDGRID_API_KEY,
    MAILGUN_API_KEY: process.env.MAILGUN_API_KEY,
    MAILGUN_DOMAIN: process.env.MAILGUN_DOMAIN,
    AWS_ACCESS_KEY_ID: process.env.AWS_ACCESS_KEY_ID,
    AWS_SECRET_ACCESS_KEY: process.env.AWS_SECRET_ACCESS_KEY,
    AWS_REGION: process.env.AWS_REGION || 'us-east-1',

    ADMIN_EMAIL: getEnv('ADMIN_EMAIL'),
    JWT_SECRET: getEnv('JWT_SECRET'),
    JWT_EXPIRATION: getEnv('JWT_EXPIRATION', '24h'),
    ADMIN_PASSWORD_HASH: getEnv('ADMIN_PASSWORD_HASH'),

    BASE_CITY: getEnv('BASE_CITY', 'Toronto, ON, Canada'),
    BASE_LATITUDE: getEnvNumber('BASE_LATITUDE', 43.6532),
    BASE_LONGITUDE: getEnvNumber('BASE_LONGITUDE', -79.3832),
    DELIVERY_RADIUS_KM: getEnvNumber('DELIVERY_RADIUS_KM', 200),

    GEOCODING_PROVIDER: geocodingProvider as EnvConfig['GEOCODING_PROVIDER'],
    GOOGLE_MAPS_API_KEY: process.env.GOOGLE_MAPS_API_KEY,
    OPENCAGE_API_KEY: process.env.OPENCAGE_API_KEY,

    FRONTEND_URL: getEnv('FRONTEND_URL', 'http://localhost:5173'),

    REVIEW_EMAIL_DELAY_HOURS: getEnvNumber('REVIEW_EMAIL_DELAY_HOURS', 48),

    RATE_LIMIT_WINDOW_MS: getEnvNumber('RATE_LIMIT_WINDOW_MS', 900000),
    RATE_LIMIT_MAX_REQUESTS: getEnvNumber('RATE_LIMIT_MAX_REQUESTS', 100),
};

export const isDevelopment = env.NODE_ENV === 'development';
export const isProduction = env.NODE_ENV === 'production';
export const isStaging = env.NODE_ENV === 'staging';
