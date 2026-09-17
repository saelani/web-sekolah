<div class="min-h-screen flex items-center justify-center bg-slate-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden">
        
        <!-- Header -->
        <div class="bg-blue-900 px-6 py-6 text-center text-white">
            <h2 class="text-xl font-bold tracking-wide">PEMULIHAN KATA SANDI</h2>
            <p class="text-xs text-blue-200 mt-1">Masukkan email terdaftar untuk mereset sandi Anda</p>
        </div>

        <!-- Form Body -->
        <div class="px-8 py-6">
            @if (session()->has('status'))
                <div class="mb-4 text-sm font-medium text-green-600 bg-green-50 p-3 rounded border border-green-200 text-center">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Form Kirim Tautan / Reset Langsung -->
            <form wire:submit.prevent="resetPassword" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email / NISN Terdaftar</label>
                    <input wire:model="email" type="email" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent" placeholder="nama@sekolah.sch.id">
                    @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kata Sandi Baru</label>
                    <input wire:model="password" type="password" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent" placeholder="••••••••">
                    @error('password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Konfirmasi Kata Sandi Baru</label>
                    <input wire:model="password_confirmation" type="password" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent" placeholder="••••••••">
                </div>

                <button type="submit" class="w-full py-2.5 px-4 bg-blue-700 hover:bg-blue-800 text-white font-semibold text-sm rounded-lg transition duration-200 shadow-md">
                    PERBARUI KATA SANDI
                </button>
            </form>

            <!-- Footer Links -->
            <div class="mt-6 pt-4 border-t border-slate-100 space-y-3 text-center">
                <p class="text-xs text-slate-600">
                    Ingat kata sandi Anda? 
                    <a href="{{ route('login') }}" class="text-blue-700 font-semibold hover:underline">Masuk di sini</a>
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