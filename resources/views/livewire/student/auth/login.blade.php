<div class="min-h-screen bg-slate-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="text-center">
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                Portal Siswa
            </h2>
            <p class="mt-2 text-sm text-slate-600">
                Silakan masuk menggunakan akun siswa Anda
            </p>
        </div>
    </div>

    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md">
        {{-- Card Form Login --}}
        <div class="bg-white py-8 px-4 shadow-sm border border-slate-200 sm:rounded-2xl sm:px-10">
            
            <form wire:submit.prevent="login" class="space-y-5">
                {{-- Email / NISN Field --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">
                        NISN / Email
                    </label>
                    <div class="mt-1">
                        <input 
                            wire:model="email" 
                            id="email" 
                            type="text" 
                            required 
                            class="w-full px-3 py-2 border border-slate-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            placeholder="Masukkan NISN atau Email"
                        >
                    </div>
                    @error('email') 
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                {{-- Password Field --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">
                        Password
                    </label>
                    <div class="mt-1">
                        <input 
                            wire:model="password" 
                            id="password" 
                            type="password" 
                            required 
                            class="w-full px-3 py-2 border border-slate-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            placeholder="••••••••"
                        >
                    </div>
                    @error('password') 
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                {{-- Submit Button & Kembali ke Beranda (Sejajar dalam 1 Container) --}}
                <div class="space-y-3 pt-2">
                    <button 
                        type="submit" 
                        class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all"
                    >
                        <span wire:loading.remove wire:target="login">Masuk Sekarang</span>
                        <span wire:loading wire:target="login">Memproses...</span>
                    </button>

                    <a 
                        href="{{ route('home') }}" 
                        wire:navigate 
                        class="w-full flex justify-center items-center gap-2 py-2.5 px-4 border border-slate-300 rounded-xl shadow-sm text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 transition-all"
                    >
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Kembali ke Beranda</span>
                    </a>
                </div>
            </form>

        </div>
    </div>
</div>