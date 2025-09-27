<?php

namespace App\Support;

use RuntimeException;

class S3Configuration
{
    /**
     * Resolve the configuration array used to interact with the S3 bucket.
     *
     * @return array{client: array<string, mixed>, bucket: string, region: string, endpoint: ?string, path_style: bool}
     */
    public static function resolve(): array
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
}

