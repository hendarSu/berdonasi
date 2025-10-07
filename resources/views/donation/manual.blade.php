<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pembayaran Manual — {{ env('APP_NAME') }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
    <main class="mx-auto max-w-2xl px-4 py-8">
        <div class="rounded-xl bg-white p-6 shadow">
            <h1 class="mb-1 text-xl font-semibold">Pembayaran Manual (Transfer)</h1>
            <p class="mb-4 text-sm text-gray-600">Referensi Donasi: <span class="font-mono">{{ $donation->reference }}</span></p>

            <div class="mb-6 space-y-2">
                <div class="text-sm">Jumlah Donasi</div>
                <div class="text-2xl font-bold">Rp {{ number_format((float)$donation->amount, 0, ',', '.') }}</div>
            </div>

            <div class="mb-6 rounded-lg border border-gray-200 p-4">
                <div class="mb-2 text-sm font-semibold">Informasi Rekening</div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                        <div class="flex items-center gap-3">
                            <div class="text-xs text-gray-500">No. Rekening</div>
                            <div id="acc-number" class="font-mono text-lg tracking-wide select-all">{{ $bank['account_number'] ?? '-' }}</div>
                        </div>
                        <button id="copy-acc-number" type="button" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">Salin</button>
                    </div>
                    <div class="text-sm text-gray-700">
                        <div><span class="text-gray-500">Bank:</span> <span class="font-medium">{{ $bank['name'] ?? '-' }}</span></div>
                        <div><span class="text-gray-500">Atas Nama:</span> <span class="font-medium">{{ $bank['account_name'] ?? '-' }}</span></div>
                    </div>
                    <div id="copy-hint" class="hidden text-xs text-green-600">Nomor rekening disalin.</div>
                </div>
                @if (!empty($qrUrl))
                    <div class="mt-4">
                        <div class="mb-1 text-sm text-gray-600">QRIS / QR Transfer:</div>
                        <img src="{{ $qrUrl }}" alt="QR" class="h-48 w-auto rounded-md border border-gray-200" />
                    </div>
                @endif
                @if (!empty($bank['instructions']))
                    <div class="mt-4">
                        <div class="mb-1 text-sm text-gray-600">Instruksi:</div>
                        <pre class="whitespace-pre-wrap rounded-md bg-gray-50 p-3 text-sm text-gray-800">{{ $bank['instructions'] }}</pre>
                    </div>
                @endif
            </div>

            <div class="mb-6">
                <div class="mb-2 text-sm font-semibold">Upload Bukti Transfer</div>
                <form method="post" action="{{ route('donation.manual.submit', ['reference' => $donation->reference]) }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <input type="file" name="proof" accept="image/*,.pdf" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                    @error('proof')
                    <div class="text-xs text-red-600">{{ $message }}</div>
                    @enderror
                    <input type="text" name="note" placeholder="Catatan (opsional)" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                    <button type="submit" class="inline-flex items-center rounded-md bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">Kirim Bukti</button>
                </form>
                @if (!empty($payment->manual_proof_path))
                    <div class="mt-3 text-xs text-gray-600">Bukti terakhir sudah diunggah. Anda dapat mengunggah ulang jika perlu.</div>
                @endif
            </div>

            <a href="{{ route('campaign.show', $donation->campaign->slug) }}" class="text-sky-600 hover:text-sky-700">← Kembali ke campaign</a>
        </div>
    </main>
</body>
</html>

<script>
    (function(){
        const btn = document.getElementById('copy-acc-number');
        const numEl = document.getElementById('acc-number');
        const hint = document.getElementById('copy-hint');
        if (btn && numEl) {
            btn.addEventListener('click', async () => {
                const text = (numEl.textContent || '').trim();
                if (!text) return;
                try {
                    await navigator.clipboard.writeText(text);
                    if (hint) {
                        hint.classList.remove('hidden');
                        setTimeout(() => hint.classList.add('hidden'), 1500);
                    }
                } catch (e) {
                    // fallback: select text
                    const range = document.createRange();
                    range.selectNodeContents(numEl);
                    const sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
            });
        }
    })();
</script>
