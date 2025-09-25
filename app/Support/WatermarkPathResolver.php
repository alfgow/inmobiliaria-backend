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

        $normalizedPath = str_replace('\\', '/', $trimmedPath);

        if (preg_match('/^(?:[a-zA-Z]:)?\//', $normalizedPath)) {
            return $normalizedPath;
        }

        $projectPath = base_path($normalizedPath);

        if (file_exists($projectPath)) {
            return $projectPath;
        }

        return $normalizedPath;
    }
}

