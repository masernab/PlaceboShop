import { Minus, Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';

type QuantityInputProps = {
    value: number;
    onChange: (value: number) => void;
    min?: number;
    max?: number;
    disabled?: boolean;
};

export function QuantityInput({
    value,
    onChange,
    min = 1,
    max = 99,
    disabled = false,
}: QuantityInputProps) {
    const { t } = useTranslation();

    return (
        <div className="flex items-center rounded-md border">
            <Button
                type="button"
                variant="ghost"
                size="icon"
                aria-label="-"
                disabled={disabled || value <= min}
                onClick={() => onChange(value - 1)}
            >
                <Minus />
            </Button>
            <span
                className="w-10 text-center text-sm font-medium tabular-nums"
                aria-label={t('product.quantity')}
            >
                {value}
            </span>
            <Button
                type="button"
                variant="ghost"
                size="icon"
                aria-label="+"
                disabled={disabled || value >= max}
                onClick={() => onChange(value + 1)}
            >
                <Plus />
            </Button>
        </div>
    );
}
