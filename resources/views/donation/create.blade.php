<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donasi — {{ $c->title }} — {{ env('APP_NAME') }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-white sm:bg-gray-50 text-gray-900">
    <header class="bg-white border-b border-gray-200">
        <div class="mx-auto max-w-7xl px-4 py-3 flex items-center justify-between gap-4">
            <a href="{{ route('campaign.show', $c->slug) }}" class="text-sky-600 hover:text-sky-700">← Kembali</a>
            {{-- <div class="text-sm text-gray-600">{{ $c->title }}</div> --}}
        </div>
    </header>

    <main class="mx-auto max-w-2xl px-4 py-8">
        <div class="rounded-md p-0 ">
            <h1 class="mb-4 text-xl font-semibold">Donasi untuk: {{ $c->title }}</h1>
            <form method="post" action="{{ route('campaign.donate', $c->slug) }}" class="space-y-3" id="donation-form">
                @csrf
                @php
                    $presets = [50000,250000,500000,1000000, 1500000, 2500000, 5000000, 10000000];
                @endphp
                <div>
                    <label class="mb-1 block text-sm text-gray-700">Jumlah Donasi (Rp)</label>
                    <div class="mb-2 grid grid-cols-4 gap-2">
                        @foreach ($presets as $preset)
                            <button type="button" data-amount="{{ $preset }}" class="preset-amount rounded-md border border-gray-200 bg-gray-50 px-2 py-1 text-xs hover:border-sky-400">{{ number_format($preset,0,',','.') }}</button>
                        @endforeach
                    </div>
                    <input id="amount-input" type="number" name="amount" min="1000" step="1000" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400" placeholder="100000" />
                </div>
                <div class="grid grid-cols-1 gap-3">
                    <div>
                        <label class="mb-1 block text-sm text-gray-700">Nama</label>
                        <input type="text" name="donor_name" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm text-gray-700">Nomor HP</label>
                        <input type="text" name="donor_phone" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400" />
                        @error('donor_phone')
                        <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                        <div id="wa-hint" class="mt-1 text-xs text-gray-500 hidden">Memeriksa nomor WhatsApp…</div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm text-gray-700">Email <span class="text-gray-500">(opsional)</span></label>
                        <input type="email" name="donor_email" placeholder="Opsional" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400" />
                    </div>
                </div>
                <div>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_anonymous" value="1" class="rounded border-gray-300 text-sky-600 focus:ring-sky-500" />
                        Sembunyikan nama (anonim)
                    </label>
                </div>
                <div>
                    <label class="mb-1 block text-sm text-gray-700">Doa Terbaik</label>
                    <input type="text" name="message" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400" />
                </div>
                <div>
                    <label class="mb-2 block text-sm text-gray-700">Metode Pembayaran</label>
                    <div class="grid grid-cols-1 gap-3"> <!-- force 2 rows (1 col) -->
                        @php
                            $defaultAutomatic = isset($automaticEnabled) ? (bool)$automaticEnabled : true;
                            $defaultManual = isset($manualEnabled) ? (bool)$manualEnabled : true;
                            $defaultChoice = $defaultAutomatic ? 'automatic' : ($defaultManual ? 'manual' : null);
                        @endphp
                        @if ($automaticEnabled ?? true)
                        <label class="flex items-center gap-3 rounded-lg border-2 border-gray-200 bg-white px-4 py-3 text-base shadow-sm hover:border-sky-300">
                            <input type="radio" name="payment_type" value="automatic" {{ ($defaultChoice === 'automatic') ? 'checked' : '' }} class="h-5 w-5 text-sky-600 focus:ring-sky-500" />
                            <span class="font-medium">Otomatis (Midtrans)</span>
                        </label>
                        @endif
                        @if ($manualEnabled ?? true)
                        <label class="flex items-center gap-3 rounded-lg border-2 border-gray-200 bg-white px-4 py-3 text-base shadow-sm hover:border-sky-300">
                            <input type="radio" name="payment_type" value="manual" {{ ($defaultChoice === 'manual') ? 'checked' : '' }} class="h-5 w-5 text-sky-600 focus:ring-sky-500" />
                            <span class="font-medium">Manual (Transfer)</span>
                        </label>
                        @endif
                    </div>
                    @if (!(($automaticEnabled ?? true) || ($manualEnabled ?? true)))
                        <div class="mt-2 rounded-md bg-red-50 px-3 py-2 text-xs text-red-700">Metode pembayaran tidak tersedia. Hubungi administrator.</div>
                    @endif
                </div>
                <div class="mt-3 relative" id="submit-wrapper">
                    <button id="submit-btn" type="submit" class="w-full inline-flex items-center justify-center rounded-md bg-orange-500 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-orange-600">Lanjutkan Pembayaran</button>
                    <div id="wa-block-overlay" class="hidden absolute inset-0 bg-white/70 backdrop-blur-[1px] flex items-center justify-center text-sm text-gray-700 rounded-md" style="
    background: #c0c0c0;
">
                        Nomor WhatsApp tidak valid.
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script>
        window.WA_VALIDATE_ENABLED = {{ isset($waValidationEnabled) && $waValidationEnabled ? 'true' : 'false' }};
        window.WA_VALIDATE_URL = '{{ route('wa.validate') }}';

        document.querySelectorAll('.preset-amount').forEach(btn => {
            btn.addEventListener('click', () => {
                const val = btn.getAttribute('data-amount');
                const input = document.getElementById('amount-input');
                if (input) input.value = val;
            });
        });

        (function() {
            if (!window.WA_VALIDATE_ENABLED) return;

            const form = document.getElementById('donation-form');
            const phoneInput = form.querySelector('input[name="donor_phone"]');
            const submitBtn = document.getElementById('submit-btn');
            const overlay = document.getElementById('wa-block-overlay');
            const hint = document.getElementById('wa-hint');

            let currentValid = true;
            let timer = null;

            function setValidState(isValid) {
                currentValid = !!isValid;
                if (currentValid) {
                    overlay.classList.add('hidden');
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-60','cursor-not-allowed');
                } else {
                    overlay.classList.remove('hidden');
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-60','cursor-not-allowed');
                }
            }

            async function validateNow() {
                const number = (phoneInput.value || '').trim();
                if (number === '') { setValidState(false); return; }
                hint.classList.remove('hidden');
                try {
                    const resp = await fetch(window.WA_VALIDATE_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': (form.querySelector('input[name="_token"]').value),
                        },
                        body: JSON.stringify({ number })
                    });
                    const data = await resp.json();
                    const ok = !!(data && data.ok !== false);
                    const isReg = !!(data && data.isRegistered !== false);
                    setValidState(ok && isReg);
                } catch (e) {
                    setValidState(false);
                } finally {
                    hint.classList.add('hidden');
                }
            }

            function debounceValidate() {
                if (timer) clearTimeout(timer);
                timer = setTimeout(validateNow, 500);
            }

            phoneInput.addEventListener('input', debounceValidate);
            phoneInput.addEventListener('blur', validateNow);

            // Initial state
            setValidState(false);
        })();

    </script>
</body>
</html>
