@php
    $studentUser = Auth::guard('student')->user() ?? (auth()->check() && auth()->user()->student ? auth()->user() : null);
    $studentData = $studentUser->student ?? $studentUser;
    $isTeacherOrAdmin = auth()->check() && !$studentUser;
@endphp

<header class="sticky top-0 z-50 bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-900 text-white shadow-lg border-b border-indigo-800/50">
    <div class="flex items-center justify-between h-16 px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        
        {{-- Brand / Logo --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
            <div class="p-2 rounded-xl bg-indigo-600/80 text-white shadow-inner group-hover:scale-105 transition-transform duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                </svg>
            </div>
            <span class="text-lg font-bold text-white tracking-wide">Portal Sekolah</span>
        </a>

        {{-- Area Tombol Akses --}}
        <div class="flex items-center gap-2 sm:gap-3">
            
            @if($studentUser)
                <a href="{{ route('student.dashboard') }}" class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-emerald-300 border border-emerald-500/30 rounded-xl bg-emerald-950/50 hover:bg-emerald-900/60 sm:text-sm sm:px-4 transition">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Portal Siswa ({{ $studentData->name ?? 'Siswa' }})</span>
                </a>
            @else
                <a href="{{ route('student.login') }}" class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-emerald-300 border border-emerald-500/30 rounded-xl bg-emerald-950/40 hover:bg-emerald-900/60 sm:text-sm sm:px-4 transition">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                    </svg>
                    <span>Login Siswa</span>
                </a>
            @endif

            <div class="h-5 w-px bg-slate-700 hidden sm:block mx-1"></div>

            @if($isTeacherOrAdmin)
                <a href="{{ url('/admin') }}" class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-indigo-200 border border-indigo-500/30 rounded-xl bg-indigo-900/50 hover:bg-indigo-800/60 sm:text-sm sm:px-4 transition">
                    <svg class="w-4 h-4 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    <span>Dashboard Guru</span>
                </a>
            @else
                <a href="{{ url('/admin/login') }}" class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-md sm:text-sm sm:px-4 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    <span>Login Guru</span>
                </a>
            @endif

        </div>

    </div>
</header>