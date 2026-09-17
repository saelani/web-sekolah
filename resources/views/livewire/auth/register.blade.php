<div class="min-h-screen flex items-center justify-center bg-slate-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden">
        
        <!-- Header -->
        <div class="bg-blue-900 px-6 py-5 text-center text-white">
            <h2 class="text-lg font-bold tracking-wide">REGISTRASI AKUN SISWA</h2>
            <p class="text-xs text-blue-200 mt-1">Lengkapi data diri untuk mendatakan akun baru</p>
        </div>

        <!-- Form Body -->
        <div class="px-8 py-6">
            <form wire:submit.prevent="register" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap</label>
                    <input wire:model="name" type="text" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none" placeholder="Sesuai Ijazah/Dapodik">
                    @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alamat Email</label>
                    <input wire:model="email" type="email" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none" placeholder="email@domain.com">
                    @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kata Sandi</label>
                    <input wire:model="password" type="password" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none" placeholder="Minimal 6 karakter">
                    @error('password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Konfirmasi Kata Sandi</label>
                    <input wire:model="password_confirmation" type="password" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none" placeholder="Ulangi kata sandi">
                </div>

                <button type="submit" class="w-full py-2.5 px-4 bg-blue-700 hover:bg-blue-800 text-white font-semibold text-sm rounded-lg transition duration-200 shadow-md">
                    DAFTARKAN AKUN
                </button>
            </form>

            <div class="mt-6 text-center border-t border-slate-100 pt-4">
                <p class="text-xs text-slate-600">
                    Sudah terdaftar? 
                    <a href="{{ route('login') }}" class="text-blue-700 font-semibold hover:underline">Masuk ke Sistem</a>
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