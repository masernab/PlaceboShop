import { Head, router, useForm } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import { toast } from 'sonner';
import InputError from '@/components/input-error';
import { formatPrice } from '@/components/shop/price';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    destroy as couponDestroy,
    store as couponStore,
    update as couponUpdate,
} from '@/routes/admin/coupons';
import type { AdminCouponData } from '@/types/admin';

type CouponsIndexProps = {
    coupons: { data: AdminCouponData[] };
};

function CouponDialog({
    coupon,
    onClose,
}: {
    coupon: AdminCouponData | null;
    onClose: () => void;
}) {
    const form = useForm({
        code: coupon?.code ?? '',
        type: coupon?.type ?? 'percent',
        value: coupon?.value ?? 10,
        min_subtotal: coupon
            ? (coupon.min_subtotal_cents / 100).toFixed(2)
            : '',
        max_uses: coupon?.max_uses != null ? String(coupon.max_uses) : '',
        starts_at: coupon?.starts_at ?? '',
        expires_at: coupon?.expires_at ?? '',
        is_active: coupon?.is_active ?? true,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();

        form.transform((data) => ({
            ...data,
            min_subtotal: data.min_subtotal === '' ? null : data.min_subtotal,
            max_uses: data.max_uses === '' ? null : data.max_uses,
            starts_at: data.starts_at === '' ? null : data.starts_at,
            expires_at: data.expires_at === '' ? null : data.expires_at,
        }));

        const options = {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Coupon saved');
                onClose();
            },
        };

        if (coupon) {
            form.put(couponUpdate.url(coupon.id), options);
        } else {
            form.post(couponStore.url(), options);
        }
    };

    return (
        <DialogContent>
            <DialogHeader>
                <DialogTitle>
                    {coupon ? `Edit ${coupon.code}` : 'New coupon'}
                </DialogTitle>
            </DialogHeader>
            <form onSubmit={submit} className="space-y-4">
                <div className="grid gap-4 sm:grid-cols-2">
                    <div className="grid gap-2">
                        <Label htmlFor="coupon-code">Code</Label>
                        <Input
                            id="coupon-code"
                            value={form.data.code}
                            onChange={(event) =>
                                form.setData(
                                    'code',
                                    event.target.value.toUpperCase(),
                                )
                            }
                            required
                        />
                        <InputError message={form.errors.code} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="coupon-type">Type</Label>
                        <Select
                            value={form.data.type}
                            onValueChange={(value) =>
                                form.setData(
                                    'type',
                                    value as 'percent' | 'fixed',
                                )
                            }
                        >
                            <SelectTrigger id="coupon-type">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="percent">
                                    Percent (%)
                                </SelectItem>
                                <SelectItem value="fixed">
                                    Fixed ($, in cents)
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError message={form.errors.type} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="coupon-value">
                            {form.data.type === 'percent'
                                ? 'Value (%)'
                                : 'Value (cents)'}
                        </Label>
                        <Input
                            id="coupon-value"
                            type="number"
                            min="1"
                            value={form.data.value}
                            onChange={(event) =>
                                form.setData(
                                    'value',
                                    Number(event.target.value),
                                )
                            }
                            required
                        />
                        <InputError message={form.errors.value} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="coupon-min">Min subtotal ($)</Label>
                        <Input
                            id="coupon-min"
                            type="number"
                            min="0"
                            step="0.01"
                            value={form.data.min_subtotal}
                            onChange={(event) =>
                                form.setData('min_subtotal', event.target.value)
                            }
                        />
                        <InputError message={form.errors.min_subtotal} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="coupon-max-uses">Max uses</Label>
                        <Input
                            id="coupon-max-uses"
                            type="number"
                            min="1"
                            value={form.data.max_uses}
                            onChange={(event) =>
                                form.setData('max_uses', event.target.value)
                            }
                        />
                        <InputError message={form.errors.max_uses} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="coupon-starts">Starts</Label>
                        <Input
                            id="coupon-starts"
                            type="date"
                            value={form.data.starts_at}
                            onChange={(event) =>
                                form.setData('starts_at', event.target.value)
                            }
                        />
                        <InputError message={form.errors.starts_at} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="coupon-expires">Expires</Label>
                        <Input
                            id="coupon-expires"
                            type="date"
                            value={form.data.expires_at}
                            onChange={(event) =>
                                form.setData('expires_at', event.target.value)
                            }
                        />
                        <InputError message={form.errors.expires_at} />
                    </div>
                </div>
                <label className="flex items-center gap-2 text-sm">
                    <Checkbox
                        checked={form.data.is_active}
                        onCheckedChange={(checked) =>
                            form.setData('is_active', checked === true)
                        }
                    />
                    Active
                </label>
                <Button type="submit" disabled={form.processing}>
                    {coupon ? 'Save changes' : 'Create coupon'}
                </Button>
            </form>
        </DialogContent>
    );
}

export default function AdminCouponsIndex({ coupons }: CouponsIndexProps) {
    const [editing, setEditing] = useState<AdminCouponData | 'new' | null>(
        null,
    );

    const destroy = (coupon: AdminCouponData) => {
        router.delete(couponDestroy.url(coupon.id), { preserveScroll: true });
    };

    const describeValue = (coupon: AdminCouponData) =>
        coupon.type === 'percent'
            ? `${coupon.value}%`
            : formatPrice(coupon.value, 'en-US');

    return (
        <>
            <Head title="Coupons" />

            <div className="flex items-center justify-between">
                <h1 className="text-xl font-semibold tracking-tight">
                    Coupons
                </h1>
                <Button onClick={() => setEditing('new')}>
                    <Plus />
                    New coupon
                </Button>
            </div>

            <Card>
                <CardContent>
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b text-left text-muted-foreground">
                                    <th className="py-2 font-medium">Code</th>
                                    <th className="py-2 font-medium">
                                        Discount
                                    </th>
                                    <th className="hidden py-2 font-medium sm:table-cell">
                                        Min subtotal
                                    </th>
                                    <th className="hidden py-2 font-medium md:table-cell">
                                        Uses
                                    </th>
                                    <th className="hidden py-2 font-medium md:table-cell">
                                        Window
                                    </th>
                                    <th className="py-2 text-right font-medium">
                                        Status
                                    </th>
                                    <th className="py-2 text-right font-medium">
                                        <span className="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {coupons.data.map((coupon) => (
                                    <tr key={coupon.id} className="border-b">
                                        <td className="py-2.5 font-mono font-medium">
                                            {coupon.code}
                                        </td>
                                        <td className="py-2.5 tabular-nums">
                                            {describeValue(coupon)}
                                        </td>
                                        <td className="hidden py-2.5 tabular-nums sm:table-cell">
                                            {coupon.min_subtotal_cents > 0
                                                ? formatPrice(
                                                      coupon.min_subtotal_cents,
                                                      'en-US',
                                                  )
                                                : '—'}
                                        </td>
                                        <td className="hidden py-2.5 tabular-nums md:table-cell">
                                            {coupon.used_count}
                                            {coupon.max_uses !== null &&
                                                ` / ${coupon.max_uses}`}
                                        </td>
                                        <td className="hidden py-2.5 text-muted-foreground md:table-cell">
                                            {coupon.starts_at ?? '—'} →{' '}
                                            {coupon.expires_at ?? '∞'}
                                        </td>
                                        <td className="py-2.5 text-right">
                                            <Badge
                                                variant={
                                                    coupon.is_active
                                                        ? 'secondary'
                                                        : 'outline'
                                                }
                                            >
                                                {coupon.is_active
                                                    ? 'Active'
                                                    : 'Inactive'}
                                            </Badge>
                                        </td>
                                        <td className="py-2.5 text-right whitespace-nowrap">
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                aria-label={`Edit ${coupon.code}`}
                                                onClick={() =>
                                                    setEditing(coupon)
                                                }
                                            >
                                                <Pencil />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                aria-label={`Delete ${coupon.code}`}
                                                onClick={() => destroy(coupon)}
                                                className="text-muted-foreground hover:text-destructive"
                                            >
                                                <Trash2 />
                                            </Button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <Dialog
                open={editing !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setEditing(null);
                    }
                }}
            >
                {editing !== null && (
                    <CouponDialog
                        key={editing === 'new' ? 'new' : editing.id}
                        coupon={editing === 'new' ? null : editing}
                        onClose={() => setEditing(null)}
                    />
                )}
            </Dialog>
        </>
    );
}
