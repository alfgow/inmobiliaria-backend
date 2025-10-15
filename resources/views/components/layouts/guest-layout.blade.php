<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Inmobiliaria') }}</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>
<body class="bg-gray-900">
    <div class="min-h-screen flex items-center justify-center px-4 text-gray-100">
        {{ $slot }}
    </div>
    @fluxScripts
</body>
</html>
