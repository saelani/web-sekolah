<div class="bg-gray-50 min-h-screen">
    {{-- Hero Banner / Profil Sekolah (Warna disesuaikan dengan header bg-slate-900) --}}
    <section class="bg-slate-900 text-white py-16 px-4 sm:px-6 lg:px-8 border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="space-y-4 max-w-2xl">
                <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl text-white">
                    {{ $profile->school_name ?? 'Selamat Datang di Web Sekolah' }}
                </h1>
                <p class="text-slate-300 text-lg">
                    {{ $profile->vision ?? 'Mewujudkan generasi cerdas, berkarakter, dan berprestasi.' }}
                </p>
                <div class="pt-2">
                    <span class="inline-block bg-slate-800 border border-slate-700 text-slate-200 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm">
                        Akreditasi: {{ $profile->accreditation ?? 'A' }} | NPSN: {{ $profile->npsn ?? '-' }}
                    </span>
                </div>
            </div>

            {{-- Container Logo dengan trik menghilangkan background hitam --}}
            @if($profile?->logo_path)
                <div class="flex-shrink-0">
                    <img 
                        src="{{ asset('storage/' . $profile->logo_path) }}" 
                        alt="Logo Sekolah" 
                        class="h-40 w-40 object-contain mix-blend-screen filter drop-shadow-md"
                    >
                </div>
            @endif
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-16">
        
        {{-- Section Berita & Artikel --}}
        <section>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900">Berita & Pengumuman</h2>
                    <p class="text-gray-600 mt-1">Kabar terbaru seputar kegiatan sekolah.</p>
                </div>
                {{-- Livewire Live Search --}}
                <div class="w-full md:w-72">
                    <input 
                        wire:model.live.debounce.300ms="search" 
                        type="text" 
                        placeholder="Cari berita..." 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm"
                    >
                </div>
            </div>

            @if($posts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($posts as $post)
                        <article 
                            wire:key="post-{{ $post->id }}"
                            class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-all border border-gray-100 flex flex-col group relative"
                        >
                            {{-- Thumbnail Berita --}}
                            <div class="overflow-hidden h-48 w-full bg-gray-200">
                                <a href="{{ route('berita.show', $post->slug ?? $post->id) }}" wire:navigate class="block h-full w-full">
                                    @if($post->thumbnail_path)
                                        <img src="{{ asset('storage/' . $post->thumbnail_path) }}" alt="{{ $post->title }}" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="h-full w-full flex items-center justify-center text-gray-400">No Image</div>
                                    @endif
                                </a>
                            </div>

                            {{-- Isi Berita --}}
                            <div class="p-6 flex-1 flex flex-col justify-between">
                                <div>
                                    <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">
                                        {{ $post->category?->name ?? 'Umum' }}
                                    </span>
                                    
                                    <h3 class="text-xl font-bold text-gray-900 mt-2 line-clamp-2">
                                        <a href="{{ route('berita.show', $post->slug ?? $post->id) }}" wire:navigate class="hover:text-blue-600 transition-colors before:absolute before:inset-0">
                                            {{ $post->title }}
                                        </a>
                                    </h3>

                                    <p class="text-gray-600 text-sm mt-2 line-clamp-3">
                                        {{ $post->excerpt }}
                                    </p>
                                </div>

                                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500 z-10">
                                    <span>{{ $post->published_at?->format('d M Y') }}</span>
                                    <span class="text-blue-600 font-semibold group-hover:underline">Baca &rarr;</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="mt-8">
                    {{ $posts->links() }}
                </div>
            @else
                <div class="bg-white p-8 text-center rounded-xl border text-gray-500">
                    Tidak ada berita yang ditemukan.
                </div>
            @endif
        </section>

        {{-- Section Fasilitas --}}
        <section>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Fasilitas Sekolah</h2>
            <p class="text-gray-600 mb-8">Sarana dan prasarana penunjang kegiatan belajar mengajar.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($facilities as $facility)
                    <div wire:key="facility-{{ $facility->id }}" class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm flex items-start gap-4">
                        @if($facility->image_path)
                            <img src="{{ asset('storage/' . $facility->image_path) }}" alt="{{ $facility->name }}" class="w-20 h-20 rounded-lg object-cover flex-shrink-0">
                        @endif
                        <div>
                            <h4 class="font-bold text-gray-900">{{ $facility->name }}</h4>
                            <p class="text-xs text-gray-600 mt-1 line-clamp-2">{{ $facility->description }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Section Prestasi --}}
        <section>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Prestasi Siswa</h2>
            <p class="text-gray-600 mb-8">Kebanggaan dan pencapaian akademik maupun non-akademik.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($achievements as $achievement)
                    <div wire:key="achievement-{{ $achievement->id }}" class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">
                                {{ $achievement->level }}
                            </span>
                            <span class="text-xs text-gray-400">
                                {{ $achievement->achievement_date?->format('Y') }}
                            </span>
                        </div>
                        <h4 class="font-bold text-gray-900 text-lg">{{ $achievement->title }}</h4>
                        <p class="text-sm text-gray-600">Pemenang: <span class="font-medium text-gray-800">{{ $achievement->winner_name }}</span></p>
                    </div>
                @endforeach
            </div>
        </section>

    </div>
</div>