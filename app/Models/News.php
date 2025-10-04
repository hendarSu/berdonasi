<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class News extends Model
{
    use HasFactory;

    protected $table = 'news';

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body_md', 'cover_path', 'author_id',
        'published_at', 'meta_title', 'meta_description', 'meta_image_url',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function getCoverUrlAttribute(): ?string
    {
        if (! $this->cover_path) return null;
        try {
            $ttl = (int) (env('S3_SIGNED_URL_TTL', 300));
            return Storage::disk('s3')->temporaryUrl($this->cover_path, now()->addSeconds($ttl));
        } catch (\Throwable) {
            return Storage::disk('s3')->url($this->cover_path);
        }
    }
}

