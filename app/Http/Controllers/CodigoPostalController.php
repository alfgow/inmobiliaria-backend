<?php

namespace App\Http\Controllers;

use App\Models\CodigoPostal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CodigoPostalController extends Controller
{
    /**
     * Column map for allowed query parameters.
     *
     * @var array<string, string>
     */
    protected array $columnMap = [
        'codigo_postal' => 'd_codigo',
        'colonia' => 'd_asenta',
        'municipio' => 'd_mnpio',
        'estado' => 'd_estado',
        'ciudad' => 'd_ciudad',
    ];

    /**
     * List unique postal code values based on the requested type and filters.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate($this->getValidationRules(['search' => ['nullable', 'string', 'max:255']]));

        $column = $this->columnMap[$validated['type']];

        $query = CodigoPostal::query()
            ->select($column)
            ->whereNotNull($column);

        foreach ($this->columnMap as $parameter => $field) {
            if (!array_key_exists($parameter, $validated) || $parameter === $validated['type']) {
                continue;
            }

            $value = $validated[$parameter];

            if ($value === null || $value === '') {
                continue;
            }

            $query->where($field, $value);
        }

        if (!empty($validated['search'])) {
            $query->where($column, 'like', '%' . $validated['search'] . '%');
        }

        $values = $query
            ->distinct()
            ->orderBy($column)
            ->limit(50)
            ->pluck($column)
            ->filter()
            ->values();

        return response()->json([
            'data' => $values,
        ]);
    }

    /**
     * Resolve complete postal code information using a selected value.
     */
    public function resolve(Request $request): JsonResponse
    {
        $validated = $request->validate($this->getValidationRules([
            'value' => ['required', 'string', 'max:255'],
        ], ['search']));

        $column = $this->columnMap[$validated['type']];

        $query = CodigoPostal::query()
            ->select(array_values($this->columnMap));

        foreach ($this->columnMap as $parameter => $field) {
            $value = $validated[$parameter] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            $query->where($field, $value);
        }

        $query->where($column, $validated['value']);

        $results = $query
            ->distinct()
            ->orderBy('d_asenta')
            ->orderBy('d_mnpio')
            ->orderBy('d_estado')
            ->orderBy('d_codigo')
            ->get()
            ->map(fn (CodigoPostal $codigoPostal) => [
                'codigo_postal' => $codigoPostal->d_codigo,
                'colonia' => $codigoPostal->d_asenta,
                'municipio' => $codigoPostal->d_mnpio,
                'estado' => $codigoPostal->d_estado,
                'ciudad' => $codigoPostal->d_ciudad,
            ]);

        return response()->json([
            'data' => $results,
        ]);
    }

    /**
     * Build validation rules for postal code queries.
     *
     * @param  array<string, array<int, string>>  $additional
     * @param  array<int, string>  $exclusions
     * @return array<string, mixed>
     */
    protected function getValidationRules(array $additional = [], array $exclusions = []): array
    {
        $rules = array_merge([
            'type' => ['required', Rule::in(array_keys($this->columnMap))],
        ], $additional);

        foreach ($this->columnMap as $parameter => $field) {
            if (in_array($parameter, $exclusions, true)) {
                continue;
            }

            $rules[$parameter] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }
}
