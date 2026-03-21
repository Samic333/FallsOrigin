import NodeGeocoder from 'node-geocoder';
import { env } from '../config/env';
import { logger } from '../utils/logger';

interface GeocodingResult {
    latitude: number;
    longitude: number;
}

interface DeliveryCalculation {
    deliveryMethod: 'Local Delivery' | 'Postal Shipping';
    distanceKm: number;
    shippingCost: number;
}

class DeliveryService {
    private geocoder: any;

    constructor() {
        const options: any = {
            provider: env.GEOCODING_PROVIDER,
            apiKey: env.GEOCODING_PROVIDER === 'google' ? env.GOOGLE_MAPS_API_KEY : env.OPENCAGE_API_KEY,
            formatter: null,
        };

        this.geocoder = NodeGeocoder(options);
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     */
    private calculateDistance(
        lat1: number,
        lon1: number,
        lat2: number,
        lon2: number
    ): number {
        const R = 6371; // Earth's radius in km
        const dLat = this.toRad(lat2 - lat1);
        const dLon = this.toRad(lon2 - lon1);

        const a =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(this.toRad(lat1)) *
            Math.cos(this.toRad(lat2)) *
            Math.sin(dLon / 2) *
            Math.sin(dLon / 2);

        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        const distance = R * c;

        return distance;
    }

    private toRad(degrees: number): number {
        return degrees * (Math.PI / 180);
    }

    /**
     * Geocode an address to get coordinates
     */
    private async geocodeAddress(address: string): Promise<GeocodingResult> {
        try {
            const results = await this.geocoder.geocode(address);

            if (!results || results.length === 0) {
                throw new Error('Address not found');
            }

            const result = results[0];

            if (!result.latitude || !result.longitude) {
                throw new Error('Invalid geocoding result');
            }

            return {
                latitude: result.latitude,
                longitude: result.longitude,
            };
        } catch (error) {
            logger.error('Geocoding failed', {
                error: (error as Error).message,
                address,
            });
            throw new Error('Unable to geocode address');
        }
    }

    /**
     * Calculate delivery method and cost based on address
     */
    async calculateDelivery(params: {
        address: string;
        city: string;
        province: string;
        postalCode: string;
    }): Promise<DeliveryCalculation> {
        try {
            // Build full address
            const fullAddress = `${params.address}, ${params.city}, ${params.province}, ${params.postalCode}, Canada`;

            // Geocode the destination address
            const destination = await this.geocodeAddress(fullAddress);

            // Calculate distance from base location
            const distanceKm = this.calculateDistance(
                env.BASE_LATITUDE,
                env.BASE_LONGITUDE,
                destination.latitude,
                destination.longitude
            );

            logger.info('Delivery distance calculated', {
                address: fullAddress,
                distanceKm: distanceKm.toFixed(2),
            });

            // Determine delivery method and cost
            const isLocal = distanceKm <= env.DELIVERY_RADIUS_KM;
            const deliveryMethod = isLocal ? 'Local Delivery' : 'Postal Shipping';
            const shippingCost = isLocal ? 5.0 : 15.0;

            return {
                deliveryMethod,
                distanceKm,
                shippingCost,
            };
        } catch (error) {
            logger.error('Delivery calculation failed', {
                error: (error as Error).message,
                address: params.address,
            });

            // Fallback: use postal code prefix for rough estimation
            // Ontario postal codes starting with K, L, M, N, P are more likely to be local
            const postalPrefix = params.postalCode.toUpperCase().charAt(0);
            const likelyLocal = ['K', 'L', 'M', 'N', 'P'].includes(postalPrefix);

            logger.warn('Using fallback postal code estimation', {
                postalCode: params.postalCode,
                postalPrefix,
                likelyLocal,
            });

            return {
                deliveryMethod: likelyLocal ? 'Local Delivery' : 'Postal Shipping',
                distanceKm: likelyLocal ? 100 : 500, // Estimated
                shippingCost: likelyLocal ? 5.0 : 15.0,
            };
        }
    }

    /**
     * Validate Canadian postal code format
     */
    validatePostalCode(postalCode: string): boolean {
        const canadianPostalCodeRegex = /^[A-Z]\d[A-Z] ?\d[A-Z]\d$/i;
        return canadianPostalCodeRegex.test(postalCode);
    }
}

export const deliveryService = new DeliveryService();
