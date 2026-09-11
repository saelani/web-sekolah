<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Header Controls (Dropdown & Navigasi Bulan) --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
            <div class="flex items-center gap-2">
                <x-filament::button wire:click="previousMonth" color="gray" icon="heroicon-m-chevron-left" size="sm" />
                <x-filament::button wire:click="goToToday" color="gray" size="sm">Hari Ini</x-filament::button>
                <x-filament::button wire:click="nextMonth" color="gray" icon="heroicon-m-chevron-right" size="sm" />
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-white ml-2">
                    {{ $monthName }}
                </h2>
            </div>

            <div class="w-full md:w-64">
                <select wire:model.live="selectedClassId" class="w-full text-sm border-gray-300 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($classes as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Wrapper Grid Kalender (Scrollable di HP) --}}
        <div class="w-full overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="min-w-[700px]"> {{-- Menjaga lebar minimum di HP agar grid tidak gepeng --}}
                
                {{-- Header Hari --}}
                <div class="grid grid-cols-7 bg-sky-900 text-white text-center text-xs md:text-sm font-semibold py-2">
                    <div>Senin</div>
                    <div>Selasa</div>
                    <div>Rabu</div>
                    <div>Kamis</div>
                    <div>Jumat</div>
                    <div class="text-rose-300">Sabtu</div>
                    <div class="text-rose-300">Minggu</div>
                </div>

                {{-- Body Grid Tanggal --}}
                <div class="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700">
                    @foreach($days as $day)
                        <div class="min-h-[90px] md:min-h-[110px] bg-white dark:bg-gray-800 p-1 md:p-2 transition-colors
                            {{ !$day['isCurrentMonth'] ? 'bg-gray-50 text-gray-400 opacity-60' : '' }}
                            {{ $day['isToday'] ? 'ring-2 ring-sky-500 bg-sky-50/30' : '' }}">
                            
                            {{-- Angka Tanggal --}}
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-semibold {{ $day['isToday'] ? 'bg-sky-600 text-white px-1.5 py-0.5 rounded-full' : 'text-gray-700 dark:text-gray-300' }}">
                                    {{ $day['dayNumber'] }}
                                </span>
                            </div>

                            {{-- List Event --}}
                            <div class="space-y-1 overflow-y-auto max-h-[70px]">
                                @foreach($day['events'] as $event)
                                    <div class="text-[10px] md:text-xs p-1 rounded bg-emerald-100 text-emerald-800 font-medium truncate" 
                                         title="{{ $event->title ?? $event->learningObjective?->title }}">
                                        [{{ $event->type ?? 'KBM' }}] {{ $event->title ?? $event->subject?->name }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>