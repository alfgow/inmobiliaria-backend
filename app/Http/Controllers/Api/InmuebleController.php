<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexInmuebleRequest;
use App\Http\Resources\InmuebleResource;
use App\Models\Inmueble;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class InmuebleController extends Controller
{
    public function index(IndexInmuebleRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $perPage = (int) ($filters['limit'] ?? 20);

        $query = Inmueble::query()
            ->with(['images', 'coverImage', 'status'])
            ->when($filters['search'] ?? null, function (Builder $builder, string $search): void {
                $builder->where(function (Builder $nested) use ($search): void {
                    $nested
                        ->where('titulo', 'like', "%{$search}%")
                        ->orWhere('direccion', 'like', "%{$search}%")
                        ->orWhere('colonia', 'like', "%{$search}%")
                        ->orWhere('municipio', 'like', "%{$search}%")
                        ->orWhere('estado', 'like', "%{$search}%");
                });
            })
            ->when($filters['operacion'] ?? null, fn(Builder $builder, string $operacion): Builder => $builder->where('operacion', $operacion))
            ->when($filters['estatus'] ?? null, fn(Builder $builder, int $estatus): Builder => $builder->where('estatus_id', $estatus))
            ->when(
                array_key_exists('destacado', $filters) && $filters['destacado'] !== null,
                function (Builder $builder) use ($filters): Builder {
                    return $builder->where('destacado', (bool) $filters['destacado']);
                }
            )
            ->orderByDesc('destacado')
            ->orderByDesc('updated_at');

        $paginator = $query->paginate($perPage)->withQueryString();

        $collection = InmuebleResource::collection($paginator);
        $collection->additional([
            'filters' => array_filter([
                'search' => $filters['search'] ?? null,
                'operacion' => $filters['operacion'] ?? null,
                'estatus' => $filters['estatus'] ?? null,
                'destacado' => array_key_exists('destacado', $filters) && $filters['destacado'] !== null
                    ? (bool) $filters['destacado']
                    : null,
            ], static fn($value) => $value !== null && $value !== ''),
        ]);

        return $collection->response();
    }

    public function show(Inmueble $inmueble): JsonResponse
    {
        $inmueble->loadMissing(['images', 'coverImage', 'status']);

        return InmuebleResource::make($inmueble)->response();
    }
}
