<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="ArticlesResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="slug", type="string", example="breaking-news-update"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="content", type="string"),
 *     @OA\Property(property="image_url", type="string", format="uri", nullable=true),
 *     @OA\Property(property="source_id", type="integer", nullable=true),
 *     @OA\Property(property="author", type="string", nullable=true),
 *     @OA\Property(property="link", type="string", format="uri"),
 *     @OA\Property(property="published_at", type="string", format="date-time"),
 *     @OA\Property(property="category", type="string", nullable=true),
 *     @OA\Property(property="language", type="string", nullable=true),
 *     @OA\Property(property="keywords", type="string", nullable=true),
 *     @OA\Property(property="source", ref="#/components/schemas/SourceResource", nullable=true)
 * )
 */
class ArticlesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->content,
            'image_url' => $this->image_url,
            'link' => $this->link,
            'published_at' => $this->published_at?->toIso8601String(),
            'category' => $this->category,
            'language' => $this->language,
            'keywords' => $this->keywords,
            'source' => SourceResource::make($this->whenLoaded('source')),
            'author' => AuthorResource::make($this->whenLoaded('author')),
            'category' => CategoryResource::make($this->whenLoaded('category'))
        ];
    }
}
