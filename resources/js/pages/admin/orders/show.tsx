import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { OrderStatusBadge } from '@/components/shop/order-status-badge';
import { formatPrice } from '@/components/shop/price';
import { TrackingTimeline } from '@/components/shop/tracking-timeline';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { index as ordersIndex } from '@/routes/admin/orders';
import type { AdminOrderData } from '@/types/admin';

type OrderShowProps = {
    order: { data: AdminOrderData };
};

const formatDate = (iso: string) =>
    new Intl.DateTimeFormat('en-US', {
        dateStyle: 'long',
        timeStyle: 'short',
    }).format(new Date(iso));

export default function AdminOrderShow({ order }: OrderShowProps) {
    const { data } = order;

    return (
        <>
            <Head title={`Order ${data.order_number}`} />

            <div className="flex flex-wrap items-center justify-between gap-3">
                <div className="flex items-center gap-3">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href={ordersIndex()} aria-label="Back to orders">
                            <ArrowLeft />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-xl font-semibold tracking-tight">
                            {data.order_number}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {formatDate(data.placed_at)}
                        </p>
                    </div>
                </div>
                <OrderStatusBadge status={data.status} />
            </div>

            <div className="grid gap-6 lg:grid-cols-3">
                <Card className="lg:col-span-2">
                    <CardHeader>
                        <CardTitle>Items</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="divide-y">
                            {(data.items ?? []).map((item) => (
                                <div
                                    key={item.id}
                                    className="flex items-center gap-4 py-3"
                                >
                                    <div className="w-12 shrink-0">
                                        <div className="aspect-[4/5] overflow-hidden rounded-md bg-muted">
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
                                                'en-US',
                                            )}
                                        </p>
                                    </div>
                                    <span className="text-sm font-semibold tabular-nums">
                                        {formatPrice(
                                            item.line_total_cents,
                                            'en-US',
                                        )}
                                    </span>
                                </div>
                            ))}
                        </div>

                        <Separator className="my-4" />

                        <dl className="ml-auto max-w-xs space-y-1.5 text-sm">
                            <div className="flex justify-between">
                                <dt className="text-muted-foreground">
                                    Subtotal
                                </dt>
                                <dd className="tabular-nums">
                                    {formatPrice(data.subtotal_cents, 'en-US')}
                                </dd>
                            </div>
                            {data.discount_cents > 0 && (
                                <div className="flex justify-between text-emerald-600">
                                    <dt>
                                        Discount
                                        {data.coupon_code !== null &&
                                            ` (${data.coupon_code})`}
                                    </dt>
                                    <dd className="tabular-nums">
                                        −
                                        {formatPrice(
                                            data.discount_cents,
                                            'en-US',
                                        )}
                                    </dd>
                                </div>
                            )}
                            <div className="flex justify-between">
                                <dt className="text-muted-foreground">
                                    Shipping
                                </dt>
                                <dd className="tabular-nums">
                                    {data.shipping_cents === 0
                                        ? 'Free'
                                        : formatPrice(
                                              data.shipping_cents,
                                              'en-US',
                                          )}
                                </dd>
                            </div>
                            <div className="flex justify-between font-semibold">
                                <dt>Total</dt>
                                <dd className="tabular-nums">
                                    {formatPrice(data.total_cents, 'en-US')}
                                </dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                <div className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Customer</CardTitle>
                        </CardHeader>
                        <CardContent className="text-sm">
                            <p className="font-medium">{data.customer?.name}</p>
                            <p className="text-muted-foreground">
                                {data.customer?.email}
                            </p>
                            <Separator className="my-3" />
                            <address className="text-muted-foreground not-italic">
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
                                {data.ship.postal_code} {data.ship.city},{' '}
                                {data.ship.country}
                            </address>
                            <Separator className="my-3" />
                            <p className="text-muted-foreground">
                                {data.card_brand.toUpperCase()} ····{' '}
                                {data.card_last4}
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Tracking</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="mb-4 font-mono text-sm text-muted-foreground">
                                {data.tracking_number}
                            </p>
                            <TrackingTimeline
                                timeline={data.timeline}
                                cancelled={data.cancelled_at !== null}
                            />
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
