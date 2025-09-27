<?php

namespace App\Models;

use App\Support\S3Configuration;
use Aws\S3\S3Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Throwable;

class InmuebleImage extends Model
{
    use HasFactory;

    protected static ?S3Client $s3ClientInstance = null;

    protected $table = 'inmueble_imagenes';

    protected $fillable = [
        'inmueble_id',
        'disk',
        's3_key',
        'path',
        'url',
        'orden',
        'metadata',
    ];

    protected $casts = [
        'orden' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Image belongs to an inmueble.
     */
    public function inmueble(): BelongsTo
    {
        return $this->belongsTo(Inmueble::class);
    }

    /**
     * Retrieve the URL using the configured filesystem disk when not stored.
     */
    public function getUrlAttribute(?string $value): ?string
    {
        if (! empty($value)) {
            return $value;
        }

        if (empty($this->path) || empty($this->disk)) {
            return $value;
        }

        return $this->generateUrlForPath($this->path) ?? $value;
    }

    public function temporaryVariantUrl(string $variant): ?string
    {
        $variantPath = data_get($this->metadata, "variants.$variant.path");

        if (empty($variantPath) || empty($this->disk)) {
            return null;
        }

        return $this->generateUrlForPath($variantPath);
    }

    protected function generateUrlForPath(string $path): ?string
    {
        if (empty($this->disk)) {
            return null;
        }

        if ($this->disk === 's3') {
            return $this->generateS3Url($path);
        }

        $disk = Storage::disk($this->disk);
        $expiresAt = now()->addMinutes($this->urlTtlMinutes());

        if (method_exists($disk, 'temporaryUrl')) {
            try {
                return $disk->temporaryUrl($path, $expiresAt);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        if (method_exists($disk, 'url')) {
            return $disk->url($path);
        }

        return null;
    }

    protected function generateS3Url(string $path): ?string
    {
        try {
            $config = S3Configuration::resolve();
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }

        $key = $this->s3_key ?: $path;
        $key = ltrim($key, '/');

        try {
            $client = $this->resolveS3Client($config['client']);
            $command = $client->getCommand('GetObject', [
                'Bucket' => $config['bucket'],
                'Key' => $key,
            ]);

            $expiresAt = now()->addMinutes($this->urlTtlMinutes());
            $request = $client->createPresignedRequest($command, $expiresAt);

            return (string) $request->getUri();
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    protected function resolveS3Client(array $config): S3Client
    {
        if (static::$s3ClientInstance instanceof S3Client) {
            return static::$s3ClientInstance;
        }

        static::$s3ClientInstance = new S3Client($config);

        return static::$s3ClientInstance;
    }

    protected function urlTtlMinutes(): int
    {
        return (int) config('inmuebles.images.url_ttl_minutes', 60);
    }
}
