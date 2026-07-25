import { Check } from 'lucide-react';
import { orderStatusLabels } from '@/components/shop/order-status-badge';
import { useTranslation } from '@/hooks/use-translation';
import { cn } from '@/lib/utils';
import type { TimelineEntry } from '@/types/shop';

type TrackingTimelineProps = {
    timeline: TimelineEntry[];
    cancelled: boolean;
};

export function TrackingTimeline({
    timeline,
    cancelled,
}: TrackingTimelineProps) {
    const { t, locale } = useTranslation();

    const formatDate = (iso: string) =>
        new Intl.DateTimeFormat(locale, {
            dateStyle: 'medium',
            timeStyle: 'short',
        }).format(new Date(iso));

    const lastReachedIndex = timeline.reduce(
        (last, entry, index) => (entry.reached ? index : last),
        0,
    );

    return (
        <ol className="space-y-0">
            {timeline.map((entry, index) => {
                const isCurrent = !cancelled && index === lastReachedIndex;
                const done = entry.reached && !cancelled;

                return (
                    <li key={entry.status} className="flex gap-3">
                        <div className="flex flex-col items-center">
                            <span
                                className={cn(
                                    'flex size-6 shrink-0 items-center justify-center rounded-full border-2 text-white',
                                    done
                                        ? 'border-pink-500 bg-pink-500'
                                        : 'border-muted-foreground/30 bg-background',
                                    isCurrent && 'ring-4 ring-pink-500/20',
                                )}
                            >
                                {done && <Check className="size-3.5" />}
                            </span>
                            {index < timeline.length - 1 && (
                                <span
                                    className={cn(
                                        'w-0.5 flex-1',
                                        done && index < lastReachedIndex
                                            ? 'bg-pink-500'
                                            : 'bg-muted-foreground/20',
                                    )}
                                />
                            )}
                        </div>
                        <div className="pb-6">
                            <p
                                className={cn(
                                    'text-sm font-medium',
                                    !done && 'text-muted-foreground',
                                )}
                            >
                                {t(orderStatusLabels[entry.status])}
                            </p>
                            <p className="text-xs text-muted-foreground tabular-nums">
                                {formatDate(entry.at)}
                            </p>
                        </div>
                    </li>
                );
            })}
        </ol>
    );
}
