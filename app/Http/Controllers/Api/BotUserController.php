<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexBotUserRequest;
use App\Http\Requests\Api\StoreBotUserRequest;
use App\Http\Requests\Api\UpdateBotUserRequest;
use App\Http\Resources\BotUserResource;
use App\Models\BotUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class BotUserController extends Controller
{
    public function index(IndexBotUserRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $perPage = (int) ($filters['limit'] ?? 20);

        $query = BotUser::query()
            ->withCount('chatHistories')
            ->when($filters['status'] ?? null, fn(Builder $builder, string $status) => $builder->where('status', $status))
            ->when($filters['bot_status'] ?? null, fn(Builder $builder, string $botStatus) => $builder->where('bot_status', $botStatus))
            ->when($filters['questionnaire_status'] ?? null, fn(Builder $builder, string $questionnaireStatus) => $builder->where('questionnaire_status', $questionnaireStatus))
            ->when($filters['session_id'] ?? null, fn(Builder $builder, string $sessionId) => $builder->where('session_id', 'like', "%{$sessionId}%"))
            ->when($filters['telefono_real'] ?? null, fn(Builder $builder, string $telefonoReal) => $builder->where('telefono_real', 'like', "%{$telefonoReal}%"))
            ->when($filters['nombre'] ?? null, fn(Builder $builder, string $nombre) => $builder->where('nombre', 'like', "%{$nombre}%"))
            ->orderByDesc('updated_at');

        $paginator = $query->paginate($perPage)->withQueryString();

        $collection = BotUserResource::collection($paginator);
        $collection->additional([
            'filters' => array_filter([
                'status' => $filters['status'] ?? null,
                'bot_status' => $filters['bot_status'] ?? null,
                'questionnaire_status' => $filters['questionnaire_status'] ?? null,
                'session_id' => $filters['session_id'] ?? null,
                'telefono_real' => $filters['telefono_real'] ?? null,
                'nombre' => $filters['nombre'] ?? null,
            ], static fn($value) => $value !== null && $value !== ''),
        ]);

        return $collection->response();
    }

    public function store(StoreBotUserRequest $request): JsonResponse
    {
        $botUser = BotUser::create($request->validated());

        return BotUserResource::make($botUser)
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $botUser): JsonResponse
    {
        $normalizedValue = preg_replace('/\D+/', '', $botUser);
        $normalizedExpression = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(%s), 'whatsapp:', ''), '@c.us', ''), '+', ''), '-', ''), ' ', ''), '(', ''), ')', '')";

        $model = BotUser::query()
            ->where('session_id', $botUser)
            ->orWhere('telefono_real', $botUser)
            ->when($normalizedValue !== '', function ($query) use ($normalizedValue, $normalizedExpression) {
                $query->orWhereRaw(
                    sprintf($normalizedExpression, 'session_id')." = ?",
                    [$normalizedValue]
                )->orWhereRaw(
                    sprintf($normalizedExpression, 'telefono_real')." = ?",
                    [$normalizedValue]
                );
            })
            ->orderByRaw(
                'CASE WHEN session_id = ? THEN 1 WHEN telefono_real = ? THEN 2 ELSE 3 END',
                [$botUser, $botUser]
            )
            ->first();

        if (! $model) {
            return response()->json(null);
        }

        $model->loadCount('chatHistories');

        return BotUserResource::make($model)->response();
    }

    public function update(UpdateBotUserRequest $request, BotUser $botUser): JsonResponse
    {
        $botUser->fill($request->validated());

        if ($botUser->isDirty()) {
            $botUser->save();
        }

        $botUser->loadCount('chatHistories');

        return BotUserResource::make($botUser)->response();
    }

    public function destroy(BotUser $botUser): JsonResponse
    {
        $botUser->delete();

        return response()->json([], 204);
    }
}
