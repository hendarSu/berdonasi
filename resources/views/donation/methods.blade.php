<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Metode — {{ env('APP_NAME') }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-white text-gray-900">
    <main class="mx-auto max-w-2xl px-4 py-8">
        <h1 class="text-xl font-semibold">Metode Pembayaran</h1>
        <p class="mt-1 text-sm text-gray-600">Referensi: <span class="font-mono">{{ $donation->reference }}</span></p>

        <form method="post" action="{{ route('donation.choose.method', $donation->reference) }}" class="mt-5 space-y-3">
            @csrf
            <div class="space-y-3">
                @foreach ($methods as $m)
                    <label class="block">
                        <input class="peer sr-only" type="radio" name="payment_method" value="{{ $m['id'] }}" required>
                        <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3 hover:border-gray-300 peer-checked:border-sky-500 peer-checked:ring-2 peer-checked:ring-sky-100 transition">
                            <div class="flex items-center gap-3 min-w-0">
                                <img src="{{ $m['logo'] }}" alt="" class="h-7 w-7 rounded object-contain bg-white" />
                                <div class="min-w-0">
                                    <div class="text-sm font-medium text-gray-900 truncate">{{ $m['name'] }}</div>
                                    <div class="text-xs text-gray-500">{{ $catalog->feeText($m) }}</div>
                                </div>
                            </div>
                            <span class="shrink-0 inline-flex items-center gap-1 rounded-full border border-sky-100 bg-sky-50 px-2.5 py-1 text-[11px] font-semibold text-sky-700">
                                <span class="h-2 w-2 rounded-full bg-sky-400 animate-pulse"></span> Midtrans
                            </span>
                        </div>
                    </label>
                @endforeach
            </div>

            @error('payment_method')
                <div class="text-xs text-red-600">{{ $message }}</div>
            @enderror

            <button type="submit" class="mt-2 inline-flex items-center rounded-md bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">Lanjut</button>
        </form>

        <div class="mt-6">
            <a href="{{ route('campaign.show', $donation->campaign->slug) }}" class="text-sky-600 hover:text-sky-700">← Kembali ke campaign</a>
        </div>
    </main>
</body>
</html>

