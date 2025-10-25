<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreContactCommentRequest;
use App\Http\Requests\Api\UpdateContactCommentRequest;
use App\Http\Resources\ContactCommentResource;
use App\Models\Contact;
use App\Models\ContactComment;
use Illuminate\Http\JsonResponse;

class ContactCommentController extends Controller
{
    public function index(Contact $contact): JsonResponse
    {
        $comments = $contact->comentarios()->orderByDesc('created_at')->get();

        return ContactCommentResource::collection($comments)->response();
    }

    public function store(Contact $contact, StoreContactCommentRequest $request): JsonResponse
    {
        $data = $request->validated();

        $comment = $contact->comentarios()->create([
            'comentario' => $data['comentario'],
            'created_at' => $data['created_at'] ?? now(),
        ]);

        return ContactCommentResource::make($comment)->response()->setStatusCode(201);
    }

    public function update(
        Contact $contact,
        ContactComment $comentario,
        UpdateContactCommentRequest $request
    ): JsonResponse {
        if ($comentario->contacto_id !== $contact->id) {
            abort(404);
        }

        $comentario->fill([
            'comentario' => $request->validated()['comentario'],
        ]);

        if ($comentario->isDirty()) {
            $comentario->save();
        }

        return ContactCommentResource::make($comentario)->response();
    }
}
