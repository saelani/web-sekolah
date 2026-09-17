<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $title ?? 'Autentikasi - Web Sekolah' }}</title>
        <script src="https://cdn.tailwindcss.com"></script>
        @livewireStyles
    </head>
    <body class="bg-gray-50 font-sans antialiased">
        <!-- Tanpa Header dan Footer, langsung konten form -->
        {{ $slot }}
        @livewireScripts
    </body>
</html>