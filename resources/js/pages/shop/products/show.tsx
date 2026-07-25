import { Head, Link } from '@inertiajs/react';
import { Minus, Plus, ShoppingBag } from 'lucide-react';
import { useState } from 'react';
import { Price } from '@/components/shop/price';
import { ProductCard } from '@/components/shop/product-card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useTranslation } from '@/hooks/use-translation';
import { cn } from '@/lib/utils';
import { index as productsIndex } from '@/routes/products';
import type { ProductCardData, ProductData } from '@/types/shop';

type ProductShowProps = {
    product: { data: ProductData };
    related: { data: ProductCardData[] };
};

export default function ProductShow({ product, related }: ProductShowProps) {
    const { t } = useTranslation();
    const { data } = product;
    const [selectedImage, setSelectedImage] = useState(0);
    const [quantity, setQuantity] = useState(1);

    const inStock = data.stock > 0;
    const onSale =
        data.compare_at_price_cents !== null &&
        data.compare_at_price_cents > data.price_cents;
    const currentImage = data.images[selectedImage] ?? data.images[0];

    return (
        <>
            <Head title={data.name} />

            <div className="grid gap-10 lg:grid-cols-2">
                <div>
                    <div className="relative aspect-[4/5] overflow-hidden rounded-xl bg-muted">
                        {currentImage && (
                            <img
                                src={currentImage.url}
                                alt={currentImage.alt}
                                className="size-full object-cover"
                            />
                        )}
                        {onSale && (
                            <Badge className="absolute top-3 left-3 bg-pink-600 text-white">
                                {t('product.sale')}
                            </Badge>
                        )}
                    </div>
                    {data.images.length > 1 && (
                        <div className="mt-3 flex gap-3">
                            {data.images.map((image, index) => (
                                <button
                                    key={image.id}
                                    type="button"
                                    onClick={() => setSelectedImage(index)}
                                    aria-label={image.alt}
                                    className={cn(
                                        'w-20 overflow-hidden rounded-lg border-2',
                                        index === selectedImage
                                            ? 'border-pink-500'
                                            : 'border-transparent',
                                    )}
                                >
                                    <img
                                        src={image.url}
                                        alt={image.alt}
                                        className="aspect-[4/5] object-cover"
                                    />
                                </button>
                            ))}
                        </div>
                    )}
                </div>

                <div>
                    <Link
                        href={productsIndex({
                            query: { category: data.category.slug },
                        })}
                        className="text-sm text-muted-foreground hover:text-foreground"
                    >
                        {data.category.name}
                    </Link>
                    <h1 className="mt-1 text-3xl font-bold tracking-tight">
                        {data.name}
                    </h1>
                    <Price
                        cents={data.price_cents}
                        compareAtCents={data.compare_at_price_cents}
                        className="mt-3 text-2xl"
                    />

                    <p className="mt-2 text-sm text-muted-foreground">
                        {t('product.sku')}: {data.sku} ·{' '}
                        <span
                            className={cn(
                                inStock
                                    ? 'text-emerald-600'
                                    : 'text-destructive',
                            )}
                        >
                            {inStock
                                ? t('product.in_stock')
                                : t('product.out_of_stock')}
                        </span>
                    </p>

                    <Separator className="my-6" />

                    <p className="leading-relaxed text-muted-foreground">
                        {data.description}
                    </p>

                    <div className="mt-8 flex flex-wrap items-center gap-4">
                        <div className="flex items-center rounded-md border">
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                aria-label="-"
                                disabled={quantity <= 1}
                                onClick={() => setQuantity(quantity - 1)}
                            >
                                <Minus />
                            </Button>
                            <span
                                className="w-10 text-center text-sm font-medium tabular-nums"
                                aria-label={t('product.quantity')}
                            >
                                {quantity}
                            </span>
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                aria-label="+"
                                disabled={quantity >= Math.max(data.stock, 1)}
                                onClick={() => setQuantity(quantity + 1)}
                            >
                                <Plus />
                            </Button>
                        </div>
                        {/* Cart arrives in Phase 3; until then the CTA is a disabled stub. */}
                        <Button
                            size="lg"
                            disabled={!inStock}
                            title={t('product.coming_soon')}
                        >
                            <ShoppingBag />
                            {t('product.add_to_cart')}
                        </Button>
                    </div>
                    <p className="mt-2 text-xs text-muted-foreground">
                        {t('product.coming_soon')}
                    </p>
                </div>
            </div>

            {related.data.length > 0 && (
                <section className="mt-16">
                    <h2 className="mb-6 text-2xl font-semibold tracking-tight">
                        {t('product.related')}
                    </h2>
                    <div className="grid grid-cols-2 gap-x-4 gap-y-8 sm:grid-cols-3 lg:grid-cols-4">
                        {related.data.map((item) => (
                            <ProductCard key={item.id} product={item} />
                        ))}
                    </div>
                </section>
            )}
        </>
    );
}
