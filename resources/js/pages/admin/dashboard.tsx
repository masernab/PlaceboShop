import { Head, Link } from '@inertiajs/react';
import { Package, ShoppingBag, Sparkles, Users } from 'lucide-react';
import { OrderStatusBadge } from '@/components/shop/order-status-badge';
import { formatPrice } from '@/components/shop/price';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { show as orderShow } from '@/routes/admin/orders';
import type { AdminOrderData } from '@/types/admin';

type DashboardProps = {
    stats: {
        products: number;
        orders_today: number;
        customers: number;
        pretend_revenue_cents: number;
    };
    latestOrders: { data: AdminOrderData[] };
};

const formatDate = (iso: string) =>
    new Intl.DateTimeFormat('en-US', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(iso));

export default function AdminDashboard({
    stats,
    latestOrders,
}: DashboardProps) {
    const cards = [
        { title: 'Products', value: String(stats.products), icon: Package },
        {
            title: 'Orders today',
            value: String(stats.orders_today),
            icon: ShoppingBag,
        },
        { title: 'Customers', value: String(stats.customers), icon: Users },
        {
            title: 'Pretend revenue',
            value: formatPrice(stats.pretend_revenue_cents, 'en-US'),
            icon: Sparkles,
        },
    ];

    return (
        <>
            <Head title="Admin dashboard" />

            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {cards.map((card) => (
                    <Card key={card.title}>
                        <CardHeader className="flex flex-row items-center justify-between">
                            <CardDescription>{card.title}</CardDescription>
                            <card.icon className="size-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <p className="text-2xl font-bold tabular-nums">
                                {card.value}
                            </p>
                        </CardContent>
                    </Card>
                ))}
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Latest orders</CardTitle>
                </CardHeader>
                <CardContent>
                    {latestOrders.data.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            No orders yet.
                        </p>
                    ) : (
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b text-left text-muted-foreground">
                                    <th className="py-2 font-medium">Order</th>
                                    <th className="py-2 font-medium">
                                        Customer
                                    </th>
                                    <th className="hidden py-2 font-medium sm:table-cell">
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
                                {latestOrders.data.map((order) => (
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
                                            {order.customer?.name}
                                        </td>
                                        <td className="hidden py-2.5 text-muted-foreground sm:table-cell">
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
                    )}
                </CardContent>
            </Card>
        </>
    );
}
