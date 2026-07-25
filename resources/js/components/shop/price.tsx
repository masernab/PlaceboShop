import { useTranslation } from '@/hooks/use-translation';
import { cn } from '@/lib/utils';

export function formatPrice(cents: number, locale: string): string {
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: 'USD',
    }).format(cents / 100);
}

type PriceProps = {
    cents: number;
    compareAtCents?: number | null;
    className?: string;
};

export function Price({ cents, compareAtCents = null, className }: PriceProps) {
    const { locale } = useTranslation();
    const onSale = compareAtCents !== null && compareAtCents > cents;

    return (
        <span className={cn('inline-flex items-baseline gap-2', className)}>
            <span className={cn('font-semibold', onSale && 'text-pink-600')}>
                {formatPrice(cents, locale)}
            </span>
            {onSale && (
                <span className="text-sm text-muted-foreground line-through">
                    {formatPrice(compareAtCents, locale)}
                </span>
            )}
        </span>
    );
}
