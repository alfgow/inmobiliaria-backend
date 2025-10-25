<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreContactIaInteractionRequest;
use App\Http\Requests\Api\UpdateContactIaInteractionRequest;
use App\Http\Resources\ContactIaInteractionResource;
use App\Models\Contact;
use App\Models\ContactIaInteraction;
use Illuminate\Http\JsonResponse;

class ContactIaInteractionController extends Controller
{
    public function index(Contact $contact): JsonResponse
    {
        $interactions = $contact->iaInteractions()->orderByDesc('created_at')->get();

        return ContactIaInteractionResource::collection($interactions)->response();
    }

    public function store(Contact $contact, StoreContactIaInteractionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $interaction = $contact->iaInteractions()->create([
            'payload' => $data['payload'],
            'created_at' => $data['created_at'] ?? now(),
        ]);

        return ContactIaInteractionResource::make($interaction)->response()->setStatusCode(201);
    }

    public function update(
        Contact $contact,
        ContactIaInteraction $interaccion,
        UpdateContactIaInteractionRequest $request
    ): JsonResponse {
        if ($interaccion->contacto_id !== $contact->id) {
            abort(404);
        }

        $interaccion->fill([
            'payload' => $request->validated()['payload'],
        ]);

        if ($interaccion->isDirty()) {
            $interaccion->save();
        }

        return ContactIaInteractionResource::make($interaccion)->response();
    }
}
