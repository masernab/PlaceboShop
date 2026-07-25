import { Head, Link } from '@inertiajs/react';
import { Heart } from 'lucide-react';
import { ProductCard } from '@/components/shop/product-card';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';
import { index as productsIndex } from '@/routes/products';
import type { ProductCardData } from '@/types/shop';

type WishlistProps = {
    products: { data: ProductCardData[] };
};

export default function Wishlist({ products }: WishlistProps) {
    const { t } = useTranslation();

    return (
        <>
            <Head title={t('wishlist.title')} />

            <h1 className="mb-6 text-2xl font-semibold tracking-tight">
                {t('wishlist.title')}
            </h1>

            {products.data.length === 0 ? (
                <div className="rounded-xl border border-dashed py-24 text-center">
                    <Heart className="mx-auto size-10 text-muted-foreground" />
                    <p className="mx-auto mt-4 max-w-md text-muted-foreground">
                        {t('wishlist.empty')}
                    </p>
                    <Button className="mt-6" asChild>
                        <Link href={productsIndex()}>
                            {t('cart.empty_cta')}
                        </Link>
                    </Button>
                </div>
            ) : (
                <div className="grid grid-cols-2 gap-x-4 gap-y-8 sm:grid-cols-3 lg:grid-cols-4">
                    {products.data.map((product) => (
                        <ProductCard key={product.id} product={product} />
                    ))}
                </div>
            )}
        </>
    );
}
