import { Head, Link, router } from '@inertiajs/react';
import { Pencil, Plus, Search, Trash2 } from 'lucide-react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import { formatPrice } from '@/components/shop/price';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    create as productCreate,
    destroy as productDestroy,
    edit as productEdit,
    index as productsIndex,
} from '@/routes/admin/products';
import type { AdminProductData } from '@/types/admin';
import type { Paginated } from '@/types/shop';

type ProductsIndexProps = {
    products: Paginated<AdminProductData>;
    filters: { q: string };
};

export default function AdminProductsIndex({
    products,
    filters,
}: ProductsIndexProps) {
    const [search, setSearch] = useState(filters.q);

    const submitSearch = (event: FormEvent) => {
        event.preventDefault();

        router.get(
            productsIndex.url(),
            search.trim() === '' ? {} : { q: search.trim() },
            { preserveState: true },
        );
    };

    const destroy = (product: AdminProductData) => {
        router.delete(productDestroy.url(product.id), {
            preserveScroll: true,
        });
    };

    return (
        <>
            <Head title="Products" />

            <div className="flex flex-wrap items-center justify-between gap-3">
                <h1 className="text-xl font-semibold tracking-tight">
                    Products
                </h1>
                <div className="flex items-center gap-2">
                    <form onSubmit={submitSearch} className="relative">
                        <Search className="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            type="search"
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            placeholder="Search products…"
                            className="w-56 pl-8"
                        />
                    </form>
                    <Button asChild>
                        <Link href={productCreate()}>
                            <Plus />
                            New product
                        </Link>
                    </Button>
                </div>
            </div>

            <Card>
                <CardContent>
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b text-left text-muted-foreground">
                                <th className="py-2 font-medium">Product</th>
                                <th className="hidden py-2 font-medium md:table-cell">
                                    Category
                                </th>
                                <th className="py-2 text-right font-medium">
                                    Price
                                </th>
                                <th className="hidden py-2 text-right font-medium sm:table-cell">
                                    Stock
                                </th>
                                <th className="py-2 text-right font-medium">
                                    Status
                                </th>
                                <th className="py-2 text-right font-medium">
                                    <span className="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {products.data.map((product) => (
                                <tr key={product.id} className="border-b">
                                    <td className="py-2.5">
                                        <div className="flex items-center gap-3">
                                            <div className="w-9 shrink-0">
                                                <div className="aspect-[4/5] overflow-hidden rounded bg-muted">
                                                    {product.thumbnail_url && (
                                                        <img
                                                            src={
                                                                product.thumbnail_url
                                                            }
                                                            alt=""
                                                            className="size-full object-cover"
                                                        />
                                                    )}
                                                </div>
                                            </div>
                                            <div>
                                                <p className="font-medium">
                                                    {product.name.en}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    {product.sku}
                                                    {product.is_featured &&
                                                        ' · Featured'}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td className="hidden py-2.5 md:table-cell">
                                        {product.category?.name.en}
                                    </td>
                                    <td className="py-2.5 text-right tabular-nums">
                                        {formatPrice(
                                            product.price_cents,
                                            'en-US',
                                        )}
                                    </td>
                                    <td className="hidden py-2.5 text-right tabular-nums sm:table-cell">
                                        {product.stock}
                                    </td>
                                    <td className="py-2.5 text-right">
                                        <Badge
                                            variant={
                                                product.is_active
                                                    ? 'secondary'
                                                    : 'outline'
                                            }
                                        >
                                            {product.is_active
                                                ? 'Active'
                                                : 'Inactive'}
                                        </Badge>
                                    </td>
                                    <td className="py-2.5 text-right whitespace-nowrap">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            aria-label={`Edit ${product.name.en}`}
                                            asChild
                                        >
                                            <Link
                                                href={productEdit(product.id)}
                                            >
                                                <Pencil />
                                            </Link>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            aria-label={`Delete ${product.name.en}`}
                                            onClick={() => destroy(product)}
                                            className="text-muted-foreground hover:text-destructive"
                                        >
                                            <Trash2 />
                                        </Button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>

                    {products.meta.last_page > 1 && (
                        <div className="mt-4 flex justify-center gap-2">
                            {products.links.prev && (
                                <Button variant="ghost" size="sm" asChild>
                                    <Link
                                        href={products.links.prev}
                                        preserveScroll
                                        preserveState
                                    >
                                        Previous
                                    </Link>
                                </Button>
                            )}
                            {products.links.next && (
                                <Button variant="ghost" size="sm" asChild>
                                    <Link
                                        href={products.links.next}
                                        preserveScroll
                                        preserveState
                                    >
                                        Next
                                    </Link>
                                </Button>
                            )}
                        </div>
                    )}
                </CardContent>
            </Card>
        </>
    );
}
