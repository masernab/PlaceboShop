import { Badge } from '@/components/ui/badge';
import { useTranslation } from '@/hooks/use-translation';
import type { TranslationKey } from '@/lang/en';
import { cn } from '@/lib/utils';
import type { OrderStatusValue } from '@/types/shop';

export const orderStatusLabels: Record<OrderStatusValue, TranslationKey> = {
    paid: 'status.paid',
    processing: 'status.processing',
    shipped: 'status.shipped',
    out_for_delivery: 'status.out_for_delivery',
    delivered: 'status.delivered',
    cancelled: 'status.cancelled',
};

const styles: Record<OrderStatusValue, string> = {
    paid: 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300',
    processing:
        'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
    shipped:
        'bg-violet-100 text-violet-800 dark:bg-violet-950 dark:text-violet-300',
    out_for_delivery:
        'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300',
    delivered:
        'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300',
};

export function OrderStatusBadge({ status }: { status: OrderStatusValue }) {
    const { t } = useTranslation();

    return (
        <Badge variant="secondary" className={cn(styles[status])}>
            {t(orderStatusLabels[status])}
        </Badge>
    );
}
