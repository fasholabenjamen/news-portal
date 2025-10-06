<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticlesResource;
use App\Http\Requests\ArticleIndexRequest;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class ArticleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/articles",
     *     tags={"Articles"},
     *     summary="Get a paginated list of articles",
     *     description="Retrieve articles with optional filters for category, author, source, publish date range, and free-text search across title, description, content, and keywords.",
    *     @OA\Parameter(
    *         name="categories_id",
    *         in="query",
    *         description="Filter by category identifiers (comma-separated)",
    *         required=false,
    *         @OA\Schema(type="string", example="2,3")
    *     ),
     *     @OA\Parameter(
     *         name="authors_id",
     *         in="query",
    *         description="Filter by author identifiers (comma-separated)",
    *         required=false,
    *         @OA\Schema(type="string", example="1,2")
     *     ),
     *     @OA\Parameter(
     *         name="sources_id",
     *         in="query",
    *         description="Filter by source identifiers (comma-separated)",
    *         required=false,
    *         @OA\Schema(type="string", example="3,4")
     *     ),
     *     @OA\Parameter(
     *         name="publish_date",
     *         in="query",
     *         description="Date range filter in the format 'YYYY-MM-DD'",
     *         required=false,
     *         @OA\Schema(type="string", example="2025-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Full-text search across title, description, content, and keywords",
     *         required=false,
     *         @OA\Schema(type="string", example="climate change")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of results per page (max 100)",
     *         required=false,
     *         @OA\Schema(type="integer", default=50)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of articles",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ArticlesResource")),
     *             @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function index(ArticleIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $query = Article::query();

        if ($categories = $request->categoryFilters()) {
            $query->category($categories);
        }

        if ($authors = $request->authorFilters()) {
            $query->author($authors);
        }

        if ($sources = $request->sourceFilters()) {
            $query->source($sources);
        }

        if (!empty($validated['publish_date'])) {
            $query->publishDate($validated['publish_date']);
        }

        if (!empty($validated['q'])) {
            $term = $validated['q'];

            $query->whereRaw(
                "MATCH (title, description, content, keywords) AGAINST (? IN BOOLEAN MODE)",
                [$term . '*']
            );
        }

        $perPage = min((int) $validated['per_page'] ?? 50, 100);
        $articles = $query->paginate($perPage);

        return $this->paginatedResponse($articles, ArticlesResource::class);
    }

    /**
     * @OA\Get(
     *     path="/articles/{id}",
     *     tags={"Articles"},
     *     summary="Get a single article",
     *     description="Fetch the details of a specific article by its identifier.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Article ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Article details",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/ArticlesResource")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Article not found")
     * )
     */
    public function show(Article $article): ArticlesResource
    {
        return ArticlesResource::make($article);
    }
}
