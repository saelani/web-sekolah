<x-filament-panels::page>
    <form wire:submit.prevent="save">
        {{ $this->filterForm }}

        @if(!empty($scoresData['scores'] ?? []))
            <div class="mt-6">
                {{ $this->scoresForm }}
            </div>

            <div class="mt-6 flex justify-end">
                <x-filament::button type="submit" size="lg" icon="heroicon-o-check-circle">
                    Simpan Semua Nilai P5
                </x-filament::button>
            </div>
        @endif
    </form>
</x-filament-panels::page>