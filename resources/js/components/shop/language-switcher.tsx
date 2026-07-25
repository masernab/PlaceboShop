import { router, usePage } from '@inertiajs/react';
import { Globe } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { update } from '@/routes/locale';

const locales = [
    { code: 'en', label: 'English' },
    { code: 'es', label: 'Español' },
];

export function LanguageSwitcher() {
    const { locale } = usePage().props;

    const nextLocale =
        locales.find(({ code }) => code !== locale) ?? locales[0];

    const toggle = () => {
        router.put(
            update.url(),
            { locale: nextLocale.code },
            { preserveScroll: true },
        );
    };

    return (
        <Button
            variant="ghost"
            size="sm"
            onClick={toggle}
            aria-label={nextLocale.label}
        >
            <Globe />
            <span className="uppercase">{locale}</span>
        </Button>
    );
}
