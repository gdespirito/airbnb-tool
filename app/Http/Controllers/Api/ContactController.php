<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContactController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
        ]);

        $query = Contact::query()->orderBy('name');

        if ($request->filled('property_id')) {
            $query->whereHas('properties', fn ($q) => $q->where('id', $request->integer('property_id')));
        }

        return ContactResource::collection($query->get());
    }

    public function show(Contact $contact): ContactResource
    {
        return new ContactResource($contact);
    }
}
