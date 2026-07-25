import { Link, router } from '@inertiajs/react';
import { ShoppingBag } from 'lucide-react';
import type { MouseEvent } from 'react';
import { toast } from 'sonner';
import { Price } from '@/components/shop/price';
import { StarRating } from '@/components/shop/star-rating';
import { WishlistButton } from '@/components/shop/wishlist-button';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';
import { store as storeCartItem } from '@/routes/cart/items';
import { show } from '@/routes/products';
import type { ProductCardData } from '@/types/shop';

export function ProductCard({ product }: { product: ProductCardData }) {
    const { t } = useTranslation();
    const onSale =
        product.compare_at_price_cents !== null &&
        product.compare_at_price_cents > product.price_cents;

    const quickAdd = (event: MouseEvent) => {
        event.preventDefault();
        event.stopPropagation();

        router.post(
            storeCartItem.url(),
            { product_id: product.id, quantity: 1 },
            {
                preserveScroll: true,
                onSuccess: () => toast.success(t('cart.added')),
            },
        );
    };

    return (
        <Link href={show(product.slug)} className="group block">
            <div className="relative aspect-[4/5] overflow-hidden rounded-lg bg-muted">
                {product.image && (
                    <img
                        src={product.image.url}
                        alt={product.image.alt}
                        loading="lazy"
                        className="size-full object-cover transition-transform duration-300 group-hover:scale-105"
                    />
                )}
                {onSale && (
                    <Badge className="absolute top-2 left-2 bg-pink-600 text-white">
                        {t('product.sale')}
                    </Badge>
                )}
                <WishlistButton
                    productId={product.id}
                    className="absolute top-2 right-2 size-8 shadow-sm"
                />
                <Button
                    size="icon"
                    aria-label={t('product.add_to_cart')}
                    onClick={quickAdd}
                    className="absolute right-2 bottom-2 opacity-0 shadow-md transition-opacity group-hover:opacity-100 focus-visible:opacity-100"
                >
                    <ShoppingBag />
                </Button>
            </div>
            <div className="mt-3 space-y-1">
                {product.category && (
                    <p className="text-xs text-muted-foreground">
                        {product.category.name}
                    </p>
                )}
                <h3 className="text-sm font-medium group-hover:underline">
                    {product.name}
                </h3>
                {product.rating_avg !== null && (
                    <span className="flex items-center gap-1">
                        <StarRating value={product.rating_avg} />
                        <span className="text-xs text-muted-foreground">
                            ({product.reviews_count})
                        </span>
                    </span>
                )}
                <Price
                    cents={product.price_cents}
                    compareAtCents={product.compare_at_price_cents}
                    className="text-sm"
                />
            </div>
        </Link>
    );
}
