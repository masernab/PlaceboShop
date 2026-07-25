import { Head, Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import { ProductCard } from '@/components/shop/product-card';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';
import { index as productsIndex } from '@/routes/products';
import type { CategoryData, ProductCardData } from '@/types/shop';

type HomeProps = {
    featured: { data: ProductCardData[] };
    categories: { data: CategoryData[] };
};

export default function Home({ featured, categories }: HomeProps) {
    const { t } = useTranslation();

    return (
        <>
            <Head title={t('nav.home')} />

            <section className="rounded-2xl bg-gradient-to-br from-pink-100 via-rose-50 to-amber-50 px-6 py-16 text-center sm:py-24 dark:from-pink-950/40 dark:via-rose-950/20 dark:to-amber-950/20">
                <h1 className="mx-auto max-w-2xl text-4xl font-bold tracking-tight text-balance sm:text-5xl">
                    {t('home.hero_title')}
                </h1>
                <p className="mx-auto mt-4 max-w-xl text-lg text-muted-foreground">
                    {t('home.hero_subtitle')}
                </p>
                <Button size="lg" className="mt-8" asChild>
                    <Link href={productsIndex()}>
                        {t('home.hero_cta')}
                        <ArrowRight />
                    </Link>
                </Button>
            </section>

            {featured.data.length > 0 && (
                <section className="mt-14">
                    <div className="mb-6 flex items-center justify-between">
                        <h2 className="text-2xl font-semibold tracking-tight">
                            {t('home.featured')}
                        </h2>
                        <Button variant="ghost" size="sm" asChild>
                            <Link href={productsIndex()}>
                                {t('home.view_all')}
                                <ArrowRight />
                            </Link>
                        </Button>
                    </div>
                    <div className="grid grid-cols-2 gap-x-4 gap-y-8 sm:grid-cols-3 lg:grid-cols-4">
                        {featured.data.map((product) => (
                            <ProductCard key={product.id} product={product} />
                        ))}
                    </div>
                </section>
            )}

            <section className="mt-14">
                <h2 className="mb-6 text-2xl font-semibold tracking-tight">
                    {t('home.categories')}
                </h2>
                <div className="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    {categories.data.map((category) => (
                        <Link
                            key={category.id}
                            href={productsIndex({
                                query: { category: category.slug },
                            })}
                            className="group rounded-xl border p-6 transition-colors hover:border-pink-300 hover:bg-pink-50/50 dark:hover:border-pink-800 dark:hover:bg-pink-950/20"
                        >
                            <h3 className="font-semibold group-hover:text-pink-600">
                                {category.name}
                            </h3>
                            <p className="mt-1 line-clamp-2 text-sm text-muted-foreground">
                                {category.description}
                            </p>
                        </Link>
                    ))}
                </div>
            </section>
        </>
    );
}
