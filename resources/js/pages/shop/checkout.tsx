import { Head, useForm } from '@inertiajs/react';
import { CreditCard, Lock, Sparkles } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import InputError from '@/components/input-error';
import { useCouponErrorTranslator } from '@/components/shop/coupon-form';
import { formatPrice } from '@/components/shop/price';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Spinner } from '@/components/ui/spinner';
import { useTranslation } from '@/hooks/use-translation';
import { store as storeCheckout } from '@/routes/checkout';
import type { CartData, CartTotals } from '@/types/shop';

type CheckoutProps = {
    cart: { data: CartData };
    totals: CartTotals;
    coupon: string | null;
    countries: string[];
};

function detectBrand(digits: string): 'visa' | 'mastercard' | 'amex' | null {
    if (/^3[47]/.test(digits)) {
        return 'amex';
    }

    if (digits.startsWith('4')) {
        return 'visa';
    }

    const two = Number.parseInt(digits.slice(0, 2), 10);
    const four = Number.parseInt(digits.slice(0, 4), 10);

    if ((two >= 51 && two <= 55) || (four >= 2221 && four <= 2720)) {
        return 'mastercard';
    }

    return null;
}

const brandLabels = {
    visa: 'Visa',
    mastercard: 'Mastercard',
    amex: 'Amex',
} as const;

const formatCardNumber = (value: string) =>
    value
        .replace(/\D/g, '')
        .slice(0, 19)
        .replace(/(\d{4})(?=\d)/g, '$1 ');

const formatExpiry = (value: string) => {
    const digits = value.replace(/\D/g, '').slice(0, 4);

    return digits.length > 2
        ? `${digits.slice(0, 2)}/${digits.slice(2)}`
        : digits;
};

export default function Checkout({
    cart,
    totals,
    coupon,
    countries,
}: CheckoutProps) {
    const { t, locale } = useTranslation();
    const translateCouponError = useCouponErrorTranslator();
    const [fakeProcessing, setFakeProcessing] = useState(false);

    const form = useForm({
        ship_name: '',
        ship_line1: '',
        ship_line2: '',
        ship_city: '',
        ship_postal_code: '',
        ship_country: 'US',
        card_name: '',
        card_number: '',
        card_expiry: '',
        card_cvc: '',
    });

    const busy = fakeProcessing || form.processing;
    const brand = detectBrand(form.data.card_number.replace(/\s/g, ''));
    const countryNames = new Intl.DisplayNames([locale], { type: 'region' });

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (busy) {
            return;
        }

        // Fake "contacting the bank" pause before the real (instant) POST,
        // so the placebo payment feels like a payment.
        setFakeProcessing(true);
        window.setTimeout(() => {
            form.post(storeCheckout.url(), {
                onFinish: () => setFakeProcessing(false),
            });
        }, 1500);
    };

    return (
        <>
            <Head title={t('checkout.title')} />

            <h1 className="mb-6 text-2xl font-semibold tracking-tight">
                {t('checkout.title')}
            </h1>

            <div className="flex flex-col gap-10 lg:flex-row">
                <form onSubmit={submit} className="flex-1 space-y-8">
                    <section className="space-y-4">
                        <h2 className="font-semibold">
                            {t('checkout.shipping_address')}
                        </h2>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="grid gap-2 sm:col-span-2">
                                <Label htmlFor="ship_name">
                                    {t('checkout.name')}
                                </Label>
                                <Input
                                    id="ship_name"
                                    value={form.data.ship_name}
                                    onChange={(e) =>
                                        form.setData(
                                            'ship_name',
                                            e.target.value,
                                        )
                                    }
                                    autoComplete="name"
                                    required
                                />
                                <InputError message={form.errors.ship_name} />
                            </div>
                            <div className="grid gap-2 sm:col-span-2">
                                <Label htmlFor="ship_line1">
                                    {t('checkout.line1')}
                                </Label>
                                <Input
                                    id="ship_line1"
                                    value={form.data.ship_line1}
                                    onChange={(e) =>
                                        form.setData(
                                            'ship_line1',
                                            e.target.value,
                                        )
                                    }
                                    autoComplete="address-line1"
                                    required
                                />
                                <InputError message={form.errors.ship_line1} />
                            </div>
                            <div className="grid gap-2 sm:col-span-2">
                                <Label htmlFor="ship_line2">
                                    {t('checkout.line2')}
                                </Label>
                                <Input
                                    id="ship_line2"
                                    value={form.data.ship_line2}
                                    onChange={(e) =>
                                        form.setData(
                                            'ship_line2',
                                            e.target.value,
                                        )
                                    }
                                    autoComplete="address-line2"
                                />
                                <InputError message={form.errors.ship_line2} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="ship_city">
                                    {t('checkout.city')}
                                </Label>
                                <Input
                                    id="ship_city"
                                    value={form.data.ship_city}
                                    onChange={(e) =>
                                        form.setData(
                                            'ship_city',
                                            e.target.value,
                                        )
                                    }
                                    autoComplete="address-level2"
                                    required
                                />
                                <InputError message={form.errors.ship_city} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="ship_postal_code">
                                    {t('checkout.postal_code')}
                                </Label>
                                <Input
                                    id="ship_postal_code"
                                    value={form.data.ship_postal_code}
                                    onChange={(e) =>
                                        form.setData(
                                            'ship_postal_code',
                                            e.target.value,
                                        )
                                    }
                                    autoComplete="postal-code"
                                    required
                                />
                                <InputError
                                    message={form.errors.ship_postal_code}
                                />
                            </div>
                            <div className="grid gap-2 sm:col-span-2">
                                <Label htmlFor="ship_country">
                                    {t('checkout.country')}
                                </Label>
                                <Select
                                    value={form.data.ship_country}
                                    onValueChange={(value) =>
                                        form.setData('ship_country', value)
                                    }
                                >
                                    <SelectTrigger id="ship_country">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {countries.map((code) => (
                                            <SelectItem key={code} value={code}>
                                                {countryNames.of(code) ?? code}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError
                                    message={form.errors.ship_country}
                                />
                            </div>
                        </div>
                    </section>

                    <section className="space-y-4">
                        <h2 className="flex items-center gap-2 font-semibold">
                            <Lock className="size-4" />
                            {t('checkout.payment')}
                        </h2>
                        <p className="rounded-md bg-pink-50 p-3 text-xs text-pink-900 dark:bg-pink-950/40 dark:text-pink-200">
                            <Sparkles className="mr-1 inline size-3.5" />
                            {t('checkout.hint')}
                        </p>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="grid gap-2 sm:col-span-2">
                                <Label htmlFor="card_name">
                                    {t('checkout.card_name')}
                                </Label>
                                <Input
                                    id="card_name"
                                    value={form.data.card_name}
                                    onChange={(e) =>
                                        form.setData(
                                            'card_name',
                                            e.target.value,
                                        )
                                    }
                                    autoComplete="cc-name"
                                    required
                                />
                                <InputError message={form.errors.card_name} />
                            </div>
                            <div className="grid gap-2 sm:col-span-2">
                                <Label htmlFor="card_number">
                                    {t('checkout.card_number')}
                                </Label>
                                <div className="relative">
                                    <Input
                                        id="card_number"
                                        value={form.data.card_number}
                                        onChange={(e) =>
                                            form.setData(
                                                'card_number',
                                                formatCardNumber(
                                                    e.target.value,
                                                ),
                                            )
                                        }
                                        inputMode="numeric"
                                        placeholder="4242 4242 4242 4242"
                                        autoComplete="cc-number"
                                        required
                                        className="pr-24"
                                    />
                                    <span className="pointer-events-none absolute top-1/2 right-3 flex -translate-y-1/2 items-center gap-1 text-xs text-muted-foreground">
                                        <CreditCard className="size-4" />
                                        {brand !== null && brandLabels[brand]}
                                    </span>
                                </div>
                                <InputError message={form.errors.card_number} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="card_expiry">
                                    {t('checkout.card_expiry')}
                                </Label>
                                <Input
                                    id="card_expiry"
                                    value={form.data.card_expiry}
                                    onChange={(e) =>
                                        form.setData(
                                            'card_expiry',
                                            formatExpiry(e.target.value),
                                        )
                                    }
                                    inputMode="numeric"
                                    placeholder="12/29"
                                    autoComplete="cc-exp"
                                    required
                                />
                                <InputError message={form.errors.card_expiry} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="card_cvc">
                                    {t('checkout.card_cvc')}
                                </Label>
                                <Input
                                    id="card_cvc"
                                    value={form.data.card_cvc}
                                    onChange={(e) =>
                                        form.setData(
                                            'card_cvc',
                                            e.target.value
                                                .replace(/\D/g, '')
                                                .slice(0, 4),
                                        )
                                    }
                                    inputMode="numeric"
                                    placeholder="123"
                                    autoComplete="cc-csc"
                                    required
                                />
                                <InputError message={form.errors.card_cvc} />
                            </div>
                        </div>
                    </section>

                    <InputError
                        message={translateCouponError(
                            (form.errors as Record<string, string | undefined>)
                                .coupon,
                        )}
                    />

                    <Button
                        type="submit"
                        size="lg"
                        className="w-full sm:w-auto"
                        disabled={busy}
                    >
                        {busy ? (
                            <>
                                <Spinner />
                                {t('checkout.processing')}
                            </>
                        ) : (
                            `${t('checkout.place_order')} — ${formatPrice(totals.total_cents, locale)}`
                        )}
                    </Button>
                </form>

                <aside className="h-fit w-full shrink-0 rounded-xl border p-6 lg:w-96">
                    <h2 className="font-semibold">{t('cart.summary')}</h2>
                    <ul className="mt-4 space-y-3">
                        {cart.data.items.map((item) => (
                            <li
                                key={item.id}
                                className="flex items-center gap-3"
                            >
                                <div className="relative w-12 shrink-0">
                                    <div className="aspect-square overflow-hidden rounded-md bg-muted">
                                        {item.product.image && (
                                            <img
                                                src={item.product.image.url}
                                                alt={item.product.image.alt}
                                                className="size-full object-cover"
                                            />
                                        )}
                                    </div>
                                    <span className="absolute -top-1.5 -right-1.5 flex size-4.5 items-center justify-center rounded-full bg-muted-foreground/80 text-[10px] font-semibold text-white tabular-nums">
                                        {item.quantity}
                                    </span>
                                </div>
                                <span className="flex-1 text-sm">
                                    {item.product.name}
                                </span>
                                <span className="text-sm tabular-nums">
                                    {formatPrice(item.line_total_cents, locale)}
                                </span>
                            </li>
                        ))}
                    </ul>
                    <Separator className="my-4" />
                    <dl className="space-y-2 text-sm">
                        <div className="flex justify-between">
                            <dt className="text-muted-foreground">
                                {t('cart.subtotal')}
                            </dt>
                            <dd className="tabular-nums">
                                {formatPrice(totals.subtotal_cents, locale)}
                            </dd>
                        </div>
                        {totals.discount_cents > 0 && (
                            <div className="flex justify-between text-emerald-600">
                                <dt>
                                    {t('cart.discount')}
                                    {coupon !== null && ` (${coupon})`}
                                </dt>
                                <dd className="tabular-nums">
                                    −
                                    {formatPrice(totals.discount_cents, locale)}
                                </dd>
                            </div>
                        )}
                        <div className="flex justify-between">
                            <dt className="text-muted-foreground">
                                {t('cart.shipping')}
                            </dt>
                            <dd className="tabular-nums">
                                {totals.shipping_cents === 0
                                    ? t('cart.shipping_free')
                                    : formatPrice(
                                          totals.shipping_cents,
                                          locale,
                                      )}
                            </dd>
                        </div>
                    </dl>
                    <Separator className="my-4" />
                    <div className="flex justify-between font-semibold">
                        <span>{t('cart.total')}</span>
                        <span className="tabular-nums">
                            {formatPrice(totals.total_cents, locale)}
                        </span>
                    </div>
                </aside>
            </div>
        </>
    );
}
