import { Head, Link } from '@inertiajs/react';
import { OrderStatusBadge } from '@/components/shop/order-status-badge';
import { formatPrice } from '@/components/shop/price';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { show as orderShow } from '@/routes/admin/orders';
import type { AdminOrderData } from '@/types/admin';
import type { Paginated } from '@/types/shop';

type OrdersIndexProps = {
    orders: Paginated<AdminOrderData>;
};

const formatDate = (iso: string) =>
    new Intl.DateTimeFormat('en-US', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(iso));

export default function AdminOrdersIndex({ orders }: OrdersIndexProps) {
    return (
        <>
            <Head title="Orders" />

            <div className="flex items-center justify-between">
                <h1 className="text-xl font-semibold tracking-tight">Orders</h1>
                <p className="text-sm text-muted-foreground">
                    {orders.meta.total} total
                </p>
            </div>

            <Card>
                <CardContent>
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b text-left text-muted-foreground">
                                <th className="py-2 font-medium">Order</th>
                                <th className="py-2 font-medium">Customer</th>
                                <th className="hidden py-2 font-medium md:table-cell">
                                    Placed
                                </th>
                                <th className="py-2 text-right font-medium">
                                    Total
                                </th>
                                <th className="py-2 text-right font-medium">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {orders.data.map((order) => (
                                <tr key={order.id} className="border-b">
                                    <td className="py-2.5">
                                        <Link
                                            href={orderShow(order.id)}
                                            className="font-medium hover:underline"
                                        >
                                            {order.order_number}
                                        </Link>
                                    </td>
                                    <td className="py-2.5">
                                        <p>{order.customer?.name}</p>
                                        <p className="text-xs text-muted-foreground">
                                            {order.customer?.email}
                                        </p>
                                    </td>
                                    <td className="hidden py-2.5 text-muted-foreground md:table-cell">
                                        {formatDate(order.placed_at)}
                                    </td>
                                    <td className="py-2.5 text-right tabular-nums">
                                        {formatPrice(
                                            order.total_cents,
                                            'en-US',
                                        )}
                                    </td>
                                    <td className="py-2.5 text-right">
                                        <OrderStatusBadge
                                            status={order.status}
                                        />
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>

                    {orders.meta.last_page > 1 && (
                        <div className="mt-4 flex justify-center gap-2">
                            {orders.links.prev && (
                                <Button variant="ghost" size="sm" asChild>
                                    <Link
                                        href={orders.links.prev}
                                        preserveScroll
                                    >
                                        Previous
                                    </Link>
                                </Button>
                            )}
                            {orders.links.next && (
                                <Button variant="ghost" size="sm" asChild>
                                    <Link
                                        href={orders.links.next}
                                        preserveScroll
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
