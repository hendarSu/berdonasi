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
}

