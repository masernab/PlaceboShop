import { Head, Link } from '@inertiajs/react';
import { PackageSearch, Sparkles } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';

type ErrorPageProps = {
    status: number;
};

// Rendered outside the normal middleware stack for unknown URLs, so it
// deliberately avoids the shop layout and its shared props (auth, cart…).
export default function ErrorPage({ status }: ErrorPageProps) {
    const { t } = useTranslation();

    return (
        <>
            <Head title={t('error.404_title')} />
            <div className="flex min-h-screen flex-col items-center justify-center gap-6 bg-background px-6 text-center text-foreground">
                <PackageSearch className="size-14 text-pink-500" />
                <div>
                    <p className="text-sm font-semibold text-muted-foreground tabular-nums">
                        {status}
                    </p>
                    <h1 className="mt-1 text-3xl font-bold tracking-tight">
                        {t('error.404_title')}
                    </h1>
                    <p className="mx-auto mt-3 max-w-md text-muted-foreground">
                        {t('error.404_body')}
                    </p>
                </div>
                <Button size="lg" asChild>
                    <Link href="/">
                        <Sparkles />
                        {t('error.cta')}
                    </Link>
                </Button>
            </div>
        </>
    );
}
