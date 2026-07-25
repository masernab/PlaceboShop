import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { toast } from 'sonner';
import InputError from '@/components/input-error';
import { StarRating } from '@/components/shop/star-rating';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { useTranslation } from '@/hooks/use-translation';
import { store as storeReview } from '@/routes/products/reviews';

type ReviewsSectionProps = {
    productSlug: string;
    ratingAvg: number | null;
    reviewsCount: number;
    reviews: {
        id: number;
        rating: number;
        title: string | null;
        body: string;
        author: string;
        created_at: string | null;
    }[];
    canReview: boolean;
};

function ReviewForm({ productSlug }: { productSlug: string }) {
    const { t } = useTranslation();
    const form = useForm({ rating: 0, title: '', body: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();

        form.post(storeReview.url(productSlug), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                toast.success(t('reviews.submitted'));
            },
        });
    };

    return (
        <form onSubmit={submit} className="space-y-4 rounded-xl border p-5">
            <h3 className="font-semibold">{t('reviews.write')}</h3>
            <div className="grid gap-2">
                <Label>{t('reviews.rating')}</Label>
                <StarRating
                    value={form.data.rating}
                    onChange={(rating) => form.setData('rating', rating)}
                    size="md"
                    label={t('reviews.rating')}
                />
                <InputError message={form.errors.rating} />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="review-title">{t('reviews.title_label')}</Label>
                <Input
                    id="review-title"
                    value={form.data.title}
                    onChange={(event) =>
                        form.setData('title', event.target.value)
                    }
                    maxLength={120}
                />
                <InputError message={form.errors.title} />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="review-body">{t('reviews.body_label')}</Label>
                <textarea
                    id="review-body"
                    value={form.data.body}
                    onChange={(event) =>
                        form.setData('body', event.target.value)
                    }
                    rows={4}
                    required
                    className="w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-2 focus-visible:ring-ring"
                />
                <InputError message={form.errors.body} />
            </div>
            <Button type="submit" disabled={form.processing}>
                {t('reviews.submit')}
            </Button>
        </form>
    );
}

export function ReviewsSection({
    productSlug,
    ratingAvg,
    reviewsCount,
    reviews,
    canReview,
}: ReviewsSectionProps) {
    const { t, locale } = useTranslation();

    const formatDate = (iso: string | null) =>
        iso === null
            ? ''
            : new Intl.DateTimeFormat(locale, { dateStyle: 'medium' }).format(
                  new Date(iso),
              );

    const distribution = [5, 4, 3, 2, 1].map((stars) => ({
        stars,
        count: reviews.filter((review) => review.rating === stars).length,
    }));

    return (
        <section id="reviews" className="mt-16 scroll-mt-24">
            <h2 className="mb-6 text-2xl font-semibold tracking-tight">
                {t('reviews.title')}
            </h2>

            <div className="flex flex-col gap-10 lg:flex-row">
                <div className="w-full shrink-0 space-y-6 lg:w-80">
                    <div className="rounded-xl border p-5">
                        {ratingAvg === null ? (
                            <p className="text-sm text-muted-foreground">
                                {t('reviews.empty')}
                            </p>
                        ) : (
                            <>
                                <div className="flex items-baseline gap-2">
                                    <span className="text-4xl font-bold tabular-nums">
                                        {ratingAvg}
                                    </span>
                                    <div>
                                        <StarRating value={ratingAvg} />
                                        <p className="mt-0.5 text-xs text-muted-foreground">
                                            {t('reviews.count', {
                                                count: reviewsCount,
                                            })}
                                        </p>
                                    </div>
                                </div>
                                <Separator className="my-4" />
                                <div className="space-y-1.5">
                                    {distribution.map(({ stars, count }) => (
                                        <div
                                            key={stars}
                                            className="flex items-center gap-2 text-xs"
                                        >
                                            <span className="w-3 text-right tabular-nums">
                                                {stars}
                                            </span>
                                            <div className="h-2 flex-1 overflow-hidden rounded-full bg-muted">
                                                <div
                                                    className="h-full rounded-full bg-amber-400"
                                                    style={{
                                                        width:
                                                            reviews.length === 0
                                                                ? 0
                                                                : `${(count / reviews.length) * 100}%`,
                                                    }}
                                                />
                                            </div>
                                            <span className="w-5 text-muted-foreground tabular-nums">
                                                {count}
                                            </span>
                                        </div>
                                    ))}
                                </div>
                            </>
                        )}
                    </div>

                    {canReview && <ReviewForm productSlug={productSlug} />}
                </div>

                <div className="flex-1">
                    {reviews.length === 0 ? (
                        <p className="text-muted-foreground">
                            {t('reviews.empty')}
                        </p>
                    ) : (
                        <ul className="divide-y">
                            {reviews.map((review) => (
                                <li key={review.id} className="py-5">
                                    <div className="flex items-center justify-between gap-4">
                                        <StarRating value={review.rating} />
                                        <span className="text-xs text-muted-foreground">
                                            {formatDate(review.created_at)}
                                        </span>
                                    </div>
                                    {review.title !== null && (
                                        <h3 className="mt-2 text-sm font-semibold">
                                            {review.title}
                                        </h3>
                                    )}
                                    <p className="mt-1 text-sm leading-relaxed text-muted-foreground">
                                        {review.body}
                                    </p>
                                    <p className="mt-2 text-xs font-medium">
                                        {review.author}
                                    </p>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            </div>
        </section>
    );
}
