<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaProxyController extends Controller
{
    public function show(Request $request, string $disk)
    {
        $encoded = $request->query('p');
        if (! $encoded) {
            abort(404);
        }
        $path = base64_decode(strtr($encoded, '-_', '+/'));
        if (! $path) {
            abort(404);
        }

        $ttl = (int) (env('S3_SIGNED_URL_TTL', 300));
        try {
            $url = Storage::disk($disk)->temporaryUrl($path, now()->addSeconds($ttl));
        } catch (\Throwable $e) {
            // Fallback: try public URL
            $url = Storage::disk($disk)->url($path);
        }
        return redirect()->away($url);
    }
}

