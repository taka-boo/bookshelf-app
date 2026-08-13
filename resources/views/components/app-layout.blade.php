<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        {{-- 共通フラッシュメッセージ（青：追加・更新系） --}}
        @if (session('success'))
            <div id="flash-message"
                class="fixed top-20 left-1/2 -translate-x-1/2 z-50 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded shadow-lg transition-opacity duration-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- 共通フラッシュメッセージ（赤：削除・解除系） --}}
        @if (session('removed'))
            <div id="flash-message"
                class="fixed top-20 left-1/2 -translate-x-1/2 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-lg transition-opacity duration-700">
                {{ session('removed') }}
            </div>
        @endif

        {{-- 共通フラッシュメッセージ（赤：エラー系） --}}
        @if (session('error'))
            <div id="flash-message"
                class="fixed top-20 left-1/2 -translate-x-1/2 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-lg transition-opacity duration-700">
                {{ session('error') }}
            </div>
        @endif

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>

    @stack('scripts')

    <script>
        // スクロール位置の保持
        document.addEventListener('submit', function () {
            sessionStorage.setItem('scrollY', window.scrollY);
        });

        window.addEventListener('load', function () {
            var y = sessionStorage.getItem('scrollY');
            if (y !== null) {
                window.scrollTo(0, parseInt(y));
                sessionStorage.removeItem('scrollY');
            }
        });

        // フラッシュメッセージのフェードアウト
        var flash = document.getElementById('flash-message');
        if (flash) {
            setTimeout(function () {
                flash.classList.add('opacity-0');
                setTimeout(function () { flash.remove(); }, 700);
            }, 2500);
        }
    </script>
</body>

</html>