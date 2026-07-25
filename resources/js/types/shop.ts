export type CategoryData = {
    id: number;
    slug: string;
    name: string;
    description: string;
    products_count?: number;
};

export type ProductImageData = {
    id: number;
    url: string;
    alt: string;
};

export type ProductCardData = {
    id: number;
    slug: string;
    name: string;
    price_cents: number;
    compare_at_price_cents: number | null;
    image?: ProductImageData | null;
    category?: CategoryData;
};

export type ProductData = {
    id: number;
    slug: string;
    sku: string;
    name: string;
    description: string;
    price_cents: number;
    compare_at_price_cents: number | null;
    stock: number;
    images: ProductImageData[];
    category: CategoryData;
};

export type ProductSort = 'newest' | 'price_asc' | 'price_desc' | 'name';

export type ProductFilters = {
    q: string;
    category: string;
    min: number | null;
    max: number | null;
    sort: ProductSort;
};

export type CartItemData = {
    id: number;
    quantity: number;
    line_total_cents: number;
    product: ProductCardData;
};

export type CartData = {
    id: number;
    items: CartItemData[];
};

export type CartTotals = {
    subtotal_cents: number;
    discount_cents: number;
    shipping_cents: number;
    total_cents: number;
};

export type OrderStatusValue =
    | 'paid'
    | 'processing'
    | 'shipped'
    | 'out_for_delivery'
    | 'delivered'
    | 'cancelled';

export type TimelineEntry = {
    status: Exclude<OrderStatusValue, 'cancelled'>;
    at: string;
    reached: boolean;
};

export type OrderItemData = {
    id: number;
    product_id: number | null;
    name: string;
    unit_price_cents: number;
    quantity: number;
    line_total_cents: number;
    image_url: string | null;
};

export type OrderData = {
    id: number;
    order_number: string;
    placed_at: string;
    cancelled_at: string | null;
    status: OrderStatusValue;
    subtotal_cents: number;
    discount_cents: number;
    shipping_cents: number;
    total_cents: number;
    coupon_code: string | null;
    card_brand: string;
    card_last4: string;
    tracking_number: string;
    ship: {
        name: string;
        line1: string;
        line2: string | null;
        city: string;
        postal_code: string;
        country: string;
    };
    timeline: TimelineEntry[];
    items?: OrderItemData[];
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type Paginated<T> = {
    data: T[];
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        from: number | null;
        last_page: number;
        links: PaginationLink[];
        path: string;
        per_page: number;
        to: number | null;
        total: number;
    };
};
