<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

abstract class Controller
{
    /**
     * @param class-string<JsonResource> $modelResource
     */
    public function paginatedResponse(LengthAwarePaginator $paginator, string $modelResource): JsonResponse
    {
        if (!is_subclass_of($modelResource, JsonResource::class)) {
            throw new InvalidArgumentException('The provided resource must extend '.JsonResource::class);
        }

        return response()->json([
            'data' => $modelResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'path' => $paginator->path(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ]
        ]);
    }
}
