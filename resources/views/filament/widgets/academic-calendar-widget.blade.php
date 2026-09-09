<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Header Navigation & Filter --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div class="flex items-center space-x-2">
                <x-filament::button wire:click="previousMonth" icon="heroicon-m-chevron-left" color="gray" size="sm" />
                <x-filament::button wire:click="goToToday" color="gray" size="sm">Hari Ini</x-filament::button>
                <x-filament::button wire:click="nextMonth" icon="heroicon-m-chevron-right" color="gray" size="sm" />
                <h2 class="text-lg font-bold text-gray-800 dark:text-white capitalize">
                    {{ $monthName }}
                </h2>
            </div>

            <div class="w-full md:w-64">
                <select wire:model.live="selectedClassId" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($classes as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Grid Header Hari --}}
        <div class="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700 border border-gray-200 dark:border-gray-700 rounded-t-lg text-center font-semibold text-xs text-gray-600 dark:text-gray-300 py-2">
            <div>Senin</div>
            <div>Selasa</div>
            <div>Rabu</div>
            <div>Kamis</div>
            <div>Jumat</div>
            <div class="text-red-500">Sabtu</div>
            <div class="text-red-500">Minggu</div>
        </div>

        {{-- Grid Tanggal --}}
        <div class="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700 border-x border-b border-gray-200 dark:border-gray-700 rounded-b-lg overflow-hidden">
            @foreach($days as $day)
                <div class="min-h-[100px] bg-white dark:bg-gray-900 p-1.5 flex flex-col justify-between transition {{ !$day['isCurrentMonth'] ? 'opacity-40 bg-gray-50 dark:bg-gray-950' : '' }}">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold px-1.5 py-0.5 rounded-full {{ $day['isToday'] ? 'bg-primary-600 text-white' : 'text-gray-700 dark:text-gray-300' }}">
                            {{ $day['date']->format('j') }}
                        </span>
                    </div>

                    {{-- List Event / Agenda --}}
                    <div class="mt-1 space-y-1 overflow-y-auto max-h-[80px]">
                        @foreach($day['events'] as $event)
                            @php
                                $colorClass = match($event->activity_type) {
                                    'kbm' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-300',
                                    'slm' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border-amber-300',
                                    'sls' => 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border-rose-300',
                                    'event' => 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300 border-sky-300',
                                    default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 border-gray-300',
                                };
                            @endphp

                            <div class="text-[10px] p-1 rounded border leading-tight truncate {{ $colorClass }}" title="{{ $event->title }} ({{ strtoupper($event->activity_type) }})">
                                <span class="font-bold">[{{ strtoupper($event->activity_type) }}]</span> {{ $event->title }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>