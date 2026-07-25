import { Moon, Sun } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useAppearance } from '@/hooks/use-appearance';
import { useTranslation } from '@/hooks/use-translation';

export function AppearanceToggle() {
    const { resolvedAppearance, updateAppearance } = useAppearance();
    const { t } = useTranslation();

    const toggle = () => {
        updateAppearance(resolvedAppearance === 'dark' ? 'light' : 'dark');
    };

    return (
        <Button
            variant="ghost"
            size="icon"
            onClick={toggle}
            aria-label={t('common.toggle_theme')}
        >
            {resolvedAppearance === 'dark' ? <Sun /> : <Moon />}
        </Button>
    );
}
