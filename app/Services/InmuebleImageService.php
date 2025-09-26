<?php

namespace App\Services;

use App\Models\Inmueble;
use App\Models\InmuebleImage;
use App\Support\AddressSlugger;
use App\Support\WatermarkPathResolver;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
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
     * @param  array<int, UploadedFile|null>  $imagenes
     */
    public function storeImages(
        Inmueble $inmueble,
        array $imagenes,
        string $diskName,
        ?string $fallbackDisk = null,
    ): void {
        $imagenes = array_values(array_filter(
            $imagenes,
            static fn ($imagen) => $imagen instanceof UploadedFile,
        ));

        if (empty($imagenes)) {
            return;
        }

        $basePath = AddressSlugger::forInmueble($inmueble);

        try {
            $this->storeImagesOnDisk($inmueble, $imagenes, $diskName, $basePath);
        } catch (Throwable $primaryException) {
            report($primaryException);

            if ($fallbackDisk === null) {
                throw $primaryException;
            }

            $this->cleanupDirectoryQuietly($diskName, $basePath);

            try {
                $this->storeImagesOnDisk($inmueble, $imagenes, $fallbackDisk, $basePath);
            } catch (Throwable $fallbackException) {
                report($fallbackException);

                $this->cleanupDirectoryQuietly($fallbackDisk, $basePath);

                throw new RuntimeException(
                    sprintf(
                        'No se pudieron subir las imágenes al disco "%s" ni al alternativo "%s". Error original: %s',
                        $diskName,
                        $fallbackDisk,
                        $primaryException->getMessage(),
                    ),
                    0,
                    $fallbackException,
                );
            }
        }
    }

    /**
     * @param  array<int, UploadedFile>  $imagenes
     */
    protected function storeImagesOnDisk(
        Inmueble $inmueble,
        array $imagenes,
        string $diskName,
        string $basePath,
    ): void {
        $ordenBase = (int) $inmueble->images()->max('orden');
        $records = [];
        $storedFiles = [];

        try {
            foreach ($imagenes as $imagen) {
                $filePayload = $this->storeSingleImage($imagen, $diskName, $basePath);

                $records[] = [
                    'disk' => $diskName,
                    'path' => $filePayload['path'],
                    'url' => $filePayload['url'],
                    'orden' => $ordenBase + count($records) + 1,
                    'metadata' => $filePayload['metadata'],
                ];

                $storedFiles[] = [
                    'disk' => $diskName,
                    'paths' => $filePayload['stored_paths'],
                ];
            }
        } catch (Throwable $exception) {
            $this->cleanupStoredFiles($storedFiles);

            throw $exception;
        }

        if (empty($records)) {
            return;
        }

        try {
            $inmueble->images()->createMany($records);
        } catch (Throwable $exception) {
            $this->cleanupStoredFiles($storedFiles);

            throw $exception;
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
        $processedExtension = 'jpg';

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

        $paths = $this->resolveUniqueVariantPaths($disk, $basePath, $baseName, $originalExtension, $processedExtension);

        $storedFile = $disk->putFileAs($basePath, $image, basename($paths['original']), $visibility);

        if ($storedFile === false) {
            throw new RuntimeException(sprintf('Unable to store original image for [%s].', $originalName));
        }

        $normalizedVariant = $this->createNormalizedVariant($image);
        $normalizedContents = (string) ($normalizedVariant['contents'] ?? '');
        $this->ensureVariantContentsNotEmpty($normalizedContents, 'normalized');
        $this->storeVariantContents($disk, $paths['normalized'], $normalizedContents, $visibility);

        $watermarkedVariant = $this->createWatermarkedVariant($normalizedContents);
        $watermarkedContents = (string) ($watermarkedVariant['contents'] ?? '');
        $this->ensureVariantContentsNotEmpty($watermarkedContents, 'watermarked');
        $this->storeVariantContents($disk, $paths['watermarked'], $watermarkedContents, $visibility);

        $thumbnailVariant = $this->createThumbnailVariant($normalizedContents);
        $thumbnailContents = (string) ($thumbnailVariant['contents'] ?? '');
        $this->ensureVariantContentsNotEmpty($thumbnailContents, 'thumbnail');
        $this->storeVariantContents($disk, $paths['thumbnail'], $thumbnailContents, $visibility);

        $originalSize = $this->resolveUploadedFileSize($image);
        $normalizedSize = strlen($normalizedContents);
        $watermarkedSize = strlen($watermarkedContents);
        $thumbnailSize = strlen($thumbnailContents);

        $originalMime = $image->getMimeType() ?: $image->getClientMimeType() ?: 'image/' . $originalExtension;

        $metadata = [
            'original_name' => $originalName,
            'size' => $originalSize,
            'mime_type' => $originalMime,
            'variants' => [
                'original' => $this->filterVariantMetadata([
                    'path' => $paths['original'],
                    'url' => $disk->url($paths['original']),
                    'size' => $originalSize,
                    'mime_type' => $originalMime,
                ]),
                'normalized' => $this->filterVariantMetadata([
                    'path' => $paths['normalized'],
                    'url' => $disk->url($paths['normalized']),
                    'width' => $normalizedVariant['width'] ?? null,
                    'height' => $normalizedVariant['height'] ?? null,
                    'size' => $normalizedSize,
                    'mime_type' => 'image/jpeg',
                ]),
                'watermarked' => $this->filterVariantMetadata([
                    'path' => $paths['watermarked'],
                    'url' => $disk->url($paths['watermarked']),
                    'width' => $watermarkedVariant['width'] ?? ($normalizedVariant['width'] ?? null),
                    'height' => $watermarkedVariant['height'] ?? ($normalizedVariant['height'] ?? null),
                    'size' => $watermarkedSize,
                    'mime_type' => 'image/jpeg',
                ]),
                'thumbnail' => $this->filterVariantMetadata([
                    'path' => $paths['thumbnail'],
                    'url' => $disk->url($paths['thumbnail']),
                    'width' => $thumbnailVariant['width'] ?? null,
                    'height' => $thumbnailVariant['height'] ?? null,
                    'size' => $thumbnailSize,
                    'mime_type' => 'image/jpeg',
                ]),
            ],
        ];

        return [
            'path' => $paths['watermarked'],
            'url' => $disk->url($paths['watermarked']),
            'metadata' => $metadata,
            'stored_paths' => array_values(array_unique($paths)),
        ];
    }

    /**
     * @param  array<int, array{disk: string, paths: array<int, string>}>  $storedFiles
     */
    protected function cleanupStoredFiles(array $storedFiles): void
    {
        foreach ($storedFiles as $fileGroup) {
            $paths = array_values(array_filter(array_unique($fileGroup['paths'])));

            if (empty($paths)) {
                continue;
            }

            try {
                $disk = Storage::disk($fileGroup['disk']);

                foreach ($paths as $path) {
                    $disk->delete($path);
                }
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }

    protected function cleanupDirectoryQuietly(string $diskName, string $basePath): void
    {
        try {
            $disk = Storage::disk($diskName);

            if (! method_exists($disk, 'allFiles')) {
                return;
            }

            $files = $disk->allFiles($basePath);

            if (empty($files)) {
                return;
            }

            $disk->delete($files);
        } catch (Throwable $exception) {
            report($exception);
        }
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
                    $image = $image->place($watermark, $position, $offsetX, $offsetY);
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
        $path = WatermarkPathResolver::resolve(config('inmuebles.images.watermark.path'));

        if ($path === null || $path === '') {
            return null;
        }

        if (is_file($path)) {
            $contents = file_get_contents($path);

            return $contents === false ? null : (string) $contents;
        }

        $diskName = (string) config('inmuebles.images.watermark.disk', '');

        if ($diskName !== '') {
            try {
                $disk = Storage::disk($diskName);

                if (method_exists($disk, 'exists') && $disk->exists($path)) {
                    $contents = $disk->get($path);

                    if ($contents !== false && $contents !== null) {
                        return (string) $contents;
                    }
                }
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            try {
                $contents = file_get_contents($path);

                return $contents === false ? null : (string) $contents;
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return null;
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

            $driver = $this->resolveImageDriver();

            if ($driver !== null) {
                return new $class($driver);
            }

            return app()->make($class);
        } catch (\Throwable $exception) {
            report($exception);

            return null;
        }
    }

    protected function resolveImageDriver(): ?object
    {
        $drivers = [
            '\\Intervention\\Image\\Drivers\\Imagick\\Driver',
            '\\Intervention\\Image\\Drivers\\Gd\\Driver',
        ];

        foreach ($drivers as $driverClass) {
            if (! class_exists($driverClass)) {
                continue;
            }

            try {
                return new $driverClass();
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return null;
    }

    /**
     * @param  Filesystem|FilesystemAdapter  $disk
     * @return array{
     *     original: string,
     *     normalized: string,
     *     watermarked: string,
     *     thumbnail: string,
     * }
     */
    protected function resolveUniqueVariantPaths($disk, string $basePath, string $baseName, string $originalExtension, string $processedExtension): array
    {
        $candidateBase = $baseName;
        $counter = 1;

        while (true) {
            $paths = [
                'original' => sprintf('%s/%s_original.%s', $basePath, $candidateBase, $originalExtension),
                'normalized' => sprintf('%s/%s_normalized.%s', $basePath, $candidateBase, $processedExtension),
                'watermarked' => sprintf('%s/%s_watermarked.%s', $basePath, $candidateBase, $processedExtension),
                'thumbnail' => sprintf('%s/%s_thumbnail.%s', $basePath, $candidateBase, $processedExtension),
            ];

            if (! $this->anyPathExists($disk, $paths)) {
                return $paths;
            }

            $candidateBase = sprintf('%s_%d', $baseName, $counter);
            $counter++;
        }
    }

    /**
     * @param  Filesystem|FilesystemAdapter  $disk
     * @param  array<string, string>  $paths
     */
    protected function anyPathExists($disk, array $paths): bool
    {
        foreach ($paths as $path) {
            if ($disk->exists($path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  Filesystem|FilesystemAdapter  $disk
     * @param  array<string, mixed>  $options
     */
    protected function storeVariantContents($disk, string $path, string $contents, array $options): void
    {
        $result = $disk->put($path, $contents, $options);

        if ($result === false) {
            throw new RuntimeException(sprintf('Unable to store image variant at [%s].', $path));
        }
    }

    protected function resolveUploadedFileSize(UploadedFile $file): ?int
    {
        $size = $file->getSize();

        if (is_int($size)) {
            return $size;
        }

        $path = $file->getPathname();

        if ($path !== '' && is_file($path)) {
            $fileSize = @filesize($path);

            if ($fileSize !== false) {
                return (int) $fileSize;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    protected function filterVariantMetadata(array $metadata): array
    {
        return array_filter($metadata, static fn ($value) => $value !== null && $value !== '');
    }

    protected function ensureVariantContentsNotEmpty(string $contents, string $variant): void
    {
        if ($contents === '') {
            throw new RuntimeException(sprintf('Empty contents generated for %s variant.', $variant));
        }
    }
}
