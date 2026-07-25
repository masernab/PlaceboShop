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
