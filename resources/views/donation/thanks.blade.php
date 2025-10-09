<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Terima kasih — {{ env('APP_NAME') }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-white text-gray-900">
    <main class="bg-white mx-auto flex min-h-screen max-w-2xl items-center justify-center px-4">
        <div class="w-full bg-white p-8 text-center">
            <div class="mb-4 flex justify-center">
                <img src="{{ asset('image/terimakasih.png') }}" alt="Terima kasih" class="max-h-40 w-auto" />
            </div>
            <p class="text-gray-600">Permintaan donasi Anda sudah tercatat.</p>
            <a href="{{ route('home') }}" class="mt-6 inline-flex items-center rounded-md bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-sky-700">Kembali ke beranda</a>

        </div>
    </main>
</body>
</html>
