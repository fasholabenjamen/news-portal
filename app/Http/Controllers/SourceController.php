<?php

namespace App\Http\Controllers;

use App\Http\Resources\SourceResource;
use App\Models\Source;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class SourceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/sources",
     *     tags={"Sources"},
     *     summary="Get a list of all sources",
     *     description="Retrieve a paginated list of news sources with optional filtering and sorting",
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page (default: 15, max: 100)",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="key", type="string", example="bbc-news"),
     *                 @OA\Property(property="label", type="string", example="BBC News"),
     *             )),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 50), 100);
        $query = Source::query();
        $sources = $query->paginate($perPage);
        return $this->paginatedResponse($sources, SourceResource::class);

    }
}
