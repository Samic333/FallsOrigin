import React from 'react';
import { Product } from './types';

export const FALLS_ORIGIN_COFFEE: Product = {
  id: 'falls-origin-001',
  name: 'Falls Origin Coffee – Single-Origin Ethiopian',
  description: 'Our signature Single-Origin Ethiopian Heirloom coffee. Experience a clear, vibrant cup with tea-like clarity and unmistakable notes of citrus acidity, wildflower honey, and delicate floral aromatics. Sourced directly from Yirgacheffe at 2100m elevation.',
  price: 32.00,
  weight: '500 g / 1.1 lb',
  // Using the high-fidelity brand asset provided by the user
  image: '/falls-origin-premium-packaging.png',
  origin: 'Ethiopia (Yirgacheffe)',
  roastIntensity: 3,
  roastNotes: ['Citrus', 'Honey', 'Floral']
};

export const ROAST_DETAILS = {
  profile: 'Medium Roast',
  notes: ['Citrus', 'Honey', 'Floral'],
  intensity: 3
};

export const BUSINESS_LOCATION = {
  city: 'Toronto',
  province: 'ON',
  lat: 43.6532,
  lng: -79.3832
};

export const CONTACT_EMAIL = 'admin@fallsorigin.coffee';