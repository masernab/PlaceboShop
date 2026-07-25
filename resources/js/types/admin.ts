import type { OrderData } from './shop';

export type LocalizedField = {
    en: string;
    es: string;
};

export type AdminProductImageData = {
    id: number;
    url: string;
    alt: string;
    position: number;
};

export type AdminProductData = {
    id: number;
    slug: string;
    sku: string;
    category_id: number;
    name: LocalizedField;
    description: LocalizedField;
    price_cents: number;
    compare_at_price_cents: number | null;
    stock: number;
    is_active: boolean;
    is_featured: boolean;
    category?: { id: number; name: LocalizedField };
    thumbnail_url?: string | null;
    images?: AdminProductImageData[];
};

export type AdminCategoryData = {
    id: number;
    slug: string;
    name: LocalizedField;
    description: Partial<LocalizedField> | null;
    position: number;
    products_count?: number;
};

export type AdminCouponData = {
    id: number;
    code: string;
    type: 'percent' | 'fixed';
    value: number;
    min_subtotal_cents: number;
    max_uses: number | null;
    used_count: number;
    starts_at: string | null;
    expires_at: string | null;
    is_active: boolean;
};

export type AdminOrderData = OrderData & {
    customer?: { name: string; email: string };
};
