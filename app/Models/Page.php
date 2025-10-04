<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'body_md', 'published_at', 'meta_title', 'meta_description', 'meta_image_url',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];
}

