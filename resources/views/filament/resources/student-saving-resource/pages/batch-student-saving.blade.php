<x-filament-panels::page>
    <form wire:submit.prevent="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex justify-end gap-x-3">
            <x-filament::button type="submit" size="lg" color="primary">
                Simpan Semua Transaksi Tabungan
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>