<?php

namespace App\Services;

use App\Models\Inmueble;
use App\Models\InmuebleImage;
use App\Support\AddressSlugger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;
use RuntimeException;

class InmuebleImageService
{
    /**
     * @var object|null
     */
    protected $imageManager;

    public function __construct($imageManager = null)
    {
        $this->imageManager = $imageManager ?: $this->resolveImageManager();
    }

    /**
     * @param  array<int, UploadedFile>  $imagenes
     */
    public function storeImages(Inmueble $inmueble, array $imagenes, string $diskName): void
    {
        if (empty($imagenes)) {
            return;
        }

        $basePath = AddressSlugger::forInmueble($inmueble);
        $ordenBase = (int) $inmueble->images()->max('orden');

        foreach ($imagenes as $index => $imagen) {
            if (! $imagen instanceof UploadedFile) {
                continue;
            }

            $filePayload = $this->storeSingleImage($imagen, $diskName, $basePath);

            $inmueble->images()->create([
                'disk' => $diskName,
                'path' => $filePayload['path'],
                'url' => $filePayload['url'],
                'orden' => $ordenBase + $index + 1,
                'metadata' => $filePayload['metadata'],
            ]);
        }
    }

    public function deleteImage(InmuebleImage $image): void
    {
        $disk = Storage::disk($image->disk);
        $paths = $this->collectPathsForDeletion($image);

        foreach ($paths as $path) {
            $disk->delete($path);
        }

        $image->delete();
    }

    protected function storeSingleImage(UploadedFile $image, string $diskName, string $basePath): array
    {
        $disk = Storage::disk($diskName);
        $visibility = ['visibility' => 'public'];

        $originalName = $image->getClientOriginalName() ?: $image->hashName();
        $originalExtension = strtolower($image->getClientOriginalExtension() ?: $image->guessExtension() ?: 'jpg');

        $baseName = Str::of(pathinfo($originalName, PATHINFO_FILENAME))
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->replaceMatches('/_+/', '_')
            ->toString();

        if ($baseName === '') {
            $baseName = 'imagen';
        }

        $baseName .= '_' . Str::lower(Str::random(6));

        $paths = [
            'original' => sprintf('%s/%s_original.%s', $basePath, $baseName, $originalExtension),
            'normalized' => sprintf('%s/%s_normalized.jpg', $basePath, $baseName),
            'watermarked' => sprintf('%s/%s_watermarked.jpg', $basePath, $baseName),
            'thumbnail' => sprintf('%s/%s_thumbnail.jpg', $basePath, $baseName),
        ];

        $disk->putFileAs($basePath, $image, basename($paths['original']), $visibility);
        $originalUrl = $disk->url($paths['original']);

        $normalizedData = $this->createNormalizedVariant($image);
        $disk->put($paths['normalized'], $normalizedData['contents'], $visibility);
        $normalizedUrl = $disk->url($paths['normalized']);

        $watermarkData = $this->createWatermarkedVariant($normalizedData['contents']);
        $disk->put($paths['watermarked'], $watermarkData['contents'], $visibility);
        $watermarkUrl = $disk->url($paths['watermarked']);

        $thumbnailData = $this->createThumbnailVariant($normalizedData['contents']);
        $disk->put($paths['thumbnail'], $thumbnailData['contents'], $visibility);
        $thumbnailUrl = $disk->url($paths['thumbnail']);

        $metadata = [
            'original_name' => $originalName,
            'size' => $image->getSize(),
            'mime_type' => $image->getMimeType(),
            'variants' => [
                'original' => [
                    'path' => $paths['original'],
                    'url' => $originalUrl,
                ],
                'normalized' => [
                    'path' => $paths['normalized'],
                    'url' => $normalizedUrl,
                    'width' => $normalizedData['width'] ?? null,
                    'height' => $normalizedData['height'] ?? null,
                ],
                'watermarked' => [
                    'path' => $paths['watermarked'],
                    'url' => $watermarkUrl,
                    'width' => $watermarkData['width'] ?? null,
                    'height' => $watermarkData['height'] ?? null,
                ],
                'thumbnail' => [
                    'path' => $paths['thumbnail'],
                    'url' => $thumbnailUrl,
                    'width' => $thumbnailData['width'] ?? null,
                    'height' => $thumbnailData['height'] ?? null,
                ],
            ],
        ];

        return [
            'path' => $paths['watermarked'],
            'url' => $watermarkUrl,
            'metadata' => $metadata,
        ];
    }

    protected function collectPathsForDeletion(InmuebleImage $image): array
    {
        $paths = [];

        if (! empty($image->path)) {
            $paths[] = $image->path;
        }

        $variants = Arr::get($image->metadata, 'variants', []);

        foreach ($variants as $variant) {
            if (! empty($variant['path'])) {
                $paths[] = $variant['path'];
            }
        }

        return array_values(array_unique($paths));
    }

    protected function createNormalizedVariant(UploadedFile $source): array
    {
        $width = (int) config('inmuebles.images.normalized.width', 1200);
        $height = (int) config('inmuebles.images.normalized.height', 800);
        $quality = (int) config('inmuebles.images.quality', 85);

        if ($this->imageManager) {
            $image = $this->imageManager->read($source->getPathname());
            $this->resizeKeepingAspectRatio($image, $width, $height);

            $encoded = $this->encodeJpeg($image, $quality);

            return [
                'contents' => $encoded,
                'width' => $this->extractDimension($image, 'width'),
                'height' => $this->extractDimension($image, 'height'),
            ];
        }

        return [
            'contents' => (string) file_get_contents($source->getPathname()),
        ];
    }

    protected function createWatermarkedVariant(string $normalizedContents): array
    {
        $quality = (int) config('inmuebles.images.quality', 85);
        $watermark = $this->loadWatermarkImage();
        $position = config('inmuebles.images.watermark.position', 'bottom-right');
        $offsetX = (int) config('inmuebles.images.watermark.offset_x', 24);
        $offsetY = (int) config('inmuebles.images.watermark.offset_y', 24);

        if ($this->imageManager) {
            $image = $this->imageManager->read($normalizedContents);

            if ($watermark) {
                if (method_exists($image, 'place')) {
                    $image->place($watermark, $position, $offsetX, $offsetY);
                } elseif (method_exists($image, 'insert')) {
                    $image->insert($watermark, $position, $offsetX, $offsetY);
                }
            }

            $encoded = $this->encodeJpeg($image, $quality);

            return [
                'contents' => $encoded,
                'width' => $this->extractDimension($image, 'width'),
                'height' => $this->extractDimension($image, 'height'),
            ];
        }

        return [
            'contents' => $normalizedContents,
        ];
    }

    protected function loadWatermarkImage(): ?object
    {
        if (! $this->imageManager) {
            return null;
        }

        $contents = $this->resolveWatermarkContents();

        if ($contents === null) {
            return null;
        }

        try {
            return $this->imageManager->read($contents);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    protected function resolveWatermarkContents(): ?string
    {
        $path = trim((string) config('inmuebles.images.watermark.path', ''));

        if ($path === '' || ! is_file($path)) {
            return null;
        }

        try {
            $contents = file_get_contents($path);

            return $contents === false ? null : (string) $contents;
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    protected function createThumbnailVariant(string $normalizedContents): array
    {
        $width = (int) config('inmuebles.images.thumbnail.width', 300);
        $height = (int) config('inmuebles.images.thumbnail.height', 200);
        $quality = (int) config('inmuebles.images.quality', 85);

        if ($this->imageManager) {
            $image = $this->imageManager->read($normalizedContents);
            $this->resizeKeepingAspectRatio($image, $width, $height);

            $encoded = $this->encodeJpeg($image, $quality);

            return [
                'contents' => $encoded,
                'width' => $this->extractDimension($image, 'width'),
                'height' => $this->extractDimension($image, 'height'),
            ];
        }

        return [
            'contents' => $normalizedContents,
        ];
    }

    protected function resizeKeepingAspectRatio($image, int $width, int $height): void
    {
        if (method_exists($image, 'scaleDown')) {
            $image->scaleDown(width: $width, height: $height);

            return;
        }

        if (method_exists($image, 'resize')) {
            $image->resize($width, $height, function ($constraint): void {
                if (is_object($constraint) && method_exists($constraint, 'aspectRatio')) {
                    $constraint->aspectRatio();
                }

                if (is_object($constraint) && method_exists($constraint, 'upsize')) {
                    $constraint->upsize();
                }
            });
        }
    }

    protected function encodeJpeg($image, int $quality): string
    {
        if (method_exists($image, 'toJpeg')) {
            $encoded = $image->toJpeg($quality);

            if (is_object($encoded) && method_exists($encoded, 'toString')) {
                return (string) $encoded->toString();
            }

            return (string) $encoded;
        }

        if (method_exists($image, 'encode')) {
            $encoded = $image->encode('jpg', $quality);

            return (string) $encoded;
        }

        throw new RuntimeException('Unable to encode image to JPEG format.');
    }

    protected function extractDimension($image, string $method): ?int
    {
        if (method_exists($image, $method)) {
            $value = $image->{$method}();

            return is_numeric($value) ? (int) $value : null;
        }

        return null;
    }

    protected function resolveImageManager(): ?object
    {
        $class = '\\Intervention\\Image\\ImageManager';

        if (! class_exists($class)) {
            return null;
        }

        try {
            if (app()->bound($class)) {
                return app($class);
            }

            return app()->make($class);
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
