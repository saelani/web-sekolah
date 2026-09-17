<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- NOTIFIKASI PREVIEW ADMIN --}}
    @if($isAdminPreview)
        <div class="p-3.5 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-sm flex items-center gap-2 shadow-sm">
            <span>⚠️</span>
            <span><strong>Mode Preview Admin:</strong> Anda sedang melihat tampilan dashboard siswa.</span>
        </div>
    @endif

    {{-- TAMPILAN JIKA SEDANG UJIAN (CBT NATIVE) --}}
    @if($isTakingExam && $activeExam)
        <div x-data="{ 
            secondsLeft: @entangle('remainingSeconds'),
            init() {
                let timer = setInterval(() => {
                    if (this.secondsLeft > 0) {
                        this.secondsLeft--;
                    } else {
                        clearInterval(timer);
                        $wire.submitExam();
                    }
                }, 1000);
            },
            formatTime(sec) {
                let h = Math.floor(sec / 3600);
                let m = Math.floor((sec % 3600) / 60);
                let s = sec % 60;
                return `${h > 0 ? h + ':' : ''}${m < 10 ? '0' : ''}${m}:${s < 10 ? '0' : ''}${s}`;
            }
        }" class="space-y-4" wire:key="cbt-exam-wrapper">
            
            <!-- Header Status Ujian & Timer -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">{{ $activeExam->title }}</h2>
                    <p class="text-xs text-gray-500">Soal ke-{{ $currentIndex + 1 }} dari {{ count($questions) }} soal</p>
                </div>

                <div class="px-4 py-2 bg-red-50 text-red-600 rounded-xl font-mono font-bold text-lg border border-red-200 flex items-center gap-2">
                    <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span x-text="formatTime(secondsLeft)"></span>
                </div>
            </div>

            <!-- Grid Utama: Soal vs Navigasi Nomor -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                
                <!-- Lembar Soal & Pilihan Jawaban -->
                <div class="lg:col-span-3 bg-white p-6 rounded-2xl shadow-sm border flex flex-col justify-between min-h-[450px]">
                    @php $currentQ = $questions[$currentIndex] ?? null; @endphp

                    @if($currentQ)
                        <div>
                            <div class="flex justify-between items-center border-b pb-3 mb-4">
                                <span class="text-xs font-semibold px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-lg">
                                    Pertanyaan No. {{ $currentIndex + 1 }}
                                </span>
                                <span class="text-xs text-gray-400">Bobot Soal: {{ $currentQ['score_weight'] ?? 1 }} Poin</span>
                            </div>

                            <!-- Pertanyaan -->
                            <div class="prose max-w-none text-gray-800 text-base mb-6">
                                {!! is_array($currentQ['question_text']) ? json_encode($currentQ['question_text']) : $currentQ['question_text'] !!}
                            </div>

                            <!-- Opsi Jawaban / Input Essay -->
                            @php
                                $type = $currentQ['type'] ?? ($currentQ['question_type'] ?? (count($currentQ['options'] ?? []) > 0 ? 'multiple_choice' : 'essay'));
                            @endphp

                            @if($type === 'essay' || empty($currentQ['options']))
                                <div class="space-y-2">
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider">Jawaban Anda:</label>
                                    <textarea 
                                        wire:model.lazy="essayAnswers.{{ $currentQ['id'] }}"
                                        wire:change="saveEssayAnswer({{ $currentQ['id'] }})"
                                        rows="5"
                                        placeholder="Ketikkan jawaban Anda secara lengkap di sini..."
                                        class="w-full p-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-gray-800 transition shadow-sm"
                                    ></textarea>
                                    <p class="text-xs text-gray-400 italic">* Jawaban tersimpan otomatis saat Anda berpindah kolom atau menekan tombol navigasi.</p>
                                </div>
                            @else
                                <div class="space-y-3">
                                    @php
                                        $letters = ['A', 'B', 'C', 'D', 'E'];
                                    @endphp

                                    @foreach($currentQ['options'] as $index => $option)
                                        @php
                                            $label = $letters[$index] ?? ($index + 1);
                                            $optionId = is_array($option) ? ($option['id'] ?? $index) : $option;
                                            $optionText = is_array($option) ? ($option['option_text'] ?? '') : $option;
                                            $isSelected = ($userAnswers[$currentQ['id']] ?? null) == $optionId;
                                        @endphp

                                        <button 
                                            wire:click="saveAnswer({{ $currentQ['id'] }}, '{{ $optionId }}')" 
                                            type="button"
                                            class="w-full text-left p-3.5 rounded-xl border transition-all flex items-center gap-3 
                                            {{ $isSelected ? 'border-indigo-600 bg-indigo-50 text-indigo-900 font-semibold ring-1 ring-indigo-500' : 'border-gray-200 hover:bg-gray-50 text-gray-700' }}">
                                            
                                            <span class="w-7 h-7 flex items-center justify-center rounded-full border text-xs flex-shrink-0 {{ $isSelected ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-gray-100 text-gray-600 border-gray-300' }}">
                                                {{ $label }}
                                            </span>
                                            <span class="text-sm flex-1">{!! $optionText !!}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Tombol Navigasi Soal -->
                    <div class="mt-8 pt-4 border-t flex justify-between items-center">
                        <button 
                            wire:click="goToPrevious" 
                            @disabled($currentIndex === 0) 
                            type="button"
                            class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition"
                        >
                            ← Sebelumnya
                        </button>

                        @if($currentIndex === count($questions) - 1)
                            <button 
                                wire:click="submitExam" 
                                wire:confirm="Apakah Anda yakin ingin menyelesaikan dan mengumpulkan ujian ini?" 
                                type="button"
                                class="px-5 py-2 bg-green-600 text-white text-sm font-bold rounded-xl hover:bg-green-700 transition shadow-sm"
                            >
                                Kumpulkan & Selesai Ujian
                            </button>
                        @else
                            <button 
                                wire:click="goToNext" 
                                type="button"
                                class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-xl hover:bg-indigo-700 transition"
                            >
                                Selanjutnya →
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Panel Kisi-Kisi Nomor Soal -->
                <div class="bg-white p-4 rounded-2xl shadow-sm border h-fit">
                    <h3 class="text-xs font-bold text-gray-600 mb-3 uppercase tracking-wider">Navigasi Nomor Soal</h3>
                    <div class="grid grid-cols-5 gap-2">
                        @foreach($questions as $index => $q)
                            @php
                                $isAnswered = isset($userAnswers[$q['id']]);
                                $isCurrent = $currentIndex === $index;
                            @endphp
                            <button 
                                wire:click="jumpToQuestion({{ $index }})" 
                                type="button"
                                class="h-9 text-xs font-bold rounded-lg border transition-all
                                {{ $isCurrent ? 'ring-2 ring-indigo-500 ring-offset-1 border-indigo-500' : '' }}
                                {{ $isAnswered ? 'bg-green-600 text-white border-green-600' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100' }}">
                                {{ $index + 1 }}
                            </button>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

    {{-- DASHBOARD TABS --}}
    @else
        {{-- NAVIGASI TAB + TOMBOL KELUAR --}}
        <div class="flex items-center justify-between border-b border-gray-200 pb-2 gap-4" wire:key="student-tabs-nav">
            <div class="flex items-center gap-2 overflow-x-auto">
                <button wire:click="$set('activeTab', 'dashboard')" 
                    class="px-4 py-2 text-sm font-semibold rounded-lg transition-all whitespace-nowrap {{ $activeTab === 'dashboard' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                    Dashboard
                </button>
                <button wire:click="$set('activeTab', 'materi')" 
                    class="px-4 py-2 text-sm font-semibold rounded-lg transition-all whitespace-nowrap {{ $activeTab === 'materi' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                    Materi
                </button>
                <button wire:click="$set('activeTab', 'ujian')" 
                    class="px-4 py-2 text-sm font-semibold rounded-lg transition-all whitespace-nowrap {{ $activeTab === 'ujian' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                    Ujian CBT
                </button>
                <button wire:click="$set('activeTab', 'jadwal')" 
                    class="px-4 py-2 text-sm font-semibold rounded-lg transition-all whitespace-nowrap {{ $activeTab === 'jadwal' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                    Jadwal & Tugas
                </button>
            </div>

            <!-- Tombol Keluar (Logout) -->
            <form action="{{ route('student.logout') }}" method="POST" class="flex-shrink-0">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-bold rounded-xl border border-rose-200 transition shadow-sm cursor-pointer whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Keluar</span>
                </button>
            </form>
        </div>

        {{-- AREA KONTEN TABS --}}
        <div class="mt-4">

            {{-- TAB 1: DASHBOARD --}}
            @if($activeTab === 'dashboard')
                <div class="space-y-6" wire:key="tab-content-dashboard">
                    
                    {{-- BIODATA SISWA --}}
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-center md:items-start gap-6">
                        <div class="relative flex-shrink-0">
                            <img 
                                src="{{ !empty($student->photo) ? asset('storage/' . $student->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($student->name ?? 'Siswa') . '&background=4f46e5&color=fff&size=128' }}" 
                                alt="Foto {{ $student->name ?? 'Siswa' }}" 
                                class="w-24 h-24 rounded-full object-cover border-4 border-indigo-50 shadow-md"
                            >
                            <span class="absolute bottom-1 right-1 w-4 h-4 bg-emerald-500 border-2 border-white rounded-full"></span>
                        </div>

                        <div class="flex-1 text-center md:text-left space-y-3 w-full">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-gray-100 pb-3">
                                <div>
                                    <h2 class="text-xl font-bold text-gray-800">{{ $student->name ?? $user->name ?? 'Nama Siswa' }}</h2>
                                    <p class="text-xs text-gray-500 font-medium mt-0.5">NISN: <span class="text-gray-700 font-semibold">{{ $student->nisn ?? '-' }}</span> | NIS: <span class="text-gray-700 font-semibold">{{ $student->nis ?? '-' }}</span></p>
                                </div>
                                
                                <div>
                                    <span class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-xl">
                                        Kelas {{ $student->classroom->name ?? 'Aktif' }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs pt-1">
                                <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                                    <span class="text-gray-400 block mb-0.5">Jenis Kelamin</span>
                                    <span class="font-bold text-gray-700">{{ isset($student->gender) ? ($student->gender === 'L' ? 'Laki-laki' : 'Perempuan') : '-' }}</span>
                                </div>
                                <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                                    <span class="text-gray-400 block mb-0.5">Tempat, Tgl Lahir</span>
                                    <span class="font-bold text-gray-700 truncate block">{{ $student->birth_place ?? '-' }}, {{ isset($student->birth_date) ? \Carbon\Carbon::parse($student->birth_date)->format('d M Y') : '-' }}</span>
                                </div>
                                <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                                    <span class="text-gray-400 block mb-0.5">Agama</span>
                                    <span class="font-bold text-gray-700">{{ $student->religion ?? '-' }}</span>
                                </div>
                                <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                                    <span class="text-gray-400 block mb-0.5">No. HP / WA</span>
                                    <span class="font-bold text-gray-700">{{ $student->phone ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- RINGKASAN METRIK & REKAP ABSENSI --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gradient-to-br from-indigo-600 to-indigo-700 p-6 rounded-2xl text-white shadow-sm flex flex-col justify-between">
                            <div>
                                <span class="text-xs uppercase tracking-wider text-indigo-200 font-semibold block">Total Tabungan</span>
                                <h3 class="text-3xl font-extrabold mt-1">Rp {{ number_format($totalSavings ?? 0, 0, ',', '.') }}</h3>
                            </div>
                            <div class="mt-4 pt-3 border-t border-indigo-500/50 flex justify-between items-center text-xs text-indigo-100">
                                <span>Transaksi Terakhir</span>
                                <span class="font-medium">{{ isset($recentSavings) && $recentSavings->first()?->created_at ? $recentSavings->first()->created_at->format('d M Y') : '-' }}</span>
                            </div>
                        </div>

                        <div class="md:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
                            <h3 class="text-sm font-bold text-gray-800 flex items-center justify-between">
                                <span>📊 Rekap Kehadiran Siswa</span>
                                <span class="text-xs font-normal text-gray-400">Semester Ini</span>
                            </h3>

                            <div class="grid grid-cols-4 gap-3 text-center">
                                <div class="bg-emerald-50 border border-emerald-100 p-3.5 rounded-xl">
                                    <span class="text-emerald-600 text-xs font-bold block">Hadir</span>
                                    <span class="text-2xl font-extrabold text-emerald-700 mt-1 block">{{ $attendances['hadir'] ?? 0 }}</span>
                                </div>
                                <div class="bg-amber-50 border border-amber-100 p-3.5 rounded-xl">
                                    <span class="text-amber-600 text-xs font-bold block">Sakit</span>
                                    <span class="text-2xl font-extrabold text-amber-700 mt-1 block">{{ $attendances['sakit'] ?? 0 }}</span>
                                </div>
                                <div class="bg-blue-50 border border-blue-100 p-3.5 rounded-xl">
                                    <span class="text-blue-600 text-xs font-bold block">Izin</span>
                                    <span class="text-2xl font-extrabold text-blue-700 mt-1 block">{{ $attendances['izin'] ?? 0 }}</span>
                                </div>
                                <div class="bg-rose-50 border border-rose-100 p-3.5 rounded-xl">
                                    <span class="text-rose-600 text-xs font-bold block">Alfa</span>
                                    <span class="text-2xl font-extrabold text-rose-700 mt-1 block">{{ $attendances['alfa'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            

            {{-- TAB 2: MATERI (Mendukung YouTube & Tampil PDF Langsung di Web) --}}
            {{-- TAB 2: MATERI --}}
            @elseif($activeTab === 'materi')
                <div class="space-y-6" wire:key="tab-content-materi">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-gray-100 pb-4 gap-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-800">📚 Materi Pembelajaran & Media Belajar</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Pelajari ringkasan, dokumen PDF, foto materi, atau tonton video pembelajaran.</p>
                            </div>

                            <!-- Filter Mata Pelajaran Cepat -->
                            <div class="w-full sm:w-64">
                                <select wire:model.live="selectedSubject" class="w-full text-xs border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Semua Mata Pelajaran</option>
                                    @foreach($allSubjects as $subj)
                                        <option value="{{ $subj->id }}">{{ $subj->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Daftar Grid Materi -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                            @forelse($materials ?? [] as $material)
                                @php
                                    $videoLink = $material->external_url ?? $material->video_url ?? '';
                                    $isYoutube = $material->type === 'youtube' || (str_contains($videoLink, 'youtube.com') || str_contains($videoLink, 'youtu.be'));
                                    
                                    $youtubeEmbedUrl = '';
                                    if ($isYoutube && !empty($videoLink)) {
                                        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $videoLink, $match)) {
                                            $youtubeEmbedUrl = 'https://www.youtube.com/embed/' . $match[1];
                                        }
                                    }
                                @endphp

                                <div class="bg-gray-50 p-5 rounded-2xl border border-gray-200 flex flex-col justify-between space-y-4 hover:shadow-md transition">
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-md uppercase 
                                                {{ $material->type === 'pdf' ? 'bg-red-50 text-red-600 border border-red-100' : ($material->type === 'image' ? 'bg-amber-50 text-amber-600 border border-amber-100' : ($isYoutube ? 'bg-rose-50 text-rose-600 border border-rose-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100')) }}">
                                                {{ $material->type === 'pdf' ? 'File PDF' : ($material->type === 'image' ? 'Gambar / Foto' : ($isYoutube ? 'Video YouTube' : 'Ringkasan Teks')) }}
                                            </span>
                                            <span class="text-xs text-gray-400 font-medium">{{ $material->class_level ?? '' }}</span>
                                        </div>

                                        <h4 class="font-bold text-base text-gray-800">{{ $material->title }}</h4>
                                        <p class="text-xs text-gray-500 font-medium">Mapel: <span class="text-gray-700 font-semibold">{{ $material->subject }}</span></p>

                                        {{-- 1. YOUTUBE VIDEO --}}
                                        @if($isYoutube && $youtubeEmbedUrl)
                                            <div class="relative w-full aspect-video rounded-xl overflow-hidden shadow-inner bg-black">
                                                <iframe 
                                                    src="{{ $youtubeEmbedUrl }}" 
                                                    title="{{ $material->title }}" 
                                                    class="absolute top-0 left-0 w-full h-full border-0" 
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                                    allowfullscreen>
                                                </iframe>
                                            </div>

                                        {{-- 2. FILE PDF VIEWER --}}
                                        @elseif($material->type === 'pdf' && !empty($material->file_path))
                                            <div class="space-y-2">
                                                <div class="w-full h-72 bg-gray-900 rounded-xl overflow-hidden border border-gray-300 shadow-inner">
                                                    <iframe 
                                                        src="{{ asset('storage/' . $material->file_path) }}" 
                                                        class="w-full h-full border-0" 
                                                        title="{{ $material->title }}">
                                                    </iframe>
                                                </div>
                                                <div class="flex justify-end">
                                                    <a href="{{ asset('storage/' . $material->file_path) }}" target="_blank" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-1.5">
                                                        <span>Buka / Download PDF Penuh</span>
                                                    </a>
                                                </div>
                                            </div>

                                        {{-- 3. GAMBAR / FOTO MATERI --}}
                                        @elseif($material->type === 'image' && !empty($material->file_path))
                                            <div class="space-y-2">
                                                <div class="rounded-xl overflow-hidden border border-gray-200 bg-white shadow-sm flex justify-center p-2">
                                                    <a href="{{ asset('storage/' . $material->file_path) }}" target="_blank" title="Klik untuk memperbesar gambar">
                                                        <img 
                                                            src="{{ asset('storage/' . $material->file_path) }}" 
                                                            alt="{{ $material->title }}" 
                                                            class="max-h-64 w-auto object-contain hover:scale-105 transition duration-300 cursor-pointer rounded-lg"
                                                        />
                                                    </a>
                                                </div>
                                                <p class="text-[10px] text-gray-400 text-center italic">* Klik gambar untuk memperbesar</p>
                                            </div>

                                        {{-- 4. RINGKASAN TEKS --}}
                                        @elseif($material->type === 'summary' && !empty($material->content))
                                            <div class="text-xs text-gray-600 bg-white p-4 rounded-xl border border-gray-200 shadow-sm leading-relaxed max-h-48 overflow-y-auto">
                                                {!! $material->content !!}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="pt-3 border-t border-gray-200 flex items-center justify-between">
                                        <span class="text-[10px] text-gray-400">Diperbarui: {{ $material->updated_at?->diffForHumans() }}</span>

                                        @if($isYoutube)
                                            <a href="{{ $videoLink }}" target="_blank" class="text-[11px] text-rose-600 hover:underline font-semibold flex items-center gap-1">
                                                <span>Buka di YouTube ↗</span>
                                            </a>
                                        @elseif($material->type === 'link' && $videoLink)
                                            <a href="{{ $videoLink }}" target="_blank" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-1">
                                                <span>Buka Link ↗</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-full p-8 bg-gray-50 rounded-xl border border-dashed text-center text-gray-400 italic text-sm">
                                    Belum ada materi pembelajaran yang tersedia untuk mata pelajaran ini.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            
            {{-- TAB 3: UJIAN CBT --}}
            @elseif($activeTab === 'ujian')
                <div class="p-6 bg-white rounded-2xl shadow-sm border border-gray-100 space-y-4" wire:key="tab-content-ujian">
                    <h3 class="text-lg font-bold text-gray-800">Daftar Ujian CBT</h3>
                    <div class="space-y-3">
                        @forelse($cbtExams as $exam)
                            @php
                                $session = $exam->sessions->where('student_id', $student->id ?? auth()->id())->first();
                            @endphp

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-200 gap-3">
                                <div>
                                    <h4 class="font-bold text-sm text-gray-800">{{ $exam->title }}</h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-xs text-gray-500">{{ $exam->subject->name ?? 'Mata Pelajaran' }}</span>
                                        <span class="text-xs text-gray-400">•</span>
                                        <span class="text-xs text-indigo-600 font-semibold">{{ $exam->duration ?? 60 }} Menit</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 self-end sm:self-auto">
                                    @if(!$session)
                                        <button wire:click="startExam({{ $exam->id }})" 
                                                wire:loading.attr="disabled"
                                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span>Mulai Ujian</span>
                                        </button>

                                    @elseif($session->status === 'ongoing')
                                        <button wire:click="startExam({{ $exam->id }})" 
                                                wire:loading.attr="disabled"
                                                class="px-4 py-2 bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-1.5 animate-pulse">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span>Lanjutkan Ujian</span>
                                        </button>

                                    @else
                                        <div class="flex items-center gap-2">
                                            <span class="px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold rounded-xl flex items-center gap-1">
                                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <span>Selesai</span>
                                            </span>
                                            @if(isset($session->total_score))
                                                <span class="px-3 py-1.5 bg-gray-100 text-gray-800 text-xs font-extrabold rounded-xl border border-gray-200">
                                                    Nilai: {{ $session->total_score }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-6 bg-gray-50 rounded-xl border border-dashed text-center text-gray-400 italic text-sm">
                                Tidak ada ujian aktif saat ini.
                            </div>
                        @endforelse
                    </div>
                </div>
            

            {{-- TAB 4: JADWAL PELAJARAN & TUGAS KELAS --}}
            @elseif($activeTab === 'jadwal')
                <div class="space-y-6" wire:key="tab-content-jadwal">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        
                        <!-- KOLOM KIRI & TENGAH: JADWAL PELAJARAN PER HARI (SENIN S.D. JUMAT) -->
                        <div class="lg:col-span-2 space-y-6">
                            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
                                <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                                    <div>
                                        <h3 class="text-base font-bold text-gray-800">📅 Jadwal Pelajaran Mingguan</h3>
                                        <p class="text-xs text-gray-500">Alokasi waktu KBM (1 JP = 35 Menit) beserta waktu istirahat.</p>
                                    </div>
                                </div>

                                @foreach($daysOrder as $day)
                                    <div class="space-y-2 pt-2">
                                        <div class="bg-indigo-50 text-indigo-900 px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider flex items-center justify-between">
                                            <span> Hari: {{ $day }}</span>
                                            <span class="text-[10px] font-normal text-indigo-700">Senin s.d. Jumat</span>
                                        </div>

                                        <div class="overflow-x-auto border border-gray-200 rounded-xl">
                                            <table class="w-full text-left text-xs border-collapse">
                                                <thead>
                                                    <tr class="bg-gray-50 text-gray-600 border-b border-gray-200">
                                                        <th class="p-2.5 text-center w-16">JP</th>
                                                        <th class="p-2.5 w-32">Waktu (WIB)</th>
                                                        <th class="p-2.5">Mata Pelajaran</th>
                                                        <th class="p-2.5 text-center w-24">Ruangan</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100">
                                                    @foreach($masterTimes as $slot)
                                                        @if($slot['type'] === 'break')
                                                            <!-- BARIS ISTIRAHAT -->
                                                            <tr class="bg-gray-50 italic text-gray-500 font-medium">
                                                                <td class="p-2 text-center">-</td>
                                                                <td class="p-2">{{ $slot['time'] }}</td>
                                                                <td colspan="2" class="p-2 text-center text-amber-700 font-semibold">
                                                                    ☕ {{ $slot['label'] }}
                                                                </td>
                                                            </tr>
                                                        @else
                                                            <!-- BARIS JAM PELAJARAN -->
                                                            @php
                                                                $matched = $rawSchedules->first(function($item) use ($day, $slot) {
                                                                    return $item->day_name === $day && 
                                                                        substr($item->start_time, 0, 5) <= substr($slot['time'], 0, 5) && 
                                                                        substr($item->end_time, 0, 5) >= substr($slot['time'], 8, 5);
                                                                });
                                                            @endphp
                                                            <tr class="hover:bg-gray-50/50">
                                                                <td class="p-2.5 text-center font-bold text-gray-700">Ke-{{ $slot['period'] }}</td>
                                                                <td class="p-2.5 text-gray-600 font-medium">{{ $slot['time'] }}</td>
                                                                <td class="p-2.5 font-semibold text-gray-800">
                                                                    {{ $matched->subject->name ?? '<span class="text-gray-300 font-normal">- Kosong -</span>' }}
                                                                </td>
                                                                <td class="p-2.5 text-center">
                                                                    @if($matched && $matched->room_name)
                                                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-[10px] font-bold">{{ $matched->room_name }}</span>
                                                                    @else
                                                                        <span class="text-gray-300">-</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- KOLOM KANAN: TUGAS KELAS / DEADLINE -->
                        <div class="space-y-6">
                            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4 sticky top-6">
                                <div class="border-b border-gray-100 pb-3">
                                    <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                                        📌 Tugas Kelas & Tenggat
                                    </h3>
                                    <p class="text-xs text-gray-500">Daftar tugas aktif dari guru.</p>
                                </div>

                                @forelse($assignments as $assignment)
                                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-2 hover:shadow-sm transition">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">
                                                {{ $assignment->subject->name ?? 'Umum' }}
                                            </span>
                                            <span class="text-[10px] font-bold text-red-600">
                                                {{ \Carbon\Carbon::parse($assignment->due_date)->format('d M Y, H:i') }}
                                            </span>
                                        </div>

                                        <h4 class="font-bold text-sm text-gray-800">{{ $assignment->title }}</h4>
                                        <div class="text-xs text-gray-600 leading-relaxed line-clamp-3">
                                            {!! $assignment->description !!}
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-8 bg-gray-50 rounded-xl border border-dashed text-center text-gray-400 italic text-xs">
                                        Tidak ada tugas aktif yang perlu dikerjakan saat ini. 🎉
                                    </div>
                                @endforelse
                            </div>
                        </div>

                    </div>
                </div>
            @endif

        </div>
    @endif

</div>