<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $metaTitle = $c->meta_title ?: $c->title;
        $metaDesc = $c->meta_description ?: ($c->summary ?: '');
        $metaImage = $c->meta_image_url ?: optional($c->media->sortBy('sort_order')->first())->url;
        $metaUrl = route('campaign.show', $c->slug);
    @endphp
    <title>{{ $metaTitle }} — {{ env('APP_NAME') }}</title>
    @if ($metaDesc)
        <meta name="description" content="{{ $metaDesc }}">
    @endif
    <meta property="og:title" content="{{ $metaTitle }}">
    @if ($metaDesc)
        <meta property="og:description" content="{{ $metaDesc }}">
    @endif
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ $metaUrl }}">
    @if ($metaImage)
        <meta property="og:image" content="{{ $metaImage }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    @if ($metaDesc)
        <meta name="twitter:description" content="{{ $metaDesc }}">
    @endif
    @if ($metaImage)
        <meta name="twitter:image" content="{{ $metaImage }}">
    @endif
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
    <header class="bg-white border-b border-gray-200">
        <div class="mx-auto max-w-7xl px-4 py-3 flex items-center gap-4">
            <a href="{{ route('home') }}" class="text-sky-600 hover:text-sky-700">← Kembali</a>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8">
        @php $cover = optional($c->media->sortBy('sort_order')->first())->url; @endphp
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-4">
                <h1 class="text-2xl font-bold leading-tight">{{ $c->title }}</h1>
                @if ($cover)
                    <img src="{{ $cover }}" alt="{{ $c->title }}" class="aspect-[16/9] w-full rounded-xl object-cover shadow" />
                @endif

                <!-- Tabs -->
                @php $activeTab = $tab ?? 'detail'; @endphp
                <div class="rounded-xl bg-white shadow">
                    <div class="border-b border-gray-200">
                        <nav class="flex overflow-x-auto" aria-label="Tabs">
                            @php
                                $tabs = [
                                    'detail' => 'Detail',
                                    'laporan' => 'Laporan',
                                    'donatur' => 'Donatur',
                                ];
                            @endphp
                            @foreach ($tabs as $key => $label)
                                <a href="{{ request()->fullUrlWithQuery(['tab' => $key]) }}" class="whitespace-nowrap px-4 py-3 text-sm {{ $activeTab === $key ? 'border-b-2 border-sky-600 font-medium text-sky-700' : 'text-gray-600 hover:text-sky-700 hover:border-b-2 hover:border-sky-300' }}">{{ $label }}</a>
                            @endforeach
                        </nav>
                    </div>

                    <div class="p-5">
                        @if ($activeTab === 'detail')
                            @if ($c->categories->count())
                                <div class="mb-3 flex flex-wrap gap-2">
                                    @foreach ($c->categories as $cat)
                                        <a href="{{ route('home', ['category' => $cat->slug]) }}" class="rounded-full bg-sky-50 px-2 py-1 text-xs text-sky-700 ring-1 ring-sky-200">#{{ $cat->name }}</a>
                                    @endforeach
                                </div>
                            @endif
                            @if ($c->summary)
                                <p class="mb-3 text-gray-700">{{ $c->summary }}</p>
                            @endif
                            @if ($c->description_md)
                                <article class="prose max-w-none">
                                    {!! nl2br(e($c->description_md)) !!}
                                </article>
                            @endif
                            @if ($c->media->count() > 1)
                                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                    @foreach ($c->media->skip(1) as $m)
                                        <img src="{{ $m->url }}" class="aspect-video w-full rounded-lg object-cover" alt="media" />
                                    @endforeach
                                </div>
                            @endif
                        @elseif ($activeTab === 'laporan')
                            @if ($articles->count() === 0)
                                <p class="text-gray-600">Belum ada laporan.</p>
                            @else
                                <ol class="relative ml-3 border-l border-gray-200">
                                    @foreach ($articles as $a)
                                        <li class="mb-8 ml-6">
                                            <span class="absolute -left-3 mt-1 h-6 w-6 rounded-full bg-sky-100 text-sky-600 ring-2 ring-white"> </span>
                                            <div class="mb-1 text-xs text-gray-500">{{ optional($a->published_at)->format('d M Y') ?? '—' }}</div>
                                            <h3 class="mb-2 text-base font-semibold">{{ $a->title }}</h3>
                                            @if ($a->payout?->amount)
                                                <div class="mb-2 inline-block rounded-full bg-orange-50 px-2 py-1 text-xs text-orange-700 ring-1 ring-orange-200">Anggaran: Rp {{ number_format((float)$a->payout->amount, 2, ',', '.') }}</div>
                                            @endif
                                            @if ($a->body_md)
                                                <div class="prose max-w-none">
                                                    {!! nl2br(e($a->body_md)) !!}
                                                </div>
                                            @endif
                                        </li>
                                    @endforeach
                                </ol>
                                <div class="mt-4">{{ $articles->links() }}</div>
                            @endif
                        @elseif ($activeTab === 'donatur')
                            @if ($donations->count() === 0)
                                <p class="text-gray-600">Belum ada donatur.</p>
                            @else
                                <div class="overflow-hidden rounded-lg border border-gray-200">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left font-medium text-gray-600">Nama</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-600">Jumlah</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-600">Waktu</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 bg-white">
                                            @foreach ($donations as $d)
                                                <tr>
                                                    <td class="px-3 py-2">{{ $d->is_anonymous ? 'Anonim' : ($d->donor_name ?: '—') }}</td>
                                                    <td class="px-3 py-2">Rp {{ number_format((float)$d->amount, 2, ',', '.') }}</td>
                                                    <td class="px-3 py-2">{{ optional($d->created_at)->format('d M Y H:i') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-4">{{ $donations->links() }}</div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <aside class="lg:col-span-1">
                <div class="sticky top-4 space-y-4">
                    <div class="rounded-xl bg-white p-5 shadow">
                        @php
                            $progress = (float)$c->target_amount > 0 ? min(100, round(((float)$c->raised_amount / (float)$c->target_amount) * 100)) : 0;
                        @endphp
                        <div class="mb-3 space-y-2">
                            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200">
                                <div class="h-full bg-sky-500" style="width: {{ $progress }}%"></div>
                            </div>
                            <div class="flex items-center justify-between text-xs text-gray-600">
                                <span>Terkumpul: Rp {{ number_format((float) $c->raised_amount, 2, ',', '.') }}</span>
                                <span>Target: Rp {{ number_format((float) $c->target_amount, 2, ',', '.') }}</span>
                            </div>
                        </div>

                        <h2 class="mb-3 text-lg font-semibold">Donasi Sekarang</h2>
                        <form method="post" action="{{ route('campaign.donate', $c->slug) }}" class="space-y-3">
                            @csrf
                            @php
                                $shareUrl = route('campaign.show', $c->slug);
                                $shareText = $c->title;
                                $encUrl = urlencode($shareUrl);
                                $encText = urlencode($shareText);
                            @endphp
                            <div>
                                <label class="mb-1 block text-sm text-gray-700">Jumlah Donasi (Rp)</label>
                                <div class="mb-2 grid grid-cols-4 gap-2">
                                    @foreach ([50000,100000,200000,500000] as $preset)
                                        <button type="button" data-amount="{{ $preset }}" class="preset-amount rounded-md border border-gray-200 bg-gray-50 px-2 py-1 text-xs hover:border-sky-400">{{ number_format($preset,0,',','.') }}</button>
                                    @endforeach
                                </div>
                                <input id="amount-input" type="number" name="amount" min="1000" step="1000" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400" placeholder="100000" />
                            </div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm text-gray-700">Nama</label>
                                    <input type="text" name="donor_name" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm text-gray-700">Email</label>
                                    <input type="email" name="donor_email" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400" />
                                </div>
                            </div>
                            <div>
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="is_anonymous" value="1" class="rounded border-gray-300 text-sky-600 focus:ring-sky-500" />
                                    Sembunyikan nama (anonim)
                                </label>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm text-gray-700">Pesan</label>
                                <input type="text" name="message" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400" />
                            </div>
                            <div class="mt-2 space-y-3">
                                <div class="relative inline-block w-full">
                                    <button type="submit" class="w-full inline-flex items-center justify-center rounded-md bg-orange-500 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-orange-600 mb-2">Donasi Sekarang</button>
                                    <button type="button" id="share-trigger" class="w-full inline-flex items-center justify-center rounded-md border border-orange-500 bg-white px-4 py-2.5 text-sm font-semibold text-orange-600 shadow-sm hover:bg-orange-50" aria-label="Share">
                                        <span class="">Bagikan</span>
                                    </button>
                                    <div id="share-popover" class="pointer-events-auto invisible absolute -top-16 left-0 z-20 translate-y-2 opacity-0 transition-all duration-150 ease-out">
                                        <div class="flex items-center gap-3 rounded-xl bg-white p-2 shadow-lg ring-1 ring-gray-200">
                                            <a href="https://wa.me/?text={{ $encText }}%20{{ $encUrl }}" target="_blank" rel="noopener" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-[#25D366] text-white hover:opacity-90" aria-label="WhatsApp">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4"><path d="M20.52 3.48A11.93 11.93 0 0012.05 0C5.53.02.25 5.29.27 11.81a11.76 11.76 0 001.64 6.07L0 24l6.3-1.82a11.86 11.86 0 005.73 1.49h.01c6.52 0 11.8-5.27 11.82-11.79a11.8 11.8 0 00-3.34-8.4z"/></svg>
                                            </a>
                                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ $encUrl }}" target="_blank" rel="noopener" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-[#1877F2] text-white hover:opacity-90" aria-label="Facebook">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4"><path d="M13.5 2.25a6.75 6.75 0 00-6.75 6.75v2.25H4.5a.75.75 0 00-.75.75v3a.75.75 0 00.75.75h2.25V21a.75.75 0 00.75.75h3a.75.75 0 00.75-.75v-5.25H14.6a.75.75 0 00.74-.63l.38-3a.75.75 0 00-.74-.87H11.25V9a2.25 2.25 0 012.25-2.25H15a.75.75 0 00.75-.75V3a.75.75 0 00-.75-.75h-1.5z"/></svg>
                                            </a>
                                            <a href="https://twitter.com/intent/tweet?text={{ $encText }}&url={{ $encUrl }}" target="_blank" rel="noopener" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-black text-white hover:opacity-90" aria-label="X">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4"><path d="M18.244 2H21l-6.52 7.455L22 22h-6.828l-5.34-7.027L3.6 22H1l7.035-8.04L2 2h6.914l4.83 6.42L18.244 2zm-2.392 18h1.662L7.225 4H5.47l10.382 16z"/></svg>
                                            </a>
                                            <a href="https://t.me/share/url?url={{ $encUrl }}&text={{ $encText }}" target="_blank" rel="noopener" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-[#229ED9] text-white hover:opacity-90" aria-label="Telegram">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4"><path d="M9.04 15.36l-.37 5.2c.53 0 .76-.23 1.03-.5l2.48-2.38 5.14 3.77c.94.52 1.6.25 1.84-.87l3.34-15.71h.01c.3-1.42-.51-1.98-1.43-1.63L1.4 9.93C.02 10.48.04 11.3 1.14 11.64l5.2 1.62 12.06-7.6c.57-.35 1.1-.16.67.22L9.04 15.36z"/></svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </aside>
        </div>
    </main>
    <script>
        document.querySelectorAll('.preset-amount').forEach(btn => {
            btn.addEventListener('click', () => {
                const val = btn.getAttribute('data-amount');
                const input = document.getElementById('amount-input');
                if (input) input.value = val;
            });
        });
        // Popover share toggle
        const trigger = document.getElementById('share-trigger');
        const pop = document.getElementById('share-popover');
        const openPop = () => {
            if (!pop) return;
            pop.classList.remove('opacity-0','invisible','translate-y-2');
        };
        const closePop = () => {
            if (!pop) return;
            pop.classList.add('opacity-0','invisible','translate-y-2');
        };
        trigger && trigger.addEventListener('click', (e) => {
            e.preventDefault();
            if (pop.classList.contains('invisible')) openPop(); else closePop();
        });
        document.addEventListener('click', (e) => {
            if (!pop || !trigger) return;
            if (pop.contains(e.target) || trigger.contains(e.target)) return;
            closePop();
        });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closePop(); });
    </script>
</body>
</html>
