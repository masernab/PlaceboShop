import { Head } from '@inertiajs/react';
import { PartyPopper } from 'lucide-react';
import { OrderStatusBadge } from '@/components/shop/order-status-badge';
import { formatPrice } from '@/components/shop/price';
import { TrackingTimeline } from '@/components/shop/tracking-timeline';
import { Separator } from '@/components/ui/separator';
import { useTranslation } from '@/hooks/use-translation';
import type { OrderData } from '@/types/shop';

type OrderShowProps = {
    order: { data: OrderData };
    justPlaced: boolean;
};

export default function OrderShow({ order, justPlaced }: OrderShowProps) {
    const { t, locale } = useTranslation();
    const { data } = order;

    const countryNames = new Intl.DisplayNames([locale], { type: 'region' });

    const formatDate = (iso: string) =>
        new Intl.DateTimeFormat(locale, {
            dateStyle: 'long',
            timeStyle: 'short',
        }).format(new Date(iso));

    return (
        <>
            <Head title={`${t('orders.order')} ${data.order_number}`} />

            {justPlaced && (
                <div className="mb-8 rounded-2xl bg-gradient-to-br from-pink-100 via-rose-50 to-amber-50 p-8 text-center dark:from-pink-950/40 dark:via-rose-950/20 dark:to-amber-950/20">
                    <PartyPopper className="mx-auto size-10 text-pink-600" />
                    <h2 className="mt-3 text-2xl font-bold tracking-tight">
                        {t('orders.confirmation_title')}
                    </h2>
                    <p className="mx-auto mt-2 max-w-xl text-muted-foreground">
                        {t('orders.confirmation_body')}
                    </p>
                </div>
            )}

            <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">
                        {t('orders.order')} {data.order_number}
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        {t('orders.placed_on', {
                            date: formatDate(data.placed_at),
                        })}
                    </p>
                </div>
                <OrderStatusBadge status={data.status} />
            </div>

            <div className="flex flex-col gap-10 lg:flex-row">
                <div className="flex-1">
                    <div className="divide-y rounded-xl border px-5">
                        {(data.items ?? []).map((item) => (
                            <div
                                key={item.id}
                                className="flex items-center gap-4 py-4"
                            >
                                <div className="w-14 shrink-0">
                                    <div className="aspect-square overflow-hidden rounded-md bg-muted">
                                        {item.image_url && (
                                            <img
                                                src={item.image_url}
                                                alt={item.name}
                                                className="size-full object-cover"
                                            />
                                        )}
                                    </div>
                                </div>
                                <div className="flex-1">
                                    <p className="text-sm font-medium">
                                        {item.name}
                                    </p>
                                    <p className="text-sm text-muted-foreground tabular-nums">
                                        {item.quantity} ×{' '}
                                        {formatPrice(
                                            item.unit_price_cents,
                                            locale,
                                        )}
                                    </p>
                                </div>
                                <span className="text-sm font-semibold tabular-nums">
                                    {formatPrice(item.line_total_cents, locale)}
                                </span>
                            </div>
                        ))}
                    </div>

                    <div className="mt-6 rounded-xl border p-5">
                        <dl className="space-y-2 text-sm">
                            <div className="flex justify-between">
                                <dt className="text-muted-foreground">
                                    {t('cart.subtotal')}
                                </dt>
                                <dd className="tabular-nums">
                                    {formatPrice(data.subtotal_cents, locale)}
                                </dd>
                            </div>
                            {data.discount_cents > 0 && (
                                <div className="flex justify-between text-emerald-600">
                                    <dt>
                                        {t('cart.discount')}
                                        {data.coupon_code !== null &&
                                            ` (${data.coupon_code})`}
                                    </dt>
                                    <dd className="tabular-nums">
                                        −
                                        {formatPrice(
                                            data.discount_cents,
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
                                    {data.shipping_cents === 0
                                        ? t('cart.shipping_free')
                                        : formatPrice(
                                              data.shipping_cents,
                                              locale,
                                          )}
                                </dd>
                            </div>
                        </dl>
                        <Separator className="my-3" />
                        <div className="flex justify-between font-semibold">
                            <span>{t('cart.total')}</span>
                            <span className="tabular-nums">
                                {formatPrice(data.total_cents, locale)}
                            </span>
                        </div>
                    </div>
                </div>

                <aside className="w-full shrink-0 space-y-6 lg:w-96">
                    <div className="rounded-xl border p-5">
                        <h2 className="font-semibold">
                            {t('orders.tracking')}
                        </h2>
                        <p className="mt-1 mb-4 text-sm text-muted-foreground">
                            {t('orders.tracking_number')}:{' '}
                            <span className="font-mono">
                                {data.tracking_number}
                            </span>
                        </p>
                        <TrackingTimeline
                            timeline={data.timeline}
                            cancelled={data.cancelled_at !== null}
                        />
                    </div>

                    <div className="rounded-xl border p-5">
                        <h2 className="font-semibold">
                            {t('orders.shipping_to')}
                        </h2>
                        <address className="mt-2 text-sm text-muted-foreground not-italic">
                            {data.ship.name}
                            <br />
                            {data.ship.line1}
                            {data.ship.line2 !== null &&
                                data.ship.line2 !== '' && (
                                    <>
                                        <br />
                                        {data.ship.line2}
                                    </>
                                )}
                            <br />
                            {data.ship.postal_code} {data.ship.city}
                            <br />
                            {countryNames.of(data.ship.country) ??
                                data.ship.country}
                        </address>
                    </div>

                    <div className="rounded-xl border p-5">
                        <h2 className="font-semibold">{t('orders.payment')}</h2>
                        <p className="mt-2 text-sm text-muted-foreground">
                            {t('orders.card_ending', {
                                brand:
                                    data.card_brand.charAt(0).toUpperCase() +
                                    data.card_brand.slice(1),
                                last4: data.card_last4,
                            })}
                        </p>
                    </div>
                </aside>
            </div>
        </>
    );
}
