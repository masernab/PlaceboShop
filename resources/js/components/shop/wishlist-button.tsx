import { router, usePage } from '@inertiajs/react';
import { Heart } from 'lucide-react';
import type { MouseEvent } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';
import { cn } from '@/lib/utils';
import { login } from '@/routes';
import {
    destroy as removeFromWishlist,
    store as addToWishlist,
} from '@/routes/wishlist';

type WishlistButtonProps = {
    productId: number;
    className?: string;
    variant?: 'secondary' | 'outline';
};

export function WishlistButton({
    productId,
    className,
    variant = 'secondary',
}: WishlistButtonProps) {
    const { auth, wishlist } = usePage().props;
    const { t } = useTranslation();
    const saved = wishlist.includes(productId);

    const toggle = (event: MouseEvent) => {
        event.preventDefault();
        event.stopPropagation();

        if (!auth.user) {
            router.visit(login().url);

            return;
        }

        if (saved) {
            router.delete(removeFromWishlist.url(productId), {
                preserveScroll: true,
                onSuccess: () => toast.success(t('wishlist.removed')),
            });
        } else {
            router.post(
                addToWishlist.url(productId),
                {},
                {
                    preserveScroll: true,
                    onSuccess: () => toast.success(t('wishlist.added')),
                },
            );
        }
    };

    return (
        <Button
            variant={variant}
            size="icon"
            aria-label={saved ? t('wishlist.remove') : t('wishlist.add')}
            aria-pressed={saved}
            onClick={toggle}
            className={className}
        >
            <Heart className={cn(saved && 'fill-pink-600 text-pink-600')} />
        </Button>
    );
}
