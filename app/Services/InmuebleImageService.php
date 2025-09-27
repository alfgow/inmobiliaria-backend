<?php

namespace App\Services;

use App\Models\Inmueble;
use App\Models\InmuebleImage;
use App\Support\WatermarkPathResolver;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class InmuebleImageService
{
    /**
     * @var object|null
     */
    protected $imageManager;

    protected S3Client $s3Client;

    protected string $s3Bucket;

    protected string $s3Region;

    protected ?string $s3Endpoint = null;

    protected bool $s3PathStyle = false;

    public function __construct(?S3Client $s3Client = null, $imageManager = null)
    {
        $config = $this->resolveS3Configuration();

        $this->s3Client = $s3Client ?: new S3Client($config['client']);
        $this->s3Bucket = $config['bucket'];
        $this->s3Region = $config['region'];
        $this->s3Endpoint = $config['endpoint'];
        $this->s3PathStyle = $config['path_style'];
        $this->imageManager = $imageManager ?: $this->resolveImageManager();
    }

    /**
     * @param  array<int, UploadedFile|null>  $imagenes
     */
    public function storeImages(Inmueble $inmueble, array $imagenes): void
    {
        $imagenes = array_values(array_filter(
            $imagenes,
            static fn ($imagen) => $imagen instanceof UploadedFile,
        ));

        Log::debug('Iniciando almacenamiento de imágenes del inmueble.', [
            'inmueble_id' => $inmueble->id,
            'total_archivos' => count($imagenes),
        ]);

        if (empty($imagenes)) {
            return;
        }

        $baseFolder = $this->buildInmuebleFolder($inmueble);
        $ordenBase = (int) $inmueble->images()->max('orden');
        $records = [];
        $uploadedKeys = [];

        try {
            foreach ($imagenes as $index => $imagen) {
                $sequence = $ordenBase + $index + 1;
                $keyPrefix = sprintf('%s/foto%d', $baseFolder, $sequence);

                Log::debug('Procesando imagen para inmueble.', [
                    'inmueble_id' => $inmueble->id,
                    'key_prefix' => $keyPrefix,
                    'orden' => $sequence,
                ]);

                $filePayload = $this->storeSingleImage($imagen, $keyPrefix);

                $filename = basename($filePayload['path']);
                $baseFolderNormalized = trim($baseFolder, '/');
                $s3Key = $filePayload['path'];

                if ($baseFolderNormalized !== '' && $filename !== '') {
                    $s3Key = $baseFolderNormalized . '/' . $filename;
                }

                $records[] = [
                    'disk' => 's3',
                    's3_key' => $s3Key,
                    'path' => $filePayload['path'],
                    'url' => null,
                    'orden' => $sequence,
                    'metadata' => $filePayload['metadata'],
                ];

                $uploadedKeys = array_merge($uploadedKeys, $filePayload['stored_keys']);
            }
        } catch (Throwable $exception) {
            report($exception);
            $this->cleanupUploadedKeys($uploadedKeys);

            throw $exception;
        }

        if (empty($records)) {
            return;
        }

        try {
            $created = $inmueble->images()->createMany($records);

            Log::debug('Imágenes creadas en base de datos para inmueble.', [
                'inmueble_id' => $inmueble->id,
                'registros_creados' => $created->count(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
            $this->cleanupUploadedKeys($uploadedKeys);

            throw $exception;
        }
    }

    public function deleteImage(InmuebleImage $image): void
    {
        $paths = $this->collectPathsForDeletion($image);

        if ($image->disk !== 's3') {
            $disk = Storage::disk($image->disk);

            foreach ($paths as $path) {
                $disk->delete($path);
            }

            $image->delete();

            return;
        }

        if (! empty($paths)) {
            $this->cleanupUploadedKeys($paths, true);
        }

        $image->delete();
    }

    protected function buildInmuebleFolder(Inmueble $inmueble): string
    {
        $direccionSlug = $this->buildDireccionSlug($inmueble);

        if ($direccionSlug !== null) {
            return $direccionSlug;
        }

        $segments = array_filter([
            $inmueble->direccion,
            $inmueble->ciudad,
            $inmueble->estado,
        ], fn ($value) => filled($value));

        if (! empty($segments)) {
            $folder = Str::slug(implode(' ', $segments), '-');

            if ($folder !== '') {
                return $folder;
            }
        }

        return 'inmueble-' . $inmueble->id;
    }

    protected function buildDireccionSlug(Inmueble $inmueble): ?string
    {
        $direccion = trim((string) ($inmueble->direccion ?? ''));

        if ($direccion === '') {
            return null;
        }

        $slug = Str::slug($direccion, '-');

        return $slug !== '' ? $slug : null;
    }

    protected function uploadUploadedFileToS3(UploadedFile $file, string $key, string $mimeType): void
    {
        $stream = fopen($file->getPathname(), 'rb');

        if ($stream === false) {
            throw new RuntimeException(sprintf('No se pudo leer el archivo [%s] para subirlo a S3.', $file->getClientOriginalName()));
        }

        try {
            Log::debug('Subiendo archivo original a S3.', [
                'bucket' => $this->s3Bucket,
                'key' => ltrim($key, '/'),
                'content_type' => $mimeType,
            ]);

            $this->s3Client->putObject([
                'Bucket' => $this->s3Bucket,
                'Key' => ltrim($key, '/'),
                'Body' => $stream,
                'ContentType' => $mimeType,
            ]);

            Log::debug('Archivo original subido correctamente a S3.', [
                'key' => ltrim($key, '/'),
            ]);
        } catch (AwsException $exception) {
            $message = $exception->getAwsErrorMessage() ?: $exception->getMessage();

            Log::error('Error al subir archivo original a S3.', [
                'key' => ltrim($key, '/'),
                'mensaje' => $message,
            ]);

            throw new RuntimeException(
                sprintf('No se pudo subir el archivo original al bucket S3: %s', $message),
                0,
                $exception,
            );
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    protected function uploadContentsToS3(string $contents, string $key, string $mimeType): void
    {
        try {
            Log::debug('Subiendo contenido a S3.', [
                'bucket' => $this->s3Bucket,
                'key' => ltrim($key, '/'),
                'content_type' => $mimeType,
            ]);

            $this->s3Client->putObject([
                'Bucket' => $this->s3Bucket,
                'Key' => ltrim($key, '/'),
                'Body' => $contents,
                'ContentType' => $mimeType,
            ]);

            Log::debug('Contenido subido correctamente a S3.', [
                'key' => ltrim($key, '/'),
            ]);
        } catch (AwsException $exception) {
            $message = $exception->getAwsErrorMessage() ?: $exception->getMessage();

            Log::error('Error al subir contenido a S3.', [
                'key' => ltrim($key, '/'),
                'mensaje' => $message,
            ]);

            throw new RuntimeException(
                sprintf('No se pudo subir la variante de imagen al bucket S3: %s', $message),
                0,
                $exception,
            );
        }
    }

    protected function cleanupUploadedKeys(array $keys, bool $throwOnFailure = false): void
    {
        $keys = array_values(array_filter(array_unique(array_map(
            static fn ($key) => is_string($key) ? ltrim($key, '/') : null,
            $keys,
        ))));

        if (empty($keys)) {
            return;
        }

        Log::debug('Iniciando limpieza de claves en S3.', [
            'total_claves' => count($keys),
        ]);

        try {
            foreach (array_chunk($keys, 1000) as $chunk) {
                $this->s3Client->deleteObjects([
                    'Bucket' => $this->s3Bucket,
                    'Delete' => [
                        'Objects' => array_map(static fn (string $key) => ['Key' => $key], $chunk),
                        'Quiet' => true,
                    ],
                ]);
            }
        } catch (Throwable $exception) {
            report($exception);

            Log::error('Error al limpiar claves en S3.', [
                'mensaje' => $exception->getMessage(),
            ]);

            if ($throwOnFailure) {
                $message = $exception instanceof AwsException
                    ? ($exception->getAwsErrorMessage() ?: $exception->getMessage())
                    : $exception->getMessage();

                throw new RuntimeException(
                    sprintf('No se pudieron eliminar las imágenes del bucket S3: %s', $message),
                    0,
                    $exception,
                );
            }
        }
    }

    protected function resolveS3Configuration(): array
    {
        $diskConfig = (array) config('filesystems.disks.s3', []);

        $bucket = (string) ($diskConfig['bucket'] ?? env('AWS_BUCKET'));
        $region = (string) ($diskConfig['region'] ?? env('AWS_DEFAULT_REGION'));

        if ($bucket === '' || $region === '') {
            throw new RuntimeException('La configuración de S3 no está completa. Asegúrate de definir AWS_BUCKET y AWS_DEFAULT_REGION.');
        }

        $clientConfig = [
            'version' => 'latest',
            'region' => $region,
        ];

        $key = $diskConfig['key'] ?? env('AWS_ACCESS_KEY_ID');
        $secret = $diskConfig['secret'] ?? env('AWS_SECRET_ACCESS_KEY');
        $token = $diskConfig['token'] ?? env('AWS_SESSION_TOKEN');

        if ($key && $secret) {
            $credentials = ['key' => $key, 'secret' => $secret];

            if ($token) {
                $credentials['token'] = $token;
            }

            $clientConfig['credentials'] = $credentials;
        }

        $endpoint = $diskConfig['endpoint'] ?? env('AWS_ENDPOINT');

        if ($endpoint) {
            $clientConfig['endpoint'] = $endpoint;
        }

        $pathStyleRaw = $diskConfig['use_path_style_endpoint'] ?? env('AWS_USE_PATH_STYLE_ENDPOINT');
        $pathStyle = filter_var($pathStyleRaw, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false;

        if ($pathStyle) {
            $clientConfig['use_path_style_endpoint'] = true;
        }

        return [
            'client' => $clientConfig,
            'bucket' => $bucket,
            'region' => $region,
            'endpoint' => $endpoint ? (string) $endpoint : null,
            'path_style' => $pathStyle,
        ];
    }

    protected function storeSingleImage(UploadedFile $image, string $keyPrefix): array
    {
        $originalName = $image->getClientOriginalName() ?: $image->hashName();
        $originalExtension = strtolower($image->getClientOriginalExtension() ?: $image->guessExtension() ?: 'jpg');
        $processedExtension = 'jpg';

        $paths = [
            'original' => sprintf('%s_original.%s', $keyPrefix, $originalExtension),
            'normalized' => sprintf('%s_normalized.%s', $keyPrefix, $processedExtension),
            'watermarked' => sprintf('%s.%s', $keyPrefix, $processedExtension),
            'thumbnail' => sprintf('%s_thumbnail.%s', $keyPrefix, $processedExtension),
        ];

        Log::debug('Nombres de archivo calculados para la imagen.', [
            'key_prefix' => $keyPrefix,
            'paths' => $paths,
        ]);

        $originalSize = $this->resolveUploadedFileSize($image);
        $originalMime = $image->getMimeType() ?: $image->getClientMimeType() ?: 'image/' . $originalExtension;
        Log::debug('Iniciando subida de imagen original.', [
            'path' => $paths['original'],
            'mime_type' => $originalMime,
            'tamano_bytes' => $originalSize,
        ]);
        $this->uploadUploadedFileToS3($image, $paths['original'], $originalMime);
        Log::debug('Imagen original subida.', [
            'path' => $paths['original'],
        ]);

        Log::debug('Generando variante normalizada.', [
            'destino' => $paths['normalized'],
        ]);
        $normalizedVariant = $this->createNormalizedVariant($image);
        $normalizedContents = (string) ($normalizedVariant['contents'] ?? '');
        $this->ensureVariantContentsNotEmpty($normalizedContents, 'normalized');
        Log::debug('Variante normalizada generada.', [
            'destino' => $paths['normalized'],
            'tamano_bytes' => strlen($normalizedContents),
            'dimensiones' => [
                'ancho' => $normalizedVariant['width'] ?? null,
                'alto' => $normalizedVariant['height'] ?? null,
            ],
        ]);
        $this->uploadContentsToS3($normalizedContents, $paths['normalized'], 'image/jpeg');
        Log::debug('Variante normalizada subida.', [
            'destino' => $paths['normalized'],
        ]);

        Log::debug('Generando variante con marca de agua.', [
            'destino' => $paths['watermarked'],
        ]);
        $watermarkedVariant = $this->createWatermarkedVariant($normalizedContents);
        $watermarkedContents = (string) ($watermarkedVariant['contents'] ?? '');
        $this->ensureVariantContentsNotEmpty($watermarkedContents, 'watermarked');
        Log::debug('Variante con marca de agua generada.', [
            'destino' => $paths['watermarked'],
            'tamano_bytes' => strlen($watermarkedContents),
            'dimensiones' => [
                'ancho' => $watermarkedVariant['width'] ?? ($normalizedVariant['width'] ?? null),
                'alto' => $watermarkedVariant['height'] ?? ($normalizedVariant['height'] ?? null),
            ],
        ]);
        $this->uploadContentsToS3($watermarkedContents, $paths['watermarked'], 'image/jpeg');
        Log::debug('Variante con marca de agua subida.', [
            'destino' => $paths['watermarked'],
        ]);

        Log::debug('Generando miniatura.', [
            'destino' => $paths['thumbnail'],
        ]);
        $thumbnailVariant = $this->createThumbnailVariant($normalizedContents);
        $thumbnailContents = (string) ($thumbnailVariant['contents'] ?? '');
        $this->ensureVariantContentsNotEmpty($thumbnailContents, 'thumbnail');
        Log::debug('Miniatura generada.', [
            'destino' => $paths['thumbnail'],
            'tamano_bytes' => strlen($thumbnailContents),
            'dimensiones' => [
                'ancho' => $thumbnailVariant['width'] ?? null,
                'alto' => $thumbnailVariant['height'] ?? null,
            ],
        ]);
        $this->uploadContentsToS3($thumbnailContents, $paths['thumbnail'], 'image/jpeg');
        Log::debug('Miniatura subida.', [
            'destino' => $paths['thumbnail'],
        ]);

        $normalizedSize = strlen($normalizedContents);
        $watermarkedSize = strlen($watermarkedContents);
        $thumbnailSize = strlen($thumbnailContents);

        Log::debug('Tamaños finales calculados para la imagen.', [
            'original' => $originalSize,
            'normalized' => $normalizedSize,
            'watermarked' => $watermarkedSize,
            'thumbnail' => $thumbnailSize,
        ]);

        $metadata = [
            'original_name' => $originalName,
            'size' => $originalSize,
            'mime_type' => $originalMime,
            'variants' => [
                'original' => $this->filterVariantMetadata([
                    'path' => $paths['original'],
                    'size' => $originalSize,
                    'mime_type' => $originalMime,
                ]),
                'normalized' => $this->filterVariantMetadata([
                    'path' => $paths['normalized'],
                    'width' => $normalizedVariant['width'] ?? null,
                    'height' => $normalizedVariant['height'] ?? null,
                    'size' => $normalizedSize,
                    'mime_type' => 'image/jpeg',
                ]),
                'watermarked' => $this->filterVariantMetadata([
                    'path' => $paths['watermarked'],
                    'width' => $watermarkedVariant['width'] ?? ($normalizedVariant['width'] ?? null),
                    'height' => $watermarkedVariant['height'] ?? ($normalizedVariant['height'] ?? null),
                    'size' => $watermarkedSize,
                    'mime_type' => 'image/jpeg',
                ]),
                'thumbnail' => $this->filterVariantMetadata([
                    'path' => $paths['thumbnail'],
                    'width' => $thumbnailVariant['width'] ?? null,
                    'height' => $thumbnailVariant['height'] ?? null,
                    'size' => $thumbnailSize,
                    'mime_type' => 'image/jpeg',
                ]),
        ],
    ];

    return [
        'path' => $paths['watermarked'],
        'metadata' => $metadata,
        'stored_keys' => array_values(array_unique($paths)),
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
