import { Head, Link, router } from '@inertiajs/react';
import { ChevronRight, ShoppingBag } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import { Price } from '@/components/shop/price';
import { ProductCard } from '@/components/shop/product-card';
import { QuantityInput } from '@/components/shop/quantity-input';
import { ReviewsSection } from '@/components/shop/reviews-section';
import { StarRating } from '@/components/shop/star-rating';
import { WishlistButton } from '@/components/shop/wishlist-button';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useTranslation } from '@/hooks/use-translation';
import { cn } from '@/lib/utils';
import { store as storeCartItem } from '@/routes/cart/items';
import { index as productsIndex } from '@/routes/products';
import type { ProductCardData, ProductData, ReviewData } from '@/types/shop';

type ProductShowProps = {
    product: { data: ProductData };
    reviews: { data: ReviewData[] };
    canReview: boolean;
    related: { data: ProductCardData[] };
};

export default function ProductShow({
    product,
    reviews,
    canReview,
    related,
}: ProductShowProps) {
    const { t } = useTranslation();
    const { data } = product;
    const [selectedImage, setSelectedImage] = useState(0);
    const [quantity, setQuantity] = useState(1);

    const inStock = data.stock > 0;
    const onSale =
        data.compare_at_price_cents !== null &&
        data.compare_at_price_cents > data.price_cents;
    const currentImage = data.images[selectedImage] ?? data.images[0];

    const addToCart = () => {
        router.post(
            storeCartItem.url(),
            { product_id: data.id, quantity },
            {
                preserveScroll: true,
                onSuccess: () => toast.success(t('cart.added')),
            },
        );
    };

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
                    <nav className="flex items-center gap-1 text-sm text-muted-foreground">
                        {data.category.parent != null && (
                            <>
                                <Link
                                    href={productsIndex({
                                        query: {
                                            category: data.category.parent.slug,
                                        },
                                    })}
                                    className="hover:text-foreground"
                                >
                                    {data.category.parent.name}
                                </Link>
                                <ChevronRight
                                    className="size-3.5 shrink-0"
                                    aria-hidden
                                />
                            </>
                        )}
                        <Link
                            href={productsIndex({
                                query: { category: data.category.slug },
                            })}
                            className="hover:text-foreground"
                        >
                            {data.category.name}
                        </Link>
                    </nav>
                    <h1 className="mt-1 text-3xl font-bold tracking-tight">
                        {data.name}
                    </h1>
                    {data.rating_avg !== null && (
                        <a
                            href="#reviews"
                            className="mt-2 flex w-fit items-center gap-1.5"
                        >
                            <StarRating value={data.rating_avg} />
                            <span className="text-sm text-muted-foreground">
                                {data.rating_avg} ·{' '}
                                {t('reviews.count', {
                                    count: data.reviews_count,
                                })}
                            </span>
                        </a>
                    )}
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
                        <QuantityInput
                            value={quantity}
                            onChange={setQuantity}
                            max={Math.max(data.stock, 1)}
                        />
                        <Button
                            size="lg"
                            disabled={!inStock}
                            onClick={addToCart}
                        >
                            <ShoppingBag />
                            {t('product.add_to_cart')}
                        </Button>
                        <WishlistButton
                            productId={data.id}
                            variant="outline"
                            className="size-10"
                        />
                    </div>
                </div>
            </div>

            <ReviewsSection
                productSlug={data.slug}
                ratingAvg={data.rating_avg}
                reviewsCount={data.reviews_count}
                reviews={reviews.data}
                canReview={canReview}
            />

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
