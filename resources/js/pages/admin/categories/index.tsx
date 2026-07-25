import { Head, router, useForm } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import { toast } from 'sonner';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    destroy as categoryDestroy,
    store as categoryStore,
    update as categoryUpdate,
} from '@/routes/admin/categories';
import type { AdminCategoryData } from '@/types/admin';

type CategoriesIndexProps = {
    categories: { data: AdminCategoryData[] };
};

function CategoryDialog({
    category,
    onClose,
}: {
    category: AdminCategoryData | null;
    onClose: () => void;
}) {
    const form = useForm({
        name: { en: category?.name.en ?? '', es: category?.name.es ?? '' },
        description: {
            en: category?.description?.en ?? '',
            es: category?.description?.es ?? '',
        },
        position: category?.position ?? 0,
    });

    const errors = form.errors as Record<string, string | undefined>;

    const submit = (event: FormEvent) => {
        event.preventDefault();

        const options = {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Category saved');
                onClose();
            },
        };

        if (category) {
            form.put(categoryUpdate.url(category.id), options);
        } else {
            form.post(categoryStore.url(), options);
        }
    };

    return (
        <DialogContent>
            <DialogHeader>
                <DialogTitle>
                    {category ? `Edit ${category.name.en}` : 'New category'}
                </DialogTitle>
            </DialogHeader>
            <form onSubmit={submit} className="space-y-4">
                <div className="grid gap-4 sm:grid-cols-2">
                    <div className="grid gap-2">
                        <Label htmlFor="cat-name-en">Name (EN)</Label>
                        <Input
                            id="cat-name-en"
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
                        <Label htmlFor="cat-name-es">Name (ES)</Label>
                        <Input
                            id="cat-name-es"
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
                        <Label htmlFor="cat-desc-en">Description (EN)</Label>
                        <Input
                            id="cat-desc-en"
                            value={form.data.description.en}
                            onChange={(event) =>
                                form.setData('description', {
                                    ...form.data.description,
                                    en: event.target.value,
                                })
                            }
                        />
                        <InputError message={errors['description.en']} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="cat-desc-es">Description (ES)</Label>
                        <Input
                            id="cat-desc-es"
                            value={form.data.description.es}
                            onChange={(event) =>
                                form.setData('description', {
                                    ...form.data.description,
                                    es: event.target.value,
                                })
                            }
                        />
                        <InputError message={errors['description.es']} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="cat-position">Position</Label>
                        <Input
                            id="cat-position"
                            type="number"
                            min="0"
                            value={form.data.position}
                            onChange={(event) =>
                                form.setData(
                                    'position',
                                    Number(event.target.value),
                                )
                            }
                            required
                        />
                        <InputError message={errors.position} />
                    </div>
                </div>
                <Button type="submit" disabled={form.processing}>
                    {category ? 'Save changes' : 'Create category'}
                </Button>
            </form>
        </DialogContent>
    );
}

export default function AdminCategoriesIndex({
    categories,
}: CategoriesIndexProps) {
    const [editing, setEditing] = useState<AdminCategoryData | 'new' | null>(
        null,
    );

    const destroy = (category: AdminCategoryData) => {
        router.delete(categoryDestroy.url(category.id), {
            preserveScroll: true,
            onError: (errors) =>
                toast.error(errors.category ?? 'Could not delete category'),
        });
    };

    return (
        <>
            <Head title="Categories" />

            <div className="flex items-center justify-between">
                <h1 className="text-xl font-semibold tracking-tight">
                    Categories
                </h1>
                <Button onClick={() => setEditing('new')}>
                    <Plus />
                    New category
                </Button>
            </div>

            <Card>
                <CardContent>
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b text-left text-muted-foreground">
                                <th className="py-2 font-medium">Name (EN)</th>
                                <th className="hidden py-2 font-medium sm:table-cell">
                                    Name (ES)
                                </th>
                                <th className="py-2 text-right font-medium">
                                    Position
                                </th>
                                <th className="py-2 text-right font-medium">
                                    Products
                                </th>
                                <th className="py-2 text-right font-medium">
                                    <span className="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {categories.data.map((category) => (
                                <tr key={category.id} className="border-b">
                                    <td className="py-2.5 font-medium">
                                        {category.name.en}
                                    </td>
                                    <td className="hidden py-2.5 sm:table-cell">
                                        {category.name.es}
                                    </td>
                                    <td className="py-2.5 text-right tabular-nums">
                                        {category.position}
                                    </td>
                                    <td className="py-2.5 text-right tabular-nums">
                                        {category.products_count}
                                    </td>
                                    <td className="py-2.5 text-right whitespace-nowrap">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            aria-label={`Edit ${category.name.en}`}
                                            onClick={() => setEditing(category)}
                                        >
                                            <Pencil />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            aria-label={`Delete ${category.name.en}`}
                                            onClick={() => destroy(category)}
                                            className="text-muted-foreground hover:text-destructive"
                                        >
                                            <Trash2 />
                                        </Button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
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
                    <CategoryDialog
                        key={editing === 'new' ? 'new' : editing.id}
                        category={editing === 'new' ? null : editing}
                        onClose={() => setEditing(null)}
                    />
                )}
            </Dialog>
        </>
    );
}
