<?php
namespace App\Exports;

use App\Filament\Resources\LearningObjectiveResource;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LearningObjectivesExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        // Menggunakan query yang sudah terfilter oleh Trait HasRoleScope
        return LearningObjectiveResource::getEloquentQuery()->with('subject');
    }

    public function headings(): array
    {
        return [
            'Mata Pelajaran',
            'Fase',
            'Kelas',
            'Semester',
            'Kode TP',
            'Deskripsi TP',
        ];
    }

    public function map($row): array
    {
        return [
            $row->subject?->name ?? '-',
            $row->phase,
            $row->level,
            $row->semester,
            $row->code,
            $row->description,
        ];
    }
}