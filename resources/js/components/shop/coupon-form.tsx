import { router, useForm } from '@inertiajs/react';
import { TicketPercent, X } from 'lucide-react';
import type { FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useTranslation } from '@/hooks/use-translation';
import en from '@/lang/en';
import type { TranslationKey } from '@/lang/en';
import {
    destroy as destroyCoupon,
    store as storeCoupon,
} from '@/routes/cart/coupon';

/**
 * Coupon errors arrive from the server as stable keys (e.g.
 * "coupon.error_expired") so they can be shown in the active locale.
 */
export function useCouponErrorTranslator() {
    const { t } = useTranslation();

    return (message?: string): string | undefined => {
        if (!message) {
            return undefined;
        }

        return message in en ? t(message as TranslationKey) : message;
    };
}

export function CouponForm({ appliedCode }: { appliedCode: string | null }) {
    const { t } = useTranslation();
    const translateError = useCouponErrorTranslator();
    const form = useForm({ code: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();

        form.post(storeCoupon.url(), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    };

    const remove = () => {
        router.delete(destroyCoupon.url(), { preserveScroll: true });
    };

    if (appliedCode !== null) {
        return (
            <div className="flex items-center justify-between rounded-md border border-dashed border-pink-300 bg-pink-50/50 py-1.5 pr-1.5 pl-3 text-sm dark:border-pink-800 dark:bg-pink-950/20">
                <span className="flex items-center gap-2 font-medium">
                    <TicketPercent className="size-4 text-pink-600" />
                    {appliedCode}
                </span>
                <Button
                    variant="ghost"
                    size="icon"
                    aria-label={t('coupon.remove')}
                    onClick={remove}
                    className="size-7"
                >
                    <X />
                </Button>
            </div>
        );
    }

    return (
        <form onSubmit={submit}>
            <div className="flex gap-2">
                <Input
                    value={form.data.code}
                    onChange={(event) =>
                        form.setData('code', event.target.value.toUpperCase())
                    }
                    placeholder={t('coupon.label')}
                    aria-label={t('coupon.label')}
                />
                <Button
                    type="submit"
                    variant="secondary"
                    disabled={form.processing || form.data.code.trim() === ''}
                >
                    {t('coupon.apply')}
                </Button>
            </div>
            {form.errors.code && (
                <p className="mt-2 text-sm text-destructive">
                    {translateError(form.errors.code)}
                </p>
            )}
        </form>
    );
}
