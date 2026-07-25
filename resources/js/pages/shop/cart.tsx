import { Head, Link, router, usePage } from '@inertiajs/react';
import { ShoppingBag, Trash2 } from 'lucide-react';
import { toast } from 'sonner';
import { formatPrice, Price } from '@/components/shop/price';
import { QuantityInput } from '@/components/shop/quantity-input';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useTranslation } from '@/hooks/use-translation';
import { login } from '@/routes';
import {
    destroy as destroyCartItem,
    update as updateCartItem,
} from '@/routes/cart/items';
import { index as productsIndex, show as productShow } from '@/routes/products';
import type { CartData, CartItemData, CartTotals } from '@/types/shop';

type CartPageProps = {
    cart: { data: CartData } | null;
    totals: CartTotals | null;
};

function CartLine({ item }: { item: CartItemData }) {
    const { t } = useTranslation();

    const updateQuantity = (quantity: number) => {
        router.put(
            updateCartItem.url(item.id),
            { quantity },
            { preserveScroll: true },
        );
    };

    const remove = () => {
        router.delete(destroyCartItem.url(item.id), {
            preserveScroll: true,
            onSuccess: () => toast.success(t('cart.removed')),
        });
    };

    return (
        <div className="flex gap-4 py-4">
            <Link
                href={productShow(item.product.slug)}
                className="w-20 shrink-0 sm:w-24"
            >
                <div className="aspect-[4/5] overflow-hidden rounded-md bg-muted">
                    {item.product.image && (
                        <img
                            src={item.product.image.url}
                            alt={item.product.image.alt}
                            className="size-full object-cover"
                        />
                    )}
                </div>
            </Link>
            <div className="flex flex-1 flex-col justify-between gap-2">
                <div className="flex items-start justify-between gap-4">
                    <div>
                        {item.product.category && (
                            <p className="text-xs text-muted-foreground">
                                {item.product.category.name}
                            </p>
                        )}
                        <Link
                            href={productShow(item.product.slug)}
                            className="text-sm font-medium hover:underline"
                        >
                            {item.product.name}
                        </Link>
                        <Price
                            cents={item.product.price_cents}
                            compareAtCents={item.product.compare_at_price_cents}
                            className="mt-1 block text-sm"
                        />
                    </div>
                    <Price
                        cents={item.line_total_cents}
                        className="text-sm font-semibold"
                    />
                </div>
                <div className="flex items-center justify-between gap-4">
                    <QuantityInput
                        value={item.quantity}
                        onChange={updateQuantity}
                    />
                    <Button
                        variant="ghost"
                        size="icon"
                        aria-label={t('cart.remove')}
                        onClick={remove}
                        className="text-muted-foreground hover:text-destructive"
                    >
                        <Trash2 />
                    </Button>
                </div>
            </div>
        </div>
    );
}

export default function Cart({ cart, totals }: CartPageProps) {
    const { auth } = usePage().props;
    const { t, locale } = useTranslation();

    const items = cart?.data.items ?? [];

    return (
        <>
            <Head title={t('cart.title')} />

            <h1 className="mb-6 text-2xl font-semibold tracking-tight">
                {t('cart.title')}
            </h1>

            {items.length === 0 || totals === null ? (
                <div className="rounded-xl border border-dashed py-24 text-center">
                    <ShoppingBag className="mx-auto size-10 text-muted-foreground" />
                    <p className="mt-4 text-muted-foreground">
                        {t('cart.empty')}
                    </p>
                    <Button className="mt-6" asChild>
                        <Link href={productsIndex()}>
                            {t('cart.empty_cta')}
                        </Link>
                    </Button>
                </div>
            ) : (
                <div className="flex flex-col gap-10 lg:flex-row">
                    <div className="flex-1 divide-y">
                        {items.map((item) => (
                            <CartLine key={item.id} item={item} />
                        ))}
                    </div>

                    <aside className="h-fit w-full shrink-0 rounded-xl border p-6 lg:w-80">
                        <h2 className="font-semibold">{t('cart.summary')}</h2>
                        <dl className="mt-4 space-y-2 text-sm">
                            <div className="flex justify-between">
                                <dt className="text-muted-foreground">
                                    {t('cart.subtotal')}
                                </dt>
                                <dd className="tabular-nums">
                                    {formatPrice(totals.subtotal_cents, locale)}
                                </dd>
                            </div>
                            {totals.discount_cents > 0 && (
                                <div className="flex justify-between text-emerald-600">
                                    <dt>{t('cart.discount')}</dt>
                                    <dd className="tabular-nums">
                                        −
                                        {formatPrice(
                                            totals.discount_cents,
                                            locale,
                                        )}
                                    </dd>
                                </div>
                            )}
                            <div className="flex justify-between">
                                <dt className="text-muted-foreground">
                                    {t('cart.shipping')}
                                </dt>
                                <dd className="tabular-nums">
                                    {totals.shipping_cents === 0
                                        ? t('cart.shipping_free')
                                        : formatPrice(
                                              totals.shipping_cents,
                                              locale,
                                          )}
                                </dd>
                            </div>
                        </dl>
                        <Separator className="my-4" />
                        <div className="flex justify-between font-semibold">
                            <span>{t('cart.total')}</span>
                            <span className="tabular-nums">
                                {formatPrice(totals.total_cents, locale)}
                            </span>
                        </div>
                        {auth.user ? (
                            <>
                                {/* Checkout arrives in Phase 4. */}
                                <Button
                                    className="mt-6 w-full"
                                    size="lg"
                                    disabled
                                    title={t('product.coming_soon')}
                                >
                                    {t('cart.checkout')}
                                </Button>
                                <p className="mt-2 text-center text-xs text-muted-foreground">
                                    {t('product.coming_soon')}
                                </p>
                            </>
                        ) : (
                            <Button className="mt-6 w-full" size="lg" asChild>
                                <Link href={login()}>
                                    {t('cart.login_to_checkout')}
                                </Link>
                            </Button>
                        )}
                    </aside>
                </div>
            )}
        </>
    );
}
