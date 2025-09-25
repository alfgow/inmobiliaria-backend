<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInmuebleRequest;
use App\Http\Requests\UpdateInmuebleRequest;
use App\Models\Inmueble;
use App\Models\InmuebleStatus;
use App\Services\InmuebleImageService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class InmuebleController extends Controller
{
    public function __construct(
        private readonly InmuebleImageService $imageService,
    ) {}

    /**
     * Display a listing of the properties.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $operacion = $request->query('operacion');
        $estatus = $request->query('estatus');

        $inmueblesQuery = Inmueble::query()
            ->with(['coverImage', 'status'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $subQuery) use ($search): void {
                    $subQuery
                        ->where('titulo', 'like', "%{$search}%")
                        ->orWhere('direccion', 'like', "%{$search}%")
                        ->orWhere('ciudad', 'like', "%{$search}%")
                        ->orWhere('estado', 'like', "%{$search}%");
                });
            })
            ->when($operacion, fn (Builder $query) => $query->where('operacion', $operacion))
            ->when($estatus, fn (Builder $query) => $query->where('estatus_id', $estatus))
            ->orderByDesc('destacado')
            ->orderByDesc('updated_at');

        /** @var LengthAwarePaginator $inmuebles */
        $inmuebles = $inmueblesQuery->paginate(12)->withQueryString();

        $statusCatalog = InmuebleStatus::withCount('inmuebles')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        $totalsQuery = Inmueble::query();
        $metrics = [
            'total' => (clone $totalsQuery)->count(),
            'destacados' => (clone $totalsQuery)->where('destacado', true)->count(),
        ];

        $operationBreakdown = Inmueble::select('operacion', DB::raw('count(*) as total'))
            ->groupBy('operacion')
            ->pluck('total', 'operacion');

        return view('inmuebles.index', [
            'inmuebles' => $inmuebles,
            'statuses' => $statusCatalog,
            'metrics' => $metrics,
            'operationBreakdown' => $operationBreakdown,
            'search' => $search,
            'selectedOperacion' => $operacion,
            'selectedStatus' => $estatus,
        ]);
    }

    /**
     * Show the form for creating a new property.
     */
    public function create(): View
    {
        $statuses = InmuebleStatus::orderBy('orden')->orderBy('nombre')->get();

        return view('inmuebles.create', [
            'statuses' => $statuses,
            'tipos' => Inmueble::TIPOS,
            'operaciones' => Inmueble::OPERACIONES,
            'watermarkPreviewUrl' => $this->getWatermarkPreviewUrl(),
        ]);
    }

    /**
     * Store a newly created property in storage.
     */
    public function store(StoreInmuebleRequest $request): RedirectResponse
    {
        $payload = $this->preparePayload($request->validated(), $request);
        $imagenes = $request->file('imagenes', []);

        DB::transaction(function () use ($payload, $imagenes): void {
            $inmueble = Inmueble::create($payload);

            $this->storeImages($inmueble, $imagenes);
        });

        return redirect()
            ->route('inmuebles.index')
            ->with('status', 'Inmueble registrado correctamente.');
    }

    /**
     * Show the form for editing the specified property.
     */
    public function edit(Inmueble $inmueble): View
    {
        $inmueble->load('images', 'status');
        $statuses = InmuebleStatus::orderBy('orden')->orderBy('nombre')->get();

        return view('inmuebles.edit', [
            'inmueble' => $inmueble,
            'statuses' => $statuses,
            'tipos' => Inmueble::TIPOS,
            'operaciones' => Inmueble::OPERACIONES,
            'watermarkPreviewUrl' => $this->getWatermarkPreviewUrl(),
        ]);
    }

    /**
     * Update the specified property in storage.
     */
    public function update(UpdateInmuebleRequest $request, Inmueble $inmueble): RedirectResponse
    {
        $payload = $this->preparePayload($request->validated(), $request);
        $imagenes = $request->file('imagenes', []);
        $imagenesEliminar = collect($request->input('imagenes_eliminar', []))->filter()->all();

        DB::transaction(function () use ($inmueble, $payload, $imagenes, $imagenesEliminar): void {
            $inmueble->update($payload);

            if (! empty($imagenesEliminar)) {
                $this->deleteImages($inmueble, $imagenesEliminar);
            }

            $this->storeImages($inmueble->fresh(), $imagenes);
        });

        return redirect()
            ->route('inmuebles.edit', $inmueble)
            ->with('status', 'Inmueble actualizado correctamente.');
    }

    /**
     * Remove the specified property from storage.
     */
    public function destroy(Inmueble $inmueble): RedirectResponse
    {
        $inmueble->load('images');

        DB::transaction(function () use ($inmueble): void {
            foreach ($inmueble->images as $image) {
                $this->imageService->deleteImage($image);
            }

            $inmueble->delete();
        });

        return redirect()
            ->route('inmuebles.index')
            ->with('status', 'Inmueble eliminado correctamente.');
    }

    /**
     * Normalize payload to be stored in the database.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function preparePayload(array $payload, Request $request): array
    {
        $payload['asesor_id'] = $request->user()->id;
        $payload['destacado'] = $request->boolean('destacado');

        foreach ([
            'habitaciones',
            'banos',
            'estacionamientos',
            'metros_cuadrados',
            'superficie_construida',
            'superficie_terreno',
            'anio_construccion',
        ] as $numericField) {
            if (! array_key_exists($numericField, $payload)) {
                continue;
            }

            if ($payload[$numericField] === '' || $payload[$numericField] === null) {
                $payload[$numericField] = null;
            }
        }

        $payload['amenidades'] = $this->transformListStringToArray($payload['amenidades'] ?? '');
        $payload['extras'] = $this->transformListStringToArray($payload['extras'] ?? '');

        unset($payload['imagenes']);

        return $payload;
    }

    private function getWatermarkPreviewUrl(): ?string
    {
        $localWatermarkPath = config('inmuebles.images.watermark.path');

        if ($localWatermarkPath && file_exists($localWatermarkPath)) {
            try {
                $contents = file_get_contents($localWatermarkPath);

                if ($contents === false) {
                    return null;
                }

                $mimeType = mime_content_type($localWatermarkPath) ?: 'image/png';

                return sprintf('data:%s;base64,%s', $mimeType, base64_encode($contents));
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        $diskName = (string) config('inmuebles.images.watermark.preview_disk', '');
        $path = trim((string) config('inmuebles.images.watermark.preview_path', ''));

        if ($diskName === '' && $path === '') {
            $diskName = (string) config('inmuebles.images.watermark.disk', '');
            $path = trim((string) config('inmuebles.images.watermark.path', ''));
        } else {
            if ($diskName === '') {
                $diskName = (string) config('inmuebles.images.watermark.disk', '');
            }

            if ($path === '') {
                $path = trim((string) config('inmuebles.images.watermark.path', ''));
            }
        }

        if ($diskName !== '' && $path !== '') {
            try {
                $disk = Storage::disk($diskName);
                $ttl = max(1, (int) config('inmuebles.images.watermark.preview_ttl', 10));
                $expiresAt = now()->addMinutes($ttl);

                try {
                    if ($disk instanceof FilesystemAdapter && method_exists($disk, 'temporaryUrl')) {
                        return $disk->temporaryUrl($path, $expiresAt);
                    }

                    throw new RuntimeException('Temporary URLs are not supported by the configured disk.');
                } catch (Throwable $temporaryUrlException) {
                    if (
                        $disk instanceof FilesystemAdapter
                        && method_exists($disk, 'url')
                        && $disk->exists($path)
                    ) {
                        return $disk->url($path);
                    }

                    report($temporaryUrlException);
                }

                if ($disk instanceof FilesystemAdapter && method_exists($disk, 'get')) {
                    try {
                        if ($disk->exists($path)) {
                            $contents = $disk->get($path);

                            if ($contents !== false && $contents !== null) {
                                $mimeType = method_exists($disk, 'mimeType') ? $disk->mimeType($path) : null;
                                $mimeType = $mimeType ?: 'image/png';

                                return sprintf('data:%s;base64,%s', $mimeType, base64_encode($contents));
                            }
                        }
                    } catch (Throwable $diskException) {
                        report($diskException);
                    }
                }
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return null;
    }

    /**
     * Convert textarea values to arrays for storage.
     *
     * @return array<int, string>|null
     */
    protected function transformListStringToArray(?string $value): ?array
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return collect(preg_split('/\r\n|\r|\n/', (string) $value))
            ->map(fn (string $item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Persist uploaded images in S3 (or the configured disk).
     *
     * @param  array<int, \Illuminate\Http\UploadedFile>  $imagenes
     */
    protected function storeImages(Inmueble $inmueble, array $imagenes): void
    {
        if (empty($imagenes)) {
            return;
        }

        $diskName = $this->resolveImageDisk();

        $this->imageService->storeImages($inmueble, $imagenes, $diskName);
    }

    /**
     * Delete images both from storage and the database.
     *
     * @param  array<int, int>  $imagenesIds
     */
    protected function deleteImages(Inmueble $inmueble, array $imagenesIds): void
    {
        $imagenes = $inmueble->images()->whereIn('id', $imagenesIds)->get();

        foreach ($imagenes as $imagen) {
            $this->imageService->deleteImage($imagen);
        }
    }

    /**
     * Determine which filesystem disk should be used for property images.
     */
    protected function resolveImageDisk(): string
    {
        if (config('filesystems.default') === 's3') {
            return 's3';
        }

        if (! empty(config('filesystems.disks.s3.bucket'))) {
            return 's3';
        }

        return config('filesystems.default', 'public');
    }
}
