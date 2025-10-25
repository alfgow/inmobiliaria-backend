<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexContactRequest;
use App\Http\Requests\Api\StoreContactRequest;
use App\Http\Requests\Api\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    public function index(IndexContactRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $perPage = (int) ($filters['limit'] ?? 15);

        $query = Contact::query()
            ->with(['latestComment', 'latestInterest'])
            ->when($filters['telefono'] ?? null, function (Builder $builder, string $telefono): void {
                $builder->where('telefono', 'like', "%{$telefono}%");
            })
            ->when($filters['email'] ?? null, function (Builder $builder, string $email): void {
                $builder->where('email', 'like', "%{$email}%");
            })
            ->when($filters['nombre'] ?? null, function (Builder $builder, string $nombre): void {
                $builder->where('nombre', 'like', "%{$nombre}%");
            })
            ->orderByDesc('id');

        $paginator = $query->paginate($perPage)->withQueryString();

        $collection = ContactResource::collection($paginator);
        $collection->additional([
            'filters' => array_filter([
                'telefono' => $filters['telefono'] ?? null,
                'email' => $filters['email'] ?? null,
                'nombre' => $filters['nombre'] ?? null,
            ], static fn($value) => $value !== null && $value !== ''),
        ]);

        return $collection->response();
    }

    public function store(StoreContactRequest $request): JsonResponse
    {
        $contact = Contact::create($request->validated());

        $resource = ContactResource::make($contact);

        return $resource->response()->setStatusCode(201);
    }

    public function show(Contact $contact): JsonResponse
    {
        $contact->load([
            'comentarios' => fn($query) => $query->orderByDesc('created_at'),
            'iaInteractions' => fn($query) => $query->orderByDesc('created_at'),
        ]);

        return ContactResource::make($contact)->response();
    }

    public function update(UpdateContactRequest $request, Contact $contact): JsonResponse
    {
        $contact->fill($request->validated());

        if ($contact->isDirty()) {
            $contact->save();
        }

        return ContactResource::make($contact)->response();
    }
}
