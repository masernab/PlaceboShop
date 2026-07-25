import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { ProductForm } from '@/components/admin/product-form';
import { Button } from '@/components/ui/button';
import { index as productsIndex } from '@/routes/admin/products';
import type { AdminCategoryData } from '@/types/admin';

type CreateProps = {
    categories: { data: AdminCategoryData[] };
};

export default function AdminProductCreate({ categories }: CreateProps) {
    return (
        <>
            <Head title="New product" />

            <div className="flex items-center gap-3">
                <Button variant="ghost" size="icon" asChild>
                    <Link href={productsIndex()} aria-label="Back to products">
                        <ArrowLeft />
                    </Link>
                </Button>
                <h1 className="text-xl font-semibold tracking-tight">
                    New product
                </h1>
            </div>

            <ProductForm categories={categories.data} />
        </>
    );
}
