<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'title',
        'body_md',
        'published_at',
        'author_id',
        'payout_id',
        'cover_path',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function payout()
    {
        return $this->belongsTo(Payout::class);
    }

    public function getCoverUrlAttribute(): ?string
    {
        if (! $this->cover_path) {
            return null;
        }
        try {
            $ttl = (int) (env('S3_SIGNED_URL_TTL', 300));
            return \Illuminate\Support\Facades\Storage::disk('s3')->temporaryUrl($this->cover_path, now()->addSeconds($ttl));
        } catch (\Throwable) {
            return \Illuminate\Support\Facades\Storage::disk('s3')->url($this->cover_path);
        }
    }

    public function setBodyMdAttribute($value): void
    {
        $processed = (string) $value;
        // Rewrite <img src="..."> to use media proxy for S3 private files if path looks like S3 object key
        $processed = preg_replace_callback('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', function ($m) {
            $src = $m[1] ?? '';
            // Only rewrite if it looks like an S3 object (contains 'articles/' path or no host but path-like)
            $path = null;
            if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://')) {
                $urlPath = parse_url($src, PHP_URL_PATH);
                if ($urlPath && str_contains($urlPath, 'articles/')) {
                    $path = ltrim($urlPath, '/');
                }
            } elseif (!empty($src)) {
                // relative path like articles/...
                if (str_starts_with($src, 'articles/')) {
                    $path = $src;
                }
            }
            if ($path) {
                $b64 = rtrim(strtr(base64_encode($path), '+/', '-_'), '=');
                $proxy = route('media.proxy', ['disk' => 's3', 'p' => $b64]);
                // Replace only the URL inside the src attribute
                return str_replace($src, $proxy, $m[0]);
            }
            return $m[0];
        }, $processed);

        $this->attributes['body_md'] = $processed;
    }
}
