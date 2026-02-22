<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexN8nChatHistoryRequest;
use App\Http\Requests\Api\StoreN8nChatHistoryRequest;
use App\Http\Requests\Api\UpdateN8nChatHistoryRequest;
use App\Http\Resources\N8nChatHistoryResource;
use App\Models\N8nChatHistory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class N8nChatHistoryController extends Controller
{
    public function index(IndexN8nChatHistoryRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $perPage = (int) ($filters['limit'] ?? 20);

        $query = N8nChatHistory::query()
            ->when($filters['session_id'] ?? null, fn(Builder $builder, string $sessionId) => $builder->where('session_id', $sessionId))
            ->orderByDesc('id');

        $paginator = $query->paginate($perPage)->withQueryString();

        $collection = N8nChatHistoryResource::collection($paginator);
        $collection->additional([
            'filters' => array_filter([
                'session_id' => $filters['session_id'] ?? null,
            ], static fn($value) => $value !== null && $value !== ''),
        ]);

        return $collection->response();
    }

    public function store(StoreN8nChatHistoryRequest $request): JsonResponse
    {
        $history = N8nChatHistory::create($request->validated());

        return N8nChatHistoryResource::make($history)
            ->response()
            ->setStatusCode(201);
    }

    public function show(N8nChatHistory $chatHistory): JsonResponse
    {
        return N8nChatHistoryResource::make($chatHistory)->response();
    }

    public function update(UpdateN8nChatHistoryRequest $request, N8nChatHistory $chatHistory): JsonResponse
    {
        $chatHistory->fill($request->validated());

        if ($chatHistory->isDirty()) {
            $chatHistory->save();
        }

        return N8nChatHistoryResource::make($chatHistory)->response();
    }

    public function destroy(N8nChatHistory $chatHistory): JsonResponse
    {
        $chatHistory->delete();

        return response()->json([], 204);
    }
}
