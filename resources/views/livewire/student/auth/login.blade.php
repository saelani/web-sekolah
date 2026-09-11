<div class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8">

        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">
            Login Portal Siswa
        </h2>

        <form wire:submit="login" class="space-y-5">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    NISN / Email
                </label>

                <input
                    type="text"
                    wire:model="email"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                >

                @error('email')
                    <span class="text-xs text-rose-600 mt-1 block">
                        {{ $message }}
                    </span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Password
                </label>

                <input
                    type="password"
                    wire:model="password"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                >

                @error('password')
                    <span class="text-xs text-rose-600 mt-1 block">
                        {{ $message }}
                    </span>
                @enderror
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md transition duration-200"
            >
                <span wire:loading.remove>
                    Masuk Portal Siswa
                </span>

                <span wire:loading>
                    Memproses...
                </span>
            </button>

        </form>

    </div>
</div>