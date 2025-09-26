<?php

namespace App\Services;

use App\Models\Inmueble;
use App\Models\InmuebleImage;
use App\Support\AddressSlugger;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class InmuebleImageService
{
    /**
     * @param  array<int, UploadedFile>  $imagenes
     */
    public function storeImages(Inmueble $inmueble, array $imagenes, string $diskName): void
    {
        if (empty($imagenes)) {
            return;
        }

        $disk = Storage::disk($diskName);
        $basePath = $this->resolveBasePath($inmueble);
        $ordenBase = (int) $inmueble->images()->max('orden');

        foreach ($imagenes as $index => $imagen) {
            if (! $imagen instanceof UploadedFile) {
                continue;
            }

            $filePayload = $this->storeSingleImage($disk, $imagen, $basePath);

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

    /**
     * @param  Filesystem|FilesystemAdapter  $disk
     */
    protected function storeSingleImage($disk, UploadedFile $image, string $basePath): array
    {
        $visibility = ['visibility' => 'public'];

        $originalName = $image->getClientOriginalName() ?: $image->hashName();
        $extension = strtolower($image->getClientOriginalExtension() ?: $image->guessExtension() ?: 'jpg');

        $fileName = sprintf(
            '%s_%s.%s',
            Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'imagen',
            Str::random(12),
            $extension
        );

        $storedPath = $disk->putFileAs($basePath, $image, $fileName, $visibility);

        if ($storedPath === false) {
            throw new RuntimeException(sprintf('Unable to store image [%s].', $originalName));
        }

        $size = $this->resolveUploadedFileSize($image);
        $mimeType = $image->getMimeType() ?: $image->getClientMimeType();

        return [
            'path' => $storedPath,
            'url' => $disk->url($storedPath),
            'metadata' => $this->filterMetadata([
                'original_name' => $originalName,
                'size' => $size,
                'mime_type' => $mimeType,
            ]),
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

    protected function resolveBasePath(Inmueble $inmueble): string
    {
        $slug = AddressSlugger::forInmueble($inmueble);

        if ($slug !== '') {
            return 'inmuebles/' . $slug;
        }

        return 'inmuebles/inmueble_' . $inmueble->id;
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
    protected function filterMetadata(array $metadata): array
    {
        return array_filter($metadata, static fn ($value) => $value !== null && $value !== '');
    }
}
