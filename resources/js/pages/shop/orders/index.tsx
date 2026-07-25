import { Head, Link } from '@inertiajs/react';
import { PackageOpen } from 'lucide-react';
import { OrderStatusBadge } from '@/components/shop/order-status-badge';
import { formatPrice } from '@/components/shop/price';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';
import { show as orderShow } from '@/routes/orders';
import { index as productsIndex } from '@/routes/products';
import type { OrderData } from '@/types/shop';

type OrdersIndexProps = {
    orders: { data: OrderData[] };
};

export default function OrdersIndex({ orders }: OrdersIndexProps) {
    const { t, locale } = useTranslation();

    const formatDate = (iso: string) =>
        new Intl.DateTimeFormat(locale, { dateStyle: 'long' }).format(
            new Date(iso),
        );

    return (
        <>
            <Head title={t('orders.title')} />

            <h1 className="mb-6 text-2xl font-semibold tracking-tight">
                {t('orders.title')}
            </h1>

            {orders.data.length === 0 ? (
                <div className="rounded-xl border border-dashed py-24 text-center">
                    <PackageOpen className="mx-auto size-10 text-muted-foreground" />
                    <p className="mt-4 text-muted-foreground">
                        {t('orders.empty')}
                    </p>
                    <Button className="mt-6" asChild>
                        <Link href={productsIndex()}>
                            {t('cart.empty_cta')}
                        </Link>
                    </Button>
                </div>
            ) : (
                <ul className="space-y-4">
                    {orders.data.map((order) => (
                        <li key={order.id}>
                            <Link
                                href={orderShow(order.id)}
                                className="flex flex-wrap items-center justify-between gap-4 rounded-xl border p-5 transition-colors hover:border-pink-300 dark:hover:border-pink-800"
                            >
                                <div>
                                    <p className="font-medium">
                                        {order.order_number}
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        {t('orders.placed_on', {
                                            date: formatDate(order.placed_at),
                                        })}
                                        {' · '}
                                        {t('orders.items', {
                                            count: order.items?.length ?? 0,
                                        })}
                                    </p>
                                </div>
                                <div className="flex items-center gap-4">
                                    <span className="font-semibold tabular-nums">
                                        {formatPrice(order.total_cents, locale)}
                                    </span>
                                    <OrderStatusBadge status={order.status} />
                                </div>
                            </Link>
                        </li>
                    ))}
                </ul>
            )}
        </>
    );
}
