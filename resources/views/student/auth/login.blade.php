<x-layouts.app title="Login Portal Siswa">
    <div class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Login Portal Siswa</h2>

            {{-- Form POST Standard Laravel --}}
            <form action="{{ route('student.login.post') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NISN / Email</label>
                    <input type="text" name="email" value="{{ old('email') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                    @error('email') 
                        <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                    @error('password') 
                        <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                <button type="submit" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md transition">
                    Masuk Portal Siswa
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>