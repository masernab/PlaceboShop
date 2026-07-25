const en = {
    'nav.home': 'Home',
    'nav.shop': 'Shop',
    'nav.wishlist': 'Wishlist',
    'nav.cart': 'Cart',
    'nav.orders': 'My orders',
    'nav.settings': 'Settings',
    'nav.admin': 'Admin',
    'nav.login': 'Log in',
    'nav.register': 'Sign up',
    'nav.logout': 'Log out',
    'nav.menu': 'Menu',
    'nav.search_placeholder': 'Search products…',
    'footer.tagline': 'Nothing ships. Nothing is charged. Pure retail joy.',
    'footer.disclaimer':
        'PlaceboShop is a placebo store: every purchase is simulated. No real money is ever charged and no products are ever shipped.',
    'common.language': 'Language',
} as const;

export type TranslationKey = keyof typeof en;

export default en;
