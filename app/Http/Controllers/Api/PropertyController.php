<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PropertyController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $properties = Property::query()
            ->with('cleaningContact')
            ->get();

        return PropertyResource::collection($properties);
    }

    public function show(Property $property): PropertyResource
    {
        $property->load('cleaningContact');

        return new PropertyResource($property);
    }
}
