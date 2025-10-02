<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bayar Donasi — {{ env('APP_NAME') }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    <script type="text/javascript" src="{{ $snapJsUrl }}" data-client-key="{{ $clientKey }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.snap && '{{ $snapToken }}') {
                snap.pay('{{ $snapToken }}', {
                    onSuccess: function(result){
                        window.location.href = '{{ route('donation.thanks', ['reference' => $donation->reference]) }}';
                    },
                    onPending: function(result){
                        window.location.href = '{{ route('donation.thanks', ['reference' => $donation->reference]) }}';
                    },
                    onError: function(result){
                        alert('Pembayaran gagal. Silakan coba lagi.');
                    },
                    onClose: function(){
                        // user closed the popup without finishing the payment
                    }
                });
            }
        });
    </script>
    <style>
        .spinner { width: 24px; height: 24px; border: 3px solid #e5e7eb; border-top-color: #0284c7; border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
    </head>
<body class="bg-gray-50 text-gray-900">
    <main class="mx-auto flex min-h-screen max-w-2xl items-center justify-center px-4">
        <div class="w-full rounded-xl bg-white p-8 text-center shadow">
            <div class="mx-auto mb-4 spinner"></div>
            <h1 class="mb-2 text-2xl font-bold">Memproses pembayaran…</h1>
            <p class="text-gray-600">Harap tunggu, Anda akan diarahkan ke popup pembayaran.</p>
            <p class="mt-2 text-sm text-gray-500">Referensi: <span class="font-mono">{{ $donation->reference }}</span></p>
        </div>
    </main>
</body>
</html>

