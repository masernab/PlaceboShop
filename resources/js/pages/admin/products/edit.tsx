import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, ExternalLink } from 'lucide-react';
import { ProductForm } from '@/components/admin/product-form';
import { ProductImages } from '@/components/admin/product-images';
import { Button } from '@/components/ui/button';
import { index as productsIndex } from '@/routes/admin/products';
import { show as productShow } from '@/routes/products';
import type { AdminCategoryData, AdminProductData } from '@/types/admin';

type EditProps = {
    product: { data: AdminProductData };
    categories: { data: AdminCategoryData[] };
};

export default function AdminProductEdit({ product, categories }: EditProps) {
    const { data } = product;

    return (
        <>
            <Head title={`Edit ${data.name.en}`} />

            <div className="flex flex-wrap items-center justify-between gap-3">
                <div className="flex items-center gap-3">
                    <Button variant="ghost" size="icon" asChild>
                        <Link
                            href={productsIndex()}
                            aria-label="Back to products"
                        >
                            <ArrowLeft />
                        </Link>
                    </Button>
                    <h1 className="text-xl font-semibold tracking-tight">
                        {data.name.en}
                    </h1>
                </div>
                <Button variant="outline" size="sm" asChild>
                    <Link href={productShow(data.slug)}>
                        <ExternalLink />
                        View in shop
                    </Link>
                </Button>
            </div>

            <ProductForm product={data} categories={categories.data} />

            <ProductImages productId={data.id} images={data.images ?? []} />
        </>
    );
}
