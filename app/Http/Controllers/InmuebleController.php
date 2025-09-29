<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInmuebleRequest;
use App\Http\Requests\UpdateInmuebleRequest;
use App\Models\Inmueble;
use App\Models\InmuebleStatus;
use App\Services\InmuebleImageService;
use App\Support\InmuebleStatusClassifier;
use App\Support\WatermarkPathResolver;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
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
                        ->orWhere('colonia', 'like', "%{$search}%")
                        ->orWhere('municipio', 'like', "%{$search}%")
                        ->orWhere('estado', 'like', "%{$search}%");
                });
            })
            ->when($operacion, fn(Builder $query) => $query->where('operacion', $operacion))
            ->when($estatus, fn(Builder $query) => $query->where('estatus_id', $estatus))
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
     * Display the properties map view.
     */
    public function map(): View
    {
        $properties = Inmueble::query()
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->with('coverImage')
            ->get()
            ->map(function (Inmueble $inmueble): array {
                $coverImage = $inmueble->coverImage;
                $imageUrl = $coverImage?->temporaryVariantUrl('watermarked') ?? $coverImage?->url;

                return [
                    'id' => $inmueble->id,
                    'title' => $inmueble->titulo,
                    'latitude' => (float) $inmueble->latitud,
                    'longitude' => (float) $inmueble->longitud,
                    'address' => $inmueble->direccion,
                    'price' => $inmueble->formattedPrice(),
                    'image_url' => $imageUrl,
                    'manage_url' => route('inmuebles.edit', $inmueble),
                ];
            })
            ->values();

        return view('inmuebles.map', [
            'properties' => $properties,
        ]);
    }

    /**
     * Show the form for creating a new property.
     */
    public function create(): View
    {
        return view('inmuebles.create', [
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

        if (! array_key_exists('estatus_id', $payload) || $payload['estatus_id'] === null) {
            $payload['estatus_id'] = 1;
        }

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

        $originalStatusId = (int) $inmueble->estatus_id;
        $newStatusId = isset($payload['estatus_id']) ? (int) $payload['estatus_id'] : $originalStatusId;
        $isClosingStatus = InmuebleStatusClassifier::isClosingStatusId($newStatusId);
        $wasClosingStatus = InmuebleStatusClassifier::isClosingStatusId($originalStatusId);
        $isTransitionToClosing = $isClosingStatus && ! $wasClosingStatus;

        if ($isClosingStatus && array_key_exists('commission_percentage', $payload)) {
            $commissionPercentage = $payload['commission_percentage'] !== null
                ? (float) $payload['commission_percentage']
                : null;
            $currentPrice = array_key_exists('precio', $payload)
                ? (float) $payload['precio']
                : (float) $inmueble->precio;

            if ($commissionPercentage !== null) {
                $payload['commission_amount'] = round(($currentPrice * $commissionPercentage) / 100, 2);
            }
        }

        DB::transaction(function () use (
            $inmueble,
            $payload,
            $imagenes,
            $imagenesEliminar,
            $isTransitionToClosing,
        ): void {
            $inmueble->update($payload);

            if (! empty($imagenesEliminar)) {
                $this->deleteImages($inmueble, $imagenesEliminar);
            }

            $updatedInmueble = $inmueble->fresh();

            $this->storeImages($updatedInmueble, $imagenes);

            if ($isTransitionToClosing) {
                $this->registerVenta($updatedInmueble);
            }
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
            'commission_status_id',
        ] as $numericField) {
            if (! array_key_exists($numericField, $payload)) {
                continue;
            }

            if ($payload[$numericField] === '' || $payload[$numericField] === null) {
                $payload[$numericField] = null;

                continue;
            }

            if ($numericField === 'commission_status_id') {
                $payload[$numericField] = (int) $payload[$numericField];
            }
        }

        foreach (['commission_percentage', 'commission_amount'] as $decimalField) {
            if (! array_key_exists($decimalField, $payload)) {
                continue;
            }

            if ($payload[$decimalField] === '' || $payload[$decimalField] === null) {
                $payload[$decimalField] = null;

                continue;
            }

            $payload[$decimalField] = round((float) $payload[$decimalField], 2);
        }

        if (array_key_exists('commission_status_name', $payload)) {
            if ($payload['commission_status_name'] === null) {
                $payload['commission_status_name'] = null;
            } else {
                $trimmedName = trim((string) $payload['commission_status_name']);
                $payload['commission_status_name'] = $trimmedName === '' ? null : $trimmedName;
            }
        }

        $payload['amenidades'] = $this->transformListStringToArray($payload['amenidades'] ?? '');
        $payload['extras'] = $this->transformListStringToArray($payload['extras'] ?? '');

        unset($payload['imagenes']);

        return $payload;
    }

    protected function registerVenta(Inmueble $inmueble): void
    {
        $commissionAmount = (float) ($inmueble->commission_amount ?? 0);

        if ($commissionAmount <= 0) {
            return;
        }

        $now = now();
        $connection = DB::connection('ventasvillanuevagarcia');
        $alreadyRegistered = $connection
            ->table('ventasvillanuevagarcia')
            ->where('inmueble_id', $inmueble->id)
            ->where('mes_venta', $now->month)
            ->where('year_venta', $now->year)
            ->exists();

        if ($alreadyRegistered) {
            return;
        }

        $connection->table('ventasvillanuevagarcia')->insert([
            'inmueble_id' => $inmueble->id,
            'id_usuario' => 1,
            'canal_venta' => 'Inmobiliaria: ' . $inmueble->operacion,
            'comision_asesor' => 0,
            'ganancia_neta' => number_format($commissionAmount, 2, '.', ''),
            'mes_venta' => $now->month,
            'year_venta' => $now->year,
            'fecha_venta' => $now,
        ]);
    }

    private function getWatermarkPreviewUrl(): ?string
    {
        $localWatermarkPath = WatermarkPathResolver::resolve(config('inmuebles.images.watermark.path'));

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
        $path = str_replace('\\', '/', trim((string) config('inmuebles.images.watermark.preview_path', '')));

        if ($diskName === '' && $path === '') {
            $diskName = (string) config('inmuebles.images.watermark.disk', '');
            $path = str_replace('\\', '/', trim((string) config('inmuebles.images.watermark.path', '')));
        } else {
            if ($diskName === '') {
                $diskName = (string) config('inmuebles.images.watermark.disk', '');
            }

            if ($path === '') {
                $path = str_replace('\\', '/', trim((string) config('inmuebles.images.watermark.path', '')));
            }
        }

        if ($diskName !== '' && $path !== '') {
            try {
                $disk = Storage::disk($diskName);

                if (
                    $disk instanceof FilesystemAdapter
                    && method_exists($disk, 'exists')
                    && $disk->exists($path)
                ) {
                    if (method_exists($disk, 'url')) {
                        $url = $disk->url($path);

                        if (is_string($url) && $url !== '') {
                            return $url;
                        }
                    }

                    if (method_exists($disk, 'get')) {
                        try {
                            $contents = $disk->get($path);

                            if ($contents !== false && $contents !== null) {
                                $mimeType = method_exists($disk, 'mimeType') ? $disk->mimeType($path) : null;
                                $mimeType = $mimeType ?: 'image/png';

                                return sprintf('data:%s;base64,%s', $mimeType, base64_encode($contents));
                            }
                        } catch (Throwable $diskException) {
                            report($diskException);
                        }
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
            ->map(fn(string $item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Persist uploaded images for the property.
     *
     * @param  array<int, \Illuminate\Http\UploadedFile>  $imagenes
     */
    protected function storeImages(Inmueble $inmueble, array $imagenes): void
    {
        if (empty($imagenes)) {
            return;
        }

        $this->imageService->storeImages($inmueble, $imagenes);
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
}
