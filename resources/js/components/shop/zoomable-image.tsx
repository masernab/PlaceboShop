import type { PointerEvent } from 'react';
import { useRef, useState } from 'react';
import { cn } from '@/lib/utils';

type ZoomableImageProps = {
    src: string;
    alt: string;
    zoom?: number;
    className?: string;
};

export function ZoomableImage({
    src,
    alt,
    zoom = 2,
    className,
}: ZoomableImageProps) {
    const imageRef = useRef<HTMLImageElement>(null);
    const [zoomed, setZoomed] = useState(false);

    const track = (event: PointerEvent<HTMLDivElement>) => {
        if (event.pointerType !== 'mouse') {
            return;
        }

        const rect = event.currentTarget.getBoundingClientRect();
        const x = ((event.clientX - rect.left) / rect.width) * 100;
        const y = ((event.clientY - rect.top) / rect.height) * 100;

        if (imageRef.current) {
            imageRef.current.style.transformOrigin = `${x}% ${y}%`;
        }

        setZoomed(true);
    };

    const reset = () => {
        if (imageRef.current) {
            imageRef.current.style.transformOrigin = 'center';
        }

        setZoomed(false);
    };

    return (
        <div
            onPointerEnter={track}
            onPointerMove={track}
            onPointerLeave={reset}
            className={cn('size-full cursor-zoom-in', className)}
        >
            <img
                ref={imageRef}
                src={src}
                alt={alt}
                draggable={false}
                style={{ transform: zoomed ? `scale(${zoom})` : undefined }}
                className="size-full object-cover transition-transform duration-200 ease-out motion-reduce:transition-none"
            />
        </div>
    );
}
