<div class="min-h-screen flex items-center justify-center bg-slate-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden">
        
        <!-- Header Tema Dapodik / Dinas -->
        <div class="bg-blue-900 px-6 py-6 text-center text-white">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-800 mb-2 border border-blue-700">
                <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                </svg>
            </div>
            <h2 class="text-xl font-bold tracking-wide">SISTEM INFORMASI SEKOLAH</h2>
            <p class="text-xs text-blue-200 mt-1">Silakan masuk menggunakan akun terdaftar</p>
        </div>

        <!-- Form Body -->
        <div class="px-8 py-6">
            @if (session()->has('status'))
                <div class="mb-4 text-sm font-medium text-green-600 bg-green-50 p-3 rounded border border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <form wire:submit.prevent="login" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email / Nama Pengguna</label>
                    <input wire:model="email" type="email" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent" placeholder="nama@sekolah.sch.id">
                    @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kata Sandi</label>
                    <input wire:model="password" type="password" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent" placeholder="••••••••">
                    @error('password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center">
                        <input wire:model="remember" type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-xs text-slate-600">Ingat Saya</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="text-xs text-blue-700 hover:underline font-medium">Lupa Kata Sandi?</a>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 bg-blue-700 hover:bg-blue-800 text-white font-semibold text-sm rounded-lg transition duration-200 shadow-md">
                    MASUK APLIKASI
                </button>
            </form>

            <!-- Footer Links -->
            <div class="mt-6 pt-4 border-t border-slate-100 space-y-3 text-center">
                <p class="text-xs text-slate-600">
                    Belum punya akun? 
                    <a href="{{ route('register') }}" class="text-blue-700 font-semibold hover:underline">Daftar Akun Baru</a>
                </p>
                <div>
                    <a href="{{ route('home') }}" class="inline-flex items-center text-xs text-slate-500 hover:text-blue-700 font-medium transition">
                        &larr; Kembali ke Beranda / Dashboard Umum
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>