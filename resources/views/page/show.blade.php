<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @php
        $metaTitle = $p->meta_title ?: $p->title;
        $metaDesc = $p->meta_description ?: '';
        $metaImage = $p->meta_image_url ?: null;
        $metaUrl = route('page.show', $p->slug);
    @endphp
    <title>{{ $metaTitle }} — {{ env('APP_NAME') }}</title>
    @if ($metaDesc)
        <meta name="description" content="{{ $metaDesc }}">
    @endif
    <link rel="canonical" href="{{ $metaUrl }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    @if ($metaDesc)
        <meta property="og:description" content="{{ $metaDesc }}">
    @endif
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ $metaUrl }}">
    @if ($metaImage)
        <meta property="og:image" content="{{ $metaImage }}">
    @endif
    @vite(['resources/css/app.css','resources/js/app.js'])
    <style>
        /* Hide Trix attachment captions (file names) under images */
        .prose figure.attachment .attachment__caption,
        .prose figure.trix-attachment .attachment__caption { display: none !important; }
        /* Add vertical spacing to images from rich text */
        .prose img { margin-top: 1.25rem; margin-bottom: 1.25rem; }
        .prose figure { margin-top: 1.25rem; margin-bottom: 1.25rem; }
    </style>
  </head>
  <body class="bg-gray-50 text-gray-900">
    <header class="bg-white border-b border-gray-200">
        <div class="mx-auto max-w-7xl px-4 py-3 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="text-sky-600 hover:text-sky-700">← Beranda</a>
            <nav class="hidden md:flex items-center gap-6 text-sm">
                @php
                    $tabBase = 'text-gray-700 hover:text-sky-700';
                    $tabActive = 'text-sky-700 font-medium';
                @endphp
                @include('partials.menu-links', ['tabBase' => $tabBase, 'tabActive' => $tabActive])
            </nav>
        </div>
    </header>
    <main class="mx-auto max-w-3xl px-4 py-8">
        <article class="space-y-4">
            <h1 class="text-2xl font-bold">{{ $p->title }}</h1>
            <div class="prose max-w-none bg-white p-5 rounded-md shadow">
                {!! $p->body_html !!}
            </div>
        </article>
    </main>
  </body>
 </html>
