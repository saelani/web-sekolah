<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- NOTIFIKASI PREVIEW ADMIN --}}
    @if($isAdminPreview)
        <div class="p-3.5 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-sm flex items-center gap-2 shadow-sm">
            <span>⚠️</span>
            <span><strong>Mode Preview Admin:</strong> Anda sedang melihat tampilan dashboard siswa.</span>
        </div>
    @endif

    {{-- TAMPILAN JIKA SEDANG UJIAN --}}
    @if($isTakingExam)
        <div class="p-6 bg-white rounded-2xl shadow-sm border space-y-4" wire:key="cbt-exam-wrapper">
            <h2 class="text-xl font-bold">{{ $activeExam->title ?? 'Ujian CBT' }}</h2>
            <p class="text-sm text-gray-500">Soal {{ $currentIndex + 1 }} dari {{ count($questions) }}</p>
            {{-- Konten CBT Ujian --}}
        </div>

    {{-- DASHBOARD TABS --}}
    @else
        {{-- NAVIGASI TAB --}}
        <div class="flex border-b border-gray-200 gap-2 overflow-x-auto pb-1" wire:key="student-tabs-nav">
            <button wire:click="$set('activeTab', 'dashboard')" 
                class="px-4 py-2.5 text-sm font-semibold rounded-lg transition-all whitespace-nowrap {{ $activeTab === 'dashboard' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                Dashboard
            </button>
            <button wire:click="$set('activeTab', 'materi')" 
                class="px-4 py-2.5 text-sm font-semibold rounded-lg transition-all whitespace-nowrap {{ $activeTab === 'materi' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                Materi
            </button>
            <button wire:click="$set('activeTab', 'ujian')" 
                class="px-4 py-2.5 text-sm font-semibold rounded-lg transition-all whitespace-nowrap {{ $activeTab === 'ujian' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                Ujian CBT
            </button>
            <button wire:click="$set('activeTab', 'jadwal')" 
                class="px-4 py-2.5 text-sm font-semibold rounded-lg transition-all whitespace-nowrap {{ $activeTab === 'jadwal' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                Jadwal & Tugas
            </button>
        </div>

        {{-- AREA KONTEN TABS --}}
        <div class="mt-4">

            {{-- TAB 1: DASHBOARD --}}
            @if($activeTab === 'dashboard')
                <div class="space-y-6" wire:key="tab-content-dashboard">
                    
                    {{-- BIODATA + TOMBOL LOGOUT SISWA --}}
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
                                
                                <div class="flex items-center justify-center md:justify-end gap-2">
                                    <span class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-xl">
                                        Kelas {{ $student->classroom->name ?? 'Aktif' }}
                                    </span>

                                    {{-- TOMBOL LOGOUT DI HALAMAN SISWA --}}
                                    <button wire:click="logout" type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-bold rounded-xl border border-rose-200 transition shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        <span>Keluar</span>
                                    </button>
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
                                <h3 class="text-3xl font-extrabold mt-1">Rp {{ number_format($totalSavings, 0, ',', '.') }}</h3>
                            </div>
                            <div class="mt-4 pt-3 border-t border-indigo-500/50 flex justify-between items-center text-xs text-indigo-100">
                                <span>Transaksi Terakhir</span>
                                <span class="font-medium">{{ $recentSavings->first()?->created_at ? $recentSavings->first()->created_at->format('d M Y') : '-' }}</span>
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

            {{-- TAB 2: MATERI --}}
            @elseif($activeTab === 'materi')
                <div class="p-6 bg-white rounded-2xl shadow-sm border border-gray-100" wire:key="tab-content-materi">
                    <h3 class="text-lg font-bold text-gray-800">Materi Pembelajaran</h3>
                </div>

            {{-- TAB 3: UJIAN CBT --}}
            {{-- TAB 3: UJIAN CBT --}}
            @elseif($activeTab === 'ujian')
                <div class="p-6 bg-white rounded-2xl shadow-sm border border-gray-100 space-y-4" wire:key="tab-content-ujian">
                    <h3 class="text-lg font-bold text-gray-800">Daftar Ujian CBT</h3>
                    <div class="space-y-3">
                        @forelse($cbtExams as $exam)
                            @php
                                // Ambil sesi ujian siswa untuk exam ini (jika ada)
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
                                        {{-- SISWA BELUM MEMULAI UJIAN --}}
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
                                        {{-- UJIAN SEDANG BERLANGSUNG --}}
                                        <button wire:click="startExam({{ $exam->id }})" 
                                                wire:loading.attr="disabled"
                                                class="px-4 py-2 bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-1.5 animate-pulse">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span>Lanjutkan Ujian</span>
                                        </button>

                                    @else
                                        {{-- UJIAN SUDAH SELESAI / SUBMITTED / FINISHED --}}
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
            
                {{-- TAB 4: JADWAL & TUGAS SISWA --}}
            
                @elseif($activeTab === 'jadwal')
                <div class="space-y-6" wire:key="tab-content-jadwal">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        
                        <!-- Section Jadwal Pelajaran -->
                        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 space-y-4">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                                <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                                    📅 Jadwal Pelajaran
                                </h2>
                                <span class="text-xs bg-blue-50 text-blue-700 font-semibold px-2.5 py-1 rounded-md">
                                    Minggu Ini
                                </span>
                            </div>

                            @if(isset($schedules) && count($schedules) > 0)
                                <div class="space-y-3">
                                    @foreach($schedules as $schedule)
                                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 flex items-center justify-between">
                                            <div>
                                                <h4 class="font-bold text-sm text-gray-800">{{ $schedule->subject->name ?? 'Mata Pelajaran' }}</h4>
                                                <p class="text-xs text-gray-500 mt-0.5">
                                                    {{ $schedule->day_name ?? 'Hari' }} • {{ $schedule->start_time ?? '-' }} - {{ $schedule->end_time ?? '-' }}
                                                </p>
                                            </div>
                                            <span class="text-xs font-semibold px-2 py-1 bg-white border rounded-lg text-gray-600">
                                                Ruang {{ $schedule->room_name ?? '-' }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-6 bg-gray-50 rounded-xl border border-dashed text-center text-gray-400 italic text-sm">
                                    Belum ada jadwal pelajaran yang diset untuk kelas Anda.
                                </div>
                            @endif
                        </div>

                        <!-- Section Tugas & Deadline -->
                        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 space-y-4">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                                <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                                    📌 Tugas Kelas
                                </h2>
                                <span class="text-xs bg-amber-50 text-amber-700 font-semibold px-2.5 py-1 rounded-md">
                                    Tenggat Waktu
                                </span>
                            </div>

                            @if(isset($assignments) && count($assignments) > 0)
                                <div class="space-y-3">
                                    @foreach($assignments as $assignment)
                                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 flex items-center justify-between gap-3">
                                            <div class="min-w-0 flex-1">
                                                <span class="text-[10px] font-bold text-indigo-600 uppercase px-1.5 py-0.5 bg-indigo-50 rounded">
                                                    {{ $assignment->subject->name ?? 'Umum' }}
                                                </span>
                                                <h4 class="font-semibold text-sm text-gray-800 truncate mt-1">{{ $assignment->title }}</h4>
                                                <p class="text-xs text-gray-500 line-clamp-1">{{ $assignment->description ?? 'Tidak ada petunjuk tambahan.' }}</p>
                                            </div>
                                            <div class="text-right flex-shrink-0">
                                                <span class="text-xs font-bold text-red-600 block">
                                                    {{ isset($assignment->due_date) ? \Carbon\Carbon::parse($assignment->due_date)->format('d M, H:i') : '-' }}
                                                </span>
                                                <span class="text-[10px] text-gray-400">Tenggat Waktu</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-6 bg-gray-50 rounded-xl border border-dashed text-center text-gray-400 italic text-sm">
                                    Tidak ada tugas aktif yang perlu dikerjakan saat ini.
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            @endif

        </div>
    @endif

</div>