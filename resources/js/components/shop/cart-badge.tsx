import { Link, usePage } from '@inertiajs/react';
import { ShoppingBag } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';
import { show } from '@/routes/cart';

export function CartBadge() {
    const { cart } = usePage().props;
    const { t } = useTranslation();

    return (
        <Button
            variant="ghost"
            size="icon"
            className="relative"
            aria-label={t('nav.cart')}
            asChild
        >
            <Link href={show()}>
                <ShoppingBag />
                {cart.count > 0 && (
                    <span className="absolute -top-0.5 -right-0.5 flex size-4.5 items-center justify-center rounded-full bg-pink-600 text-[10px] font-semibold text-white tabular-nums">
                        {cart.count > 99 ? '99+' : cart.count}
                    </span>
                )}
            </Link>
        </Button>
    );
}
