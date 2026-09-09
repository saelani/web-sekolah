<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $title ?? 'Web Sekolah' }}</title>

        <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>

        @livewireStyles
    </head>
    <body class="flex flex-col min-h-screen font-sans text-gray-900 bg-gray-50 antialiased">
        
        {{-- HEADER STATIS (Di luar Livewire DOM) --}}
        <x-header />

        {{-- MAIN CONTENT (Hanya area ini yang diproses Livewire) --}}
        <main class="flex-grow">
            {{ $slot }}
        </main>

        {{-- FOOTER STATIS (Di luar Livewire DOM) --}}
        <x-footer />

        @livewireScripts
    </body>
</html>