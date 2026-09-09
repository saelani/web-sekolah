@php
    $profile = \App\Models\SchoolProfile::first();
@endphp

<footer class="mt-16 text-gray-300 bg-gray-900 border-t border-gray-800">
    <div class="px-4 py-12 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
            
            {{-- Kolom 1: Profil Singkat --}}
            <div class="space-y-4 md:col-span-1">
                <div class="flex items-center gap-2 text-xl font-bold text-white">
                    @if($profile?->logo_path)
                        <img src="{{ asset('storage/' . $profile->logo_path) }}" class="object-contain w-8 h-8" alt="Logo">
                    @endif
                    <span>{{ $profile->school_name ?? 'Web Sekolah' }}</span>
                </div>
                <p class="text-sm text-gray-400 line-clamp-3">
                    {{ $profile->vision ?? 'Mewujudkan generasi cerdas, berkarakter, dan berprestasi unggul.' }}
                </p>
            </div>

            {{-- Kolom 2: Navigasi Cepat --}}
            <div>
                <h3 class="mb-4 text-sm font-semibold tracking-wider text-white uppercase">Navigasi</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('home') }}" class="transition hover:text-white">Beranda</a></li>
                    <li><a href="{{ url('/admin/login') }}" class="transition hover:text-white">Portal Guru</a></li>
                    <li><a href="{{ url('/api/v1/posts') }}" target="_blank" class="transition hover:text-white">API Services</a></li>
                </ul>
            </div>

            {{-- Kolom 3: Kontak Sekolah --}}
            <div>
                <h3 class="mb-4 text-sm font-semibold tracking-wider text-white uppercase">Kontak</h3>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li class="flex items-start gap-2">
                        <svg class="flex-shrink-0 w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>{{ $profile->address ?? 'Alamat sekolah belum diatur.' }}</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="flex-shrink-0 w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h32a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2V5z"/></svg>
                        <span>{{ $profile->phone ?? '-' }}</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="flex-shrink-0 w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>{{ $profile->email ?? '-' }}</span>
                    </li>
                </ul>
            </div>

            {{-- Kolom 4: Informasi Legal/NPSN --}}
            <div>
                <h3 class="mb-4 text-sm font-semibold tracking-wider text-white uppercase">Informasi</h3>
                <div class="p-4 space-y-2 text-xs bg-gray-800 rounded-lg">
                    <p><strong class="text-gray-200">NPSN:</strong> {{ $profile->npsn ?? '-' }}</p>
                    <p><strong class="text-gray-200">Akreditasi:</strong> {{ $profile->accreditation ?? '-' }}</p>
                    <p class="mt-2 text-gray-400">Terintegrasi dengan Aplikasi Mobile Android.</p>
                </div>
            </div>

        </div>

        {{-- Copyright --}}
        <div class="pt-8 mt-12 text-xs text-center text-gray-500 border-t border-gray-800">
            <p>&copy; {{ date('Y') }} {{ $profile->school_name ?? 'Sistem Sekolah' }}. All rights reserved.</p>
        </div>
    </div>
</footer>