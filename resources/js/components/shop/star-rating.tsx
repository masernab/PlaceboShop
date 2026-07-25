import { Star } from 'lucide-react';
import { cn } from '@/lib/utils';

const STARS = [1, 2, 3, 4, 5];

type StarRatingProps = {
    value: number;
    onChange?: (value: number) => void;
    size?: 'sm' | 'md';
    label?: string;
};

export function StarRating({
    value,
    onChange,
    size = 'sm',
    label,
}: StarRatingProps) {
    const starSize = size === 'sm' ? 'size-4' : 'size-6';

    if (onChange) {
        return (
            <div className="flex gap-0.5" role="radiogroup" aria-label={label}>
                {STARS.map((star) => (
                    <button
                        key={star}
                        type="button"
                        role="radio"
                        aria-checked={value === star}
                        aria-label={String(star)}
                        onClick={() => onChange(star)}
                        className="transition-transform hover:scale-110"
                    >
                        <Star
                            className={cn(
                                starSize,
                                star <= value
                                    ? 'fill-amber-400 text-amber-400'
                                    : 'text-muted-foreground/40',
                            )}
                        />
                    </button>
                ))}
            </div>
        );
    }

    return (
        <div className="flex gap-0.5" aria-label={label}>
            {STARS.map((star) => (
                <Star
                    key={star}
                    className={cn(
                        starSize,
                        star <= Math.round(value)
                            ? 'fill-amber-400 text-amber-400'
                            : 'text-muted-foreground/40',
                    )}
                />
            ))}
        </div>
    );
}
