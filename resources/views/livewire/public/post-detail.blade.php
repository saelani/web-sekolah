<div class="max-w-4xl mx-auto px-4 py-8">
    {{-- Tombol Kembali --}}
    <div class="mb-6">
        <a href="{{ url('/') }}" wire:navigate class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Berita
        </a>
    </div>

    <article class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Header Artikel --}}
        <header class="p-6 md:p-8 border-b border-gray-100 space-y-4">
            <span class="inline-block px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold uppercase tracking-wider rounded-full">
                {{ $post->category?->name ?? 'Berita' }}
            </span>

            <h1 class="text-2xl md:text-4xl font-extrabold text-gray-900 leading-tight">
                {{ $post->title }}
            </h1>

            {{-- Profil Penulis & Tanggal --}}
            <div class="flex items-center space-x-3 text-sm text-gray-500 pt-2">
                {{-- Foto Profil Penulis --}}
                @if($post->author?->avatar)
                    <img src="{{ asset('storage/' . $post->author->avatar) }}" alt="{{ $post->author->name }}" class="w-10 h-10 rounded-full object-cover">
                @else
                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 font-bold flex items-center justify-center">
                        {{ strtoupper(substr($post->author?->name ?? 'Admin', 0, 1)) }}
                    </div>
                @endif

                <div>
                    <p class="font-semibold text-gray-800">{{ $post->author?->name ?? 'Admin Sekolah' }}</p>
                    <p class="text-xs text-gray-500">{{ $post->published_at ? $post->published_at->format('d M Y') : $post->created_at->format('d M Y') }}</p>
                </div>
            </div>
        </header>

        {{-- Foto Utama Berita (Featured Image) --}}
        @if ($post->thumbnail_path || $post->image)
            <div class="w-full max-h-[500px] overflow-hidden bg-gray-100">
                <img 
                    src="{{ asset('storage/' . ($post->thumbnail_path ?? $post->image)) }}" 
                    alt="{{ $post->title }}" 
                    class="w-full h-full object-cover"
                >
            </div>
        @endif

        {{-- Isi Berita --}}
        <div class="p-6 md:p-8 prose max-w-none text-gray-700 leading-relaxed space-y-4">
            {!! $post->content !!}
        </div>

        {{-- Opsional: Foto Tambahan / Galeri jika ada --}}
        @if(!empty($post->gallery) && is_array($post->gallery))
            <div class="p-6 md:p-8 border-t border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Dokumentasi / Foto Terkait</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($post->gallery as $photo)
                        <img src="{{ asset('storage/' . $photo) }}" alt="Foto Dokumentasi" class="w-full h-32 md:h-48 object-cover rounded-xl shadow-sm border">
                    @endforeach
                </div>
            </div>
        @endif
    </article>
</div>