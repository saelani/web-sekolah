<div class="bg-gray-50 min-h-screen">
    {{-- Hero Banner / Profil Sekolah --}}
    {{-- Hero Banner / Profil Sekolah --}}
    <section class="relative bg-gradient-to-b from-slate-950 via-slate-900 to-slate-900 text-white pt-10 pb-16 px-4 sm:px-6 lg:px-8 border-b border-slate-800/80 overflow-hidden">
        {{-- Background Glow Effect --}}
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-8 text-center md:text-left">
            
            {{-- Sisi Kiri: Text Profil (Rata Tengah di HP, Rata Kiri di Desktop) --}}
            <div class="space-y-4 max-w-2xl flex flex-col items-center md:items-start">
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight text-white leading-tight">
                    {{ $profile->school_name ?? 'SDN 2 Babakanmulya' }}
                </h1>
                
                <p class="text-slate-300 text-base sm:text-lg max-w-xl font-normal leading-relaxed">
                    {{ $profile->vision ?? 'Mewujudkan generasi cerdas, berkarakter, dan berprestasi.' }}
                </p>
                
                <div class="pt-1">
                    <span class="inline-flex items-center gap-2 bg-slate-800/90 border border-slate-700/80 text-slate-200 px-4 py-2 rounded-xl text-xs sm:text-sm font-medium shadow-inner backdrop-blur-sm">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Akreditasi: <strong class="text-white">{{ $profile->accreditation ?? 'A' }}</strong> 
                        <span class="text-slate-600">|</span> 
                        NPSN: <strong class="text-white">{{ $profile->npsn ?? '20213175' }}</strong>
                    </span>
                </div>
            </div>

            {{-- Sisi Kanan: Logo Sekolah (Rata Tengah di HP, Kanan di Desktop) --}}
            @if($profile?->logo_path)
                <div class="flex-shrink-0 order-first md:order-last">
                    <div class="relative group">
                        <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-blue-500 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-300"></div>
                        <img 
                            src="{{ asset('storage/' . $profile->logo_path) }}" 
                            alt="Logo Sekolah" 
                            class="relative h-28 sm:h-36 lg:h-40 w-auto object-contain filter drop-shadow-xl transition-transform duration-300 group-hover:scale-105"
                        >
                    </div>
                </div>
            @endif

        </div>
    </section>

    {{-- SECTION CAROUSEL FOTO --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 sm:-mt-8">
        <div x-data="{ 
                activeSlide: 0,
                totalSlides: {{ $posts->whereNotNull('thumbnail_path')->take(5)->count() > 0 ? $posts->whereNotNull('thumbnail_path')->take(5)->count() : 3 }},
                timer: null,
                init() {
                    this.timer = setInterval(() => {
                        this.activeSlide = (this.activeSlide + 1) % this.totalSlides;
                    }, 5000);
                }
             }" 
             class="relative w-full h-[260px] sm:h-[400px] lg:h-[450px] rounded-2xl overflow-hidden shadow-2xl border-4 border-white bg-slate-900">

            {{-- OPSI A: Foto Dinamis dari Berita/Kegiatan Terbaru --}}
            @php
                $carouselPosts = $posts->whereNotNull('thumbnail_path')->take(5);
            @endphp

            @if($carouselPosts->count() > 0)
                @foreach($carouselPosts as $index => $cPost)
                    <div x-show="activeSlide === {{ $index }}"
                         x-transition:enter="transition ease-out duration-700"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute inset-0 w-full h-full"
                         style="display: none;">
                        
                        <img src="{{ asset('storage/' . $cPost->thumbnail_path) }}" alt="{{ $cPost->title }}" class="w-full h-full object-cover">
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-900/30 to-transparent flex flex-col justify-end p-6 sm:p-10 text-white">
                            <span class="text-xs font-bold text-blue-400 uppercase tracking-wider mb-1">
                                {{ $cPost->category?->name ?? 'Kegiatan Sekolah' }}
                            </span>
                            <h3 class="text-lg sm:text-2xl lg:text-3xl font-bold mb-1 tracking-wide drop-shadow-md">
                                {{ $cPost->title }}
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-200 max-w-2xl line-clamp-2">
                                {{ $cPost->excerpt }}
                            </p>
                        </div>
                    </div>
                @endforeach
            @else
                {{-- OPSI B: Foto Fallback (Default) jika Belum Ada Foto Berita --}}
                <div x-show="activeSlide === 0" class="absolute inset-0 w-full h-full">
                    <img src="https://images.unsplash.com/photo-1580582932707-520aed937b7b?q=80&w=1200" alt="Gedung Sekolah" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent p-6 text-white flex items-end">
                        <h3 class="text-xl font-bold">Gedung & Lingkungan Sekolah</h3>
                    </div>
                </div>
                <div x-show="activeSlide === 1" class="absolute inset-0 w-full h-full" style="display: none;">
                    <img src="https://images.unsplash.com/photo-1509062522246-3755977927d7?q=80&w=1200" alt="Kegiatan Belajar" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent p-6 text-white flex items-end">
                        <h3 class="text-xl font-bold">Kegiatan Belajar Mengajar Interaktif</h3>
                    </div>
                </div>
                <div x-show="activeSlide === 2" class="absolute inset-0 w-full h-full" style="display: none;">
                    <img src="https://images.unsplash.com/photo-1577896851231-70ef18881754?q=80&w=1200" alt="Perpustakaan" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent p-6 text-white flex items-end">
                        <h3 class="text-xl font-bold">Fasilitas Perpustakaan Digital</h3>
                    </div>
                </div>
            @endif

            {{-- Tombol Navigasi Kiri / Kanan --}}
            <button @click="activeSlide = (activeSlide === 0) ? totalSlides - 1 : activeSlide - 1" 
                    class="absolute left-4 top-1/2 -translate-y-1/2 p-2 sm:p-3 rounded-full bg-slate-900/40 hover:bg-slate-900/80 text-white backdrop-blur-sm transition border border-white/20 z-10">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>

            <button @click="activeSlide = (activeSlide + 1) % totalSlides" 
                    class="absolute right-4 top-1/2 -translate-y-1/2 p-2 sm:p-3 rounded-full bg-slate-900/40 hover:bg-slate-900/80 text-white backdrop-blur-sm transition border border-white/20 z-10">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            {{-- Indikator Titik (Dots) --}}
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                <template x-for="i in totalSlides" :key="i">
                    <button @click="activeSlide = i - 1" 
                            :class="activeSlide === (i - 1) ? 'w-8 bg-blue-500' : 'w-2.5 bg-white/60 hover:bg-white'"
                            class="h-2.5 rounded-full transition-all duration-300"></button>
                </template>
            </div>
        </div>
    </div>

    {{-- Content Utama --}}
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