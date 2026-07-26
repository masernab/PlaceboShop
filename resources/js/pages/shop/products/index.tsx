import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { ProductCard } from '@/components/shop/product-card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTranslation } from '@/hooks/use-translation';
import { index as productsIndex } from '@/routes/products';
import type {
    CategoryData,
    Paginated,
    ProductCardData,
    ProductFilters,
    ProductSort,
} from '@/types/shop';

type ProductsIndexProps = {
    products: Paginated<ProductCardData>;
    categories: { data: CategoryData[] };
    filters: ProductFilters;
};

const partialReload = {
    preserveState: true,
    preserveScroll: true,
    only: ['products', 'filters'],
};

export default function ProductsIndex({
    products,
    categories,
    filters,
}: ProductsIndexProps) {
    const { t } = useTranslation();
    const [minPrice, setMinPrice] = useState(
        filters.min !== null ? String(filters.min / 100) : '',
    );
    const [maxPrice, setMaxPrice] = useState(
        filters.max !== null ? String(filters.max / 100) : '',
    );

    const sortOptions: { value: ProductSort; label: string }[] = [
        { value: 'newest', label: t('products.sort_newest') },
        { value: 'price_asc', label: t('products.sort_price_asc') },
        { value: 'price_desc', label: t('products.sort_price_desc') },
        { value: 'name', label: t('products.sort_name') },
    ];

    const applyFilters = (
        overrides: Partial<Record<keyof ProductFilters, string | number>>,
    ) => {
        const params: Record<string, string | number | undefined> = {
            q: filters.q || undefined,
            category: filters.category || undefined,
            min: filters.min ?? undefined,
            max: filters.max ?? undefined,
            sort: filters.sort === 'newest' ? undefined : filters.sort,
            ...overrides,
        };

        const query = Object.fromEntries(
            Object.entries(params).filter(
                ([, value]) => value !== undefined && value !== '',
            ),
        );

        router.get(productsIndex.url(), query, partialReload);
    };

    const toCents = (value: string): string | number => {
        const parsed = Number.parseFloat(value.replace(',', '.'));

        return Number.isFinite(parsed) && parsed >= 0
            ? Math.round(parsed * 100)
            : '';
    };

    const submitPriceRange = (event: FormEvent) => {
        event.preventDefault();
        applyFilters({ min: toCents(minPrice), max: toCents(maxPrice) });
    };

    const clearFilters = () => {
        setMinPrice('');
        setMaxPrice('');
        router.get(productsIndex.url(), {}, partialReload);
    };

    const hasActiveFilters =
        filters.q !== '' ||
        filters.category !== '' ||
        filters.min !== null ||
        filters.max !== null ||
        filters.sort !== 'newest';

    return (
        <>
            <Head title={t('products.title')} />

            <div className="flex flex-col gap-8 md:flex-row">
                <aside className="w-full shrink-0 space-y-8 md:w-56">
                    <div>
                        <h2 className="mb-3 text-sm font-semibold">
                            {t('products.category')}
                        </h2>
                        <ul className="space-y-1">
                            <li>
                                <button
                                    type="button"
                                    onClick={() =>
                                        applyFilters({ category: '' })
                                    }
                                    data-active={filters.category === ''}
                                    className="w-full rounded-md px-2 py-1.5 text-left text-sm text-muted-foreground hover:bg-accent hover:text-foreground data-[active=true]:font-semibold data-[active=true]:text-foreground"
                                >
                                    {t('products.all_categories')}
                                </button>
                            </li>
                            {categories.data.map((category) => {
                                const branchActive =
                                    filters.category === category.slug ||
                                    (category.children?.some(
                                        (child) =>
                                            child.slug === filters.category,
                                    ) ??
                                        false);

                                return (
                                    <li key={category.id}>
                                        <button
                                            type="button"
                                            onClick={() =>
                                                applyFilters({
                                                    category: category.slug,
                                                })
                                            }
                                            data-active={
                                                filters.category ===
                                                category.slug
                                            }
                                            className="flex w-full items-center justify-between rounded-md px-2 py-1.5 text-left text-sm text-muted-foreground hover:bg-accent hover:text-foreground data-[active=true]:font-semibold data-[active=true]:text-foreground"
                                        >
                                            <span>{category.name}</span>
                                            {category.products_count !==
                                                undefined && (
                                                <span className="text-xs">
                                                    {category.products_count}
                                                </span>
                                            )}
                                        </button>
                                        {category.children !== undefined &&
                                            category.children.length > 0 && (
                                                <ul
                                                    data-branch={branchActive}
                                                    className="mt-0.5 ml-2 space-y-0.5 border-l pl-2 data-[branch=true]:border-foreground/30"
                                                >
                                                    {category.children.map(
                                                        (child) => (
                                                            <li key={child.id}>
                                                                <button
                                                                    type="button"
                                                                    onClick={() =>
                                                                        applyFilters(
                                                                            {
                                                                                category:
                                                                                    child.slug,
                                                                            },
                                                                        )
                                                                    }
                                                                    data-active={
                                                                        filters.category ===
                                                                        child.slug
                                                                    }
                                                                    className="flex w-full items-center justify-between rounded-md px-2 py-1 text-left text-xs text-muted-foreground hover:bg-accent hover:text-foreground data-[active=true]:font-semibold data-[active=true]:text-foreground"
                                                                >
                                                                    <span>
                                                                        {
                                                                            child.name
                                                                        }
                                                                    </span>
                                                                    {child.products_count !==
                                                                        undefined && (
                                                                        <span>
                                                                            {
                                                                                child.products_count
                                                                            }
                                                                        </span>
                                                                    )}
                                                                </button>
                                                            </li>
                                                        ),
                                                    )}
                                                </ul>
                                            )}
                                    </li>
                                );
                            })}
                        </ul>
                    </div>

                    <form onSubmit={submitPriceRange}>
                        <h2 className="mb-3 text-sm font-semibold">
                            {t('products.price')}
                        </h2>
                        <div className="flex items-center gap-2">
                            <Label htmlFor="filter-min" className="sr-only">
                                {t('products.min')}
                            </Label>
                            <Input
                                id="filter-min"
                                type="number"
                                min="0"
                                step="1"
                                inputMode="decimal"
                                placeholder={t('products.min')}
                                value={minPrice}
                                onChange={(event) =>
                                    setMinPrice(event.target.value)
                                }
                            />
                            <span className="text-muted-foreground">–</span>
                            <Label htmlFor="filter-max" className="sr-only">
                                {t('products.max')}
                            </Label>
                            <Input
                                id="filter-max"
                                type="number"
                                min="0"
                                step="1"
                                inputMode="decimal"
                                placeholder={t('products.max')}
                                value={maxPrice}
                                onChange={(event) =>
                                    setMaxPrice(event.target.value)
                                }
                            />
                        </div>
                        <Button
                            type="submit"
                            variant="secondary"
                            size="sm"
                            className="mt-3 w-full"
                        >
                            {t('products.apply')}
                        </Button>
                    </form>

                    {hasActiveFilters && (
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            className="w-full"
                            onClick={clearFilters}
                        >
                            {t('products.clear')}
                        </Button>
                    )}
                </aside>

                <div className="flex-1">
                    <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h1 className="text-2xl font-semibold tracking-tight">
                                {t('products.title')}
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                {filters.q !== ''
                                    ? t('products.results_for', {
                                          q: filters.q,
                                      })
                                    : t('products.results', {
                                          count: products.meta.total,
                                      })}
                            </p>
                        </div>
                        <Select
                            value={filters.sort}
                            onValueChange={(value) =>
                                applyFilters({
                                    sort: value === 'newest' ? '' : value,
                                })
                            }
                        >
                            <SelectTrigger
                                className="w-52"
                                aria-label={t('products.sort')}
                            >
                                <SelectValue placeholder={t('products.sort')} />
                            </SelectTrigger>
                            <SelectContent>
                                {sortOptions.map((option) => (
                                    <SelectItem
                                        key={option.value}
                                        value={option.value}
                                    >
                                        {option.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    {products.data.length === 0 ? (
                        <div className="rounded-xl border border-dashed py-24 text-center">
                            <p className="text-muted-foreground">
                                {t('products.empty')}
                            </p>
                            <Button
                                variant="secondary"
                                size="sm"
                                className="mt-4"
                                onClick={clearFilters}
                            >
                                {t('products.clear')}
                            </Button>
                        </div>
                    ) : (
                        <div className="grid grid-cols-2 gap-x-4 gap-y-8 sm:grid-cols-3 xl:grid-cols-4">
                            {products.data.map((product) => (
                                <ProductCard
                                    key={product.id}
                                    product={product}
                                />
                            ))}
                        </div>
                    )}

                    {products.meta.last_page > 1 && (
                        <nav
                            className="mt-10 flex items-center justify-center gap-1"
                            aria-label={t('products.title')}
                        >
                            {products.links.prev && (
                                <Button variant="ghost" size="sm" asChild>
                                    <Link
                                        href={products.links.prev}
                                        preserveScroll
                                        preserveState
                                        only={['products', 'filters']}
                                    >
                                        {t('products.previous')}
                                    </Link>
                                </Button>
                            )}
                            {products.meta.links.slice(1, -1).map((link) => (
                                <Button
                                    key={link.label}
                                    variant={link.active ? 'default' : 'ghost'}
                                    size="sm"
                                    disabled={link.url === null}
                                    asChild={link.url !== null}
                                >
                                    {link.url === null ? (
                                        <span>{link.label}</span>
                                    ) : (
                                        <Link
                                            href={link.url}
                                            preserveScroll
                                            preserveState
                                            only={['products', 'filters']}
                                        >
                                            {link.label}
                                        </Link>
                                    )}
                                </Button>
                            ))}
                            {products.links.next && (
                                <Button variant="ghost" size="sm" asChild>
                                    <Link
                                        href={products.links.next}
                                        preserveScroll
                                        preserveState
                                        only={['products', 'filters']}
                                    >
                                        {t('products.next')}
                                    </Link>
                                </Button>
                            )}
                        </nav>
                    )}
                </div>
            </div>
        </>
    );
}
