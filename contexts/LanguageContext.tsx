import React, { createContext, useContext, useState, useEffect } from 'react';

export type Language = 'en' | 'fr';

export const translations = {
    en: {
        // Navigation & Footer
        home: 'Home',
        shop: 'Shop',
        track: 'Track Order',
        contact: 'Contact',
        discovery: 'Discovery',
        ourRoast: 'Our Roast',
        orderStatus: 'Order Status',
        legal: 'Legal',
        shipping: 'Shipping',
        refunds: 'Refunds',
        terms: 'Terms',
        footerDesc: 'Ethically sourced, masterfully roasted. Bringing the heart of Ethiopian coffee culture to your doorstep in Canada.',
        footerCopyright: '© 2026 Falls Origin Coffee Roasters. Made by OPXERA.',
        footerLocation: 'Hand-crafted in Toronto, Canada.',
        // Common
        loading: 'Loading...',
        addToCart: 'Add to Cart',
        outOfStock: 'Out of Stock',
        price: 'Price',
        description: 'Description',
        details: 'Details',
        reviews: 'Reviews',
        submit: 'Submit',
        back: 'Back',
        // Shop
        shopTitle: 'Our Collection',
        shopSubtitle: 'Exquisite single-origin coffees rooted in Ethiopian heritage.',
        // Product
        origin: 'Origin',
        roastProfile: 'Roast Profile',
        process: 'Process',
        altitude: 'Altitude',
        tastingNotes: 'Tasting Notes',
        // Cart
        cartTitle: 'Your Cart',
        checkout: 'Checkout',
        subtotal: 'Subtotal',
        emptyCart: 'Your cart is empty',
        // Contact
        contactTitle: 'Contact Us',
        contactSubtitle: 'We are here to help.',
        name: 'Name',
        email: 'Email',
        subject: 'Subject',
        message: 'Message',
        send: 'Send Message'
    },
    fr: {
        // Navigation & Footer
        home: 'Accueil',
        shop: 'Boutique',
        track: 'Suivi',
        contact: 'Contact',
        discovery: 'Découverte',
        ourRoast: 'Nos Torréfactions',
        orderStatus: 'Statut Commande',
        legal: 'Légal',
        shipping: 'Livraison',
        refunds: 'Remboursements',
        terms: 'Conditions',
        footerDesc: 'Sourcing éthique, torréfaction de maître. Apporter le cœur de la culture du café éthiopien à votre porte au Canada.',
        footerCopyright: '© 2026 Falls Origin Coffee Roasters. Créé par OPXERA.',
        footerLocation: 'Fabriqué à Toronto, Canada.',
        // Common
        loading: 'Chargement...',
        addToCart: 'Ajouter au panier',
        outOfStock: 'Rupture de stock',
        price: 'Prix',
        description: 'Description',
        details: 'Détails',
        reviews: 'Avis',
        submit: 'Soumettre',
        back: 'Retour',
        // Shop
        shopTitle: 'Notre Collection',
        shopSubtitle: 'Cafés d\'origine unique exquis ancrés dans l\'héritage éthiopien.',
        // Product
        origin: 'Origine',
        roastProfile: 'Profil de Torréfaction',
        process: 'Procédé',
        altitude: 'Altitude',
        tastingNotes: 'Notes de Dégustation',
        // Cart
        cartTitle: 'Votre Panier',
        checkout: 'Payer',
        subtotal: 'Sous-total',
        emptyCart: 'Votre panier est vide',
        // Contact
        contactTitle: 'Contactez-nous',
        contactSubtitle: 'Nous sommes là pour vous aider.',
        name: 'Nom',
        email: 'Email',
        subject: 'Sujet',
        message: 'Message',
        send: 'Envoyer'
    }
};

interface LanguageContextType {
    lang: Language;
    setLang: (lang: Language) => void;
    t: typeof translations.en;
}

const LanguageContext = createContext<LanguageContextType | undefined>(undefined);

export const LanguageProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
    const [lang, setLang] = useState<Language>(() => {
        const saved = localStorage.getItem('foc_lang');
        return (saved === 'en' || saved === 'fr') ? saved : 'en';
    });

    useEffect(() => {
        localStorage.setItem('foc_lang', lang);
    }, [lang]);

    const value = {
        lang,
        setLang,
        t: translations[lang]
    };

    return (
        <LanguageContext.Provider value={value}>
            {children}
        </LanguageContext.Provider>
    );
};

export const useLanguage = () => {
    const context = useContext(LanguageContext);
    if (context === undefined) {
        throw new Error('useLanguage must be used within a LanguageProvider');
    }
    return context;
};
