<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Toko Pertanian Al Barokah') }} - Masuk Sistem</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-100 min-h-screen flex items-center justify-center p-4 sm:p-6">
    <div class="w-full max-w-md">
        
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-emerald-600 text-white shadow-sm mb-4">
                <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">TOKO PERTANIAN AL BAROKAH</h1>
            <p class="text-xs font-bold text-emerald-600 mt-1 uppercase tracking-widest">Sistem Kasir POS, Inventori & Forecasting (SMA)</p>
        </div>

        <!-- Auth Card -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8 sm:p-10">
            {{ $slot }}
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} Toko Pertanian Al Barokah. Hak cipta dilindungi.
        </div>

    </div>
</body>
</html>
