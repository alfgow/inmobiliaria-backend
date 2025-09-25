<?php

namespace App\Support;

class WatermarkPathResolver
{
    public static function resolve(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $trimmedPath = trim($path);

        if ($trimmedPath === '') {
            return null;
        }

        $expandedPath = self::expandEnvironmentVariables($trimmedPath);
        $normalizedPath = str_replace('\\', '/', $expandedPath);

        if (preg_match('/^(?:[a-zA-Z]:)?\//', $normalizedPath)) {
            return $normalizedPath;
        }

        $projectPath = base_path($normalizedPath);

        if (file_exists($projectPath)) {
            return $projectPath;
        }

        return $normalizedPath;
    }

    protected static function expandEnvironmentVariables(string $path): string
    {
        return preg_replace_callback(
            '/\$\{([A-Z0-9_]+)\}|\$([A-Z0-9_]+)/i',
            static function (array $matches): string {
                $variable = $matches[1] ?? $matches[2] ?? '';

                if ($variable === '') {
                    return $matches[0];
                }

                $value = env($variable);

                if ($value === null && strtoupper($variable) === 'APP_BASE_PATH') {
                    $value = base_path();
                }

                if ($value === null) {
                    $value = $_ENV[$variable] ?? $_SERVER[$variable] ?? '';
                }

                return is_string($value) ? $value : (string) $value;
            },
            $path
        );
    }
}

