import { router, usePage } from '@inertiajs/react';
import { ArrowDown, ArrowUp, Trash2, Upload } from 'lucide-react';
import { useRef, useState } from 'react';
import { toast } from 'sonner';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    destroy as destroyImage,
    update as updateImage,
} from '@/routes/admin/product-images';
import { store as storeImages } from '@/routes/admin/products/images';
import type { AdminProductImageData } from '@/types/admin';

type ProductImagesProps = {
    productId: number;
    images: AdminProductImageData[];
};

export function ProductImages({ productId, images }: ProductImagesProps) {
    const { errors } = usePage().props;
    const inputRef = useRef<HTMLInputElement>(null);
    const [uploading, setUploading] = useState(false);

    const upload = () => {
        const files = inputRef.current?.files;

        if (!files || files.length === 0) {
            return;
        }

        setUploading(true);

        router.post(
            storeImages.url(productId),
            { images: Array.from(files) },
            {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => {
                    if (inputRef.current) {
                        inputRef.current.value = '';
                    }

                    toast.success('Images uploaded');
                },
                onFinish: () => setUploading(false),
            },
        );
    };

    const move = (imageId: number, direction: 'up' | 'down') => {
        router.put(
            updateImage.url(imageId),
            { direction },
            { preserveScroll: true },
        );
    };

    const remove = (imageId: number) => {
        router.delete(destroyImage.url(imageId), { preserveScroll: true });
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle>Images</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
                {images.length > 0 && (
                    <ul className="flex flex-wrap gap-4">
                        {images.map((image, index) => (
                            <li key={image.id} className="w-28">
                                <div className="aspect-[4/5] overflow-hidden rounded-md bg-muted">
                                    <img
                                        src={image.url}
                                        alt={image.alt}
                                        className="size-full object-cover"
                                    />
                                </div>
                                <div className="mt-1 flex justify-center">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        className="size-7"
                                        aria-label="Move image up"
                                        disabled={index === 0}
                                        onClick={() => move(image.id, 'up')}
                                    >
                                        <ArrowUp />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        className="size-7"
                                        aria-label="Move image down"
                                        disabled={index === images.length - 1}
                                        onClick={() => move(image.id, 'down')}
                                    >
                                        <ArrowDown />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        className="size-7 text-muted-foreground hover:text-destructive"
                                        aria-label="Delete image"
                                        onClick={() => remove(image.id)}
                                    >
                                        <Trash2 />
                                    </Button>
                                </div>
                            </li>
                        ))}
                    </ul>
                )}

                <div className="flex items-center gap-2">
                    <Input
                        ref={inputRef}
                        type="file"
                        accept="image/*"
                        multiple
                        className="max-w-xs"
                    />
                    <Button
                        type="button"
                        variant="secondary"
                        disabled={uploading}
                        onClick={upload}
                    >
                        <Upload />
                        Upload
                    </Button>
                </div>
                <InputError message={errors.images as string | undefined} />
                <InputError
                    message={errors['images.0'] as string | undefined}
                />
            </CardContent>
        </Card>
    );
}
