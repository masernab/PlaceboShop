import { router, usePage } from '@inertiajs/react';
import { Globe } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useTranslation } from '@/hooks/use-translation';
import { update } from '@/routes/locale';

const locales = [
    { code: 'en', label: 'English' },
    { code: 'es', label: 'Español' },
];

export function LanguageSwitcher() {
    const { locale } = usePage().props;
    const { t } = useTranslation();

    const changeLocale = (code: string) => {
        if (code === locale) {
            return;
        }

        router.put(update.url(), { locale: code }, { preserveScroll: true });
    };

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="sm" aria-label={t('common.language')}>
                    <Globe />
                    <span className="uppercase">{locale}</span>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                {locales.map(({ code, label }) => (
                    <DropdownMenuItem
                        key={code}
                        onSelect={() => changeLocale(code)}
                        data-active={code === locale}
                        className="data-[active=true]:font-semibold"
                    >
                        {label}
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
