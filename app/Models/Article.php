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
        'source'
    ];
    protected $guarded = [];
    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function scopeSource($query, $source_id)
    {
        return $query->where('source_id', $source_id);
    }

    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    public function scopeAuthor($query, $author)
    {
        return $query->where('author', $author);
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
