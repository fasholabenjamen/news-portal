<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    use HasFactory;
    
    protected $with = [
        'source',
        'author',
        'category'
    ];
    protected $guarded = [];
    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeSource($query, $sources_id)
    {
        return $query->whereIn('source_id', $sources_id);
    }

    public function scopeCategory($query, $categories_id)
    {
        return $query->whereIn('category_id', $categories_id);
    }

    public function scopeAuthor($query, $authors_id)
    {
        return $query->whereIn('author_id', $authors_id);
    }

    public function scopeLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    public function scopePublishDate($query, $publish_date)
    {
        $publish_date = Carbon::parse($publish_date);
        return $query->where(function($query) use ($publish_date) {
            $query->whereDate('published_at', $publish_date->toDateString())
            ->orWhereBetween('published_at', [
                $publish_date->startOfDay()->toDateTimeString(),
                $publish_date->endOfDay()->toDateTimeString()
            ]);
        });
    }
}
