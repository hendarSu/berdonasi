<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $a->title }} — {{ $c->title }} — {{ env('APP_NAME') }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
    <header class="bg-white border-b border-gray-200">
        <div class="mx-auto max-w-7xl px-4 py-3 flex items-center gap-4">
            <a href="{{ route('campaign.show', $c->slug) }}" class="text-sky-600 hover:text-sky-700">← Kembali ke Campaign</a>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <article class="lg:col-span-2 space-y-3">
            <h1 class="text-2xl font-bold leading-tight">{{ $a->title }}</h1>
            <div class="text-sm text-gray-500">{{ optional($a->published_at)->format('d M Y') ?? '—' }}</div>
            <div class="text-sm text-gray-600">Campaign: <a class="text-sky-600 hover:underline" href="{{ route('campaign.show', $c->slug) }}">{{ $c->title }}</a></div>
            @if ($a->payout?->amount)
                <div class="inline-block rounded-full bg-orange-50 px-2 py-1 text-xs text-orange-700 ring-1 ring-orange-200">Anggaran: Rp {{ number_format((float)$a->payout->amount, 2, ',', '.') }}</div>
            @endif
            @if ($a->cover_url)
                <img src="{{ $a->cover_url }}" alt="Cover" class="w-full rounded-md object-cover" />
            @endif
            @if ($a->body_md)
                <div class="prose max-w-none bg-white p-4 rounded-md shadow">
                    {!! $a->body_md !!}
                </div>
            @endif
        </article>

        <aside class="lg:col-span-1">
            <div class="sticky top-4 space-y-4">
                <div class="rounded-md bg-white p-5 shadow">
                    <h2 class="mb-3 text-lg font-semibold">Dukung Campaign Ini</h2>
                    <form method="post" action="{{ route('campaign.donate', $c->slug) }}" class="space-y-3">
                        @csrf
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
                        <button type="submit" class="w-full inline-flex items-center justify-center rounded-md bg-orange-500 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-orange-600">Donasi Sekarang</button>
                    </form>
                </div>
            </div>
        </aside>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btns = document.querySelectorAll('.preset-amount');
            const input = document.getElementById('amount-input');
            btns.forEach(b => {
                b.addEventListener('click', () => {
                    input.value = b.getAttribute('data-amount');
                    input.focus();
                });
            });
        });
    </script>
</body>
</html>
