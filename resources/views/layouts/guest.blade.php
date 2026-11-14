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
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">TOKO PERTANIAN AL BAROKAH</h1>
            <p class="text-xs font-bold text-emerald-600 mt-1.5 uppercase tracking-widest">Sistem Kasir POS, Inventori & Forecasting (SMA)</p>
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
