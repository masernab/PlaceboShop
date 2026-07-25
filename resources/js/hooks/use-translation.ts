import { usePage } from '@inertiajs/react';
import en from '@/lang/en';
import type { TranslationKey } from '@/lang/en';
import es from '@/lang/es';

const dictionaries: Record<string, Record<TranslationKey, string>> = { en, es };

export function useTranslation() {
    const { locale } = usePage().props;

    const t = (
        key: TranslationKey,
        params?: Record<string, string | number>,
    ): string => {
        let value = dictionaries[locale]?.[key] ?? en[key];

        if (params) {
            for (const [name, replacement] of Object.entries(params)) {
                value = value.replace(`:${name}`, String(replacement));
            }
        }

        return value;
    };

    return { t, locale };
}
