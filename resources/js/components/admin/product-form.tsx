import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { toast } from 'sonner';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { store, update } from '@/routes/admin/products';
import type { AdminCategoryData, AdminProductData } from '@/types/admin';

type ProductFormProps = {
    product?: AdminProductData;
    categories: AdminCategoryData[];
};

export function ProductForm({ product, categories }: ProductFormProps) {
    const form = useForm({
        category_id:
            product?.category_id !== undefined
                ? String(product.category_id)
                : '',
        name: { en: product?.name.en ?? '', es: product?.name.es ?? '' },
        description: {
            en: product?.description.en ?? '',
            es: product?.description.es ?? '',
        },
        price: product ? (product.price_cents / 100).toFixed(2) : '',
        compare_at_price:
            product?.compare_at_price_cents != null
                ? (product.compare_at_price_cents / 100).toFixed(2)
                : '',
        stock: product?.stock ?? 10,
        is_active: product?.is_active ?? true,
        is_featured: product?.is_featured ?? false,
    });

    const errors = form.errors as Record<string, string | undefined>;

    const submit = (event: FormEvent) => {
        event.preventDefault();

        form.transform((data) => ({
            ...data,
            compare_at_price:
                data.compare_at_price === '' ? null : data.compare_at_price,
        }));

        const options = {
            preserveScroll: true,
            onSuccess: () => toast.success('Product saved'),
        };

        if (product) {
            form.put(update.url(product.id), options);
        } else {
            form.post(store.url(), options);
        }
    };

    return (
        <form onSubmit={submit}>
            <Card>
                <CardHeader>
                    <CardTitle>
                        {product ? 'Product details' : 'New product'}
                    </CardTitle>
                </CardHeader>
                <CardContent className="space-y-5">
                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="name-en">Name (EN)</Label>
                            <Input
                                id="name-en"
                                value={form.data.name.en}
                                onChange={(event) =>
                                    form.setData('name', {
                                        ...form.data.name,
                                        en: event.target.value,
                                    })
                                }
                                required
                            />
                            <InputError message={errors['name.en']} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="name-es">Name (ES)</Label>
                            <Input
                                id="name-es"
                                value={form.data.name.es}
                                onChange={(event) =>
                                    form.setData('name', {
                                        ...form.data.name,
                                        es: event.target.value,
                                    })
                                }
                                required
                            />
                            <InputError message={errors['name.es']} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="description-en">
                                Description (EN)
                            </Label>
                            <textarea
                                id="description-en"
                                value={form.data.description.en}
                                onChange={(event) =>
                                    form.setData('description', {
                                        ...form.data.description,
                                        en: event.target.value,
                                    })
                                }
                                rows={4}
                                required
                                className="w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            />
                            <InputError message={errors['description.en']} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="description-es">
                                Description (ES)
                            </Label>
                            <textarea
                                id="description-es"
                                value={form.data.description.es}
                                onChange={(event) =>
                                    form.setData('description', {
                                        ...form.data.description,
                                        es: event.target.value,
                                    })
                                }
                                rows={4}
                                required
                                className="w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            />
                            <InputError message={errors['description.es']} />
                        </div>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div className="grid gap-2">
                            <Label htmlFor="category">Category</Label>
                            <Select
                                value={form.data.category_id}
                                onValueChange={(value) =>
                                    form.setData('category_id', value)
                                }
                            >
                                <SelectTrigger id="category">
                                    <SelectValue placeholder="Pick one" />
                                </SelectTrigger>
                                <SelectContent>
                                    {categories.map((category) => (
                                        <SelectItem
                                            key={category.id}
                                            value={String(category.id)}
                                        >
                                            {category.name.en}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <InputError message={errors.category_id} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="price">Price ($)</Label>
                            <Input
                                id="price"
                                type="number"
                                min="0.01"
                                step="0.01"
                                value={form.data.price}
                                onChange={(event) =>
                                    form.setData('price', event.target.value)
                                }
                                required
                            />
                            <InputError message={errors.price} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="compare-at">
                                Compare-at price ($)
                            </Label>
                            <Input
                                id="compare-at"
                                type="number"
                                min="0"
                                step="0.01"
                                value={form.data.compare_at_price}
                                onChange={(event) =>
                                    form.setData(
                                        'compare_at_price',
                                        event.target.value,
                                    )
                                }
                            />
                            <InputError message={errors.compare_at_price} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="stock">Stock</Label>
                            <Input
                                id="stock"
                                type="number"
                                min="0"
                                step="1"
                                value={form.data.stock}
                                onChange={(event) =>
                                    form.setData(
                                        'stock',
                                        Number(event.target.value),
                                    )
                                }
                                required
                            />
                            <InputError message={errors.stock} />
                        </div>
                    </div>

                    <div className="flex flex-wrap gap-6">
                        <label className="flex items-center gap-2 text-sm">
                            <Checkbox
                                checked={form.data.is_active}
                                onCheckedChange={(checked) =>
                                    form.setData('is_active', checked === true)
                                }
                            />
                            Active
                        </label>
                        <label className="flex items-center gap-2 text-sm">
                            <Checkbox
                                checked={form.data.is_featured}
                                onCheckedChange={(checked) =>
                                    form.setData(
                                        'is_featured',
                                        checked === true,
                                    )
                                }
                            />
                            Featured
                        </label>
                    </div>

                    <Button type="submit" disabled={form.processing}>
                        {product ? 'Save changes' : 'Create product'}
                    </Button>
                </CardContent>
            </Card>
        </form>
    );
}
