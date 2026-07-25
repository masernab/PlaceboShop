import { Link } from '@inertiajs/react';
import { Price } from '@/components/shop/price';
import { Badge } from '@/components/ui/badge';
import { useTranslation } from '@/hooks/use-translation';
import { show } from '@/routes/products';
import type { ProductCardData } from '@/types/shop';

export function ProductCard({ product }: { product: ProductCardData }) {
    const { t } = useTranslation();
    const onSale =
        product.compare_at_price_cents !== null &&
        product.compare_at_price_cents > product.price_cents;

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
                <Price
                    cents={product.price_cents}
                    compareAtCents={product.compare_at_price_cents}
                    className="text-sm"
                />
            </div>
        </Link>
    );
}
