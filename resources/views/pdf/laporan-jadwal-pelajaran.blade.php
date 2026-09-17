@extends('pdf.layouts.master')

@section('title', 'Laporan Jadwal Pelajaran - Kelas ' . $class->name)

@section('doc-title', 'JADWAL PELAJARAN & ALOKASI WAKTU KELAS ' . strtoupper($class->name))

@section('styles')
<style>
    table.data-table {
        width: 100% !important;
        border-collapse: collapse !important;
        margin-top: 5px !important;
    }

    table.data-table th, 
    table.data-table td {
        border: 1px solid #000000 !important;
        padding: 5px 6px !important;
        font-size: 8pt !important;
    }

    table.data-table th {
        background-color: #f2f2f2 !important;
        font-weight: bold !important;
        text-align: center !important;
        text-transform: uppercase;
    }

    .break-row {
        background-color: #f3f4f6 !important;
        font-style: italic;
        color: #4b5563;
        text-align: center;
        font-weight: bold;
    }

    .signature-section {
        width: 100%;
        margin-top: 20px;
        page-break-inside: avoid;
    }

    .signature-box {
        width: 45%;
        font-size: 8.5pt;
        float: right;
        text-align: center;
    }
</style>
@endsection

@section('meta-data')
<tr>
    <td style="width: 15%;"><strong>Kelas</strong></td>
    <td style="width: 35%;">: {{ $class?->name ?? '-' }}</td>
    <td style="width: 15%;"><strong>Wali Kelas</strong></td>
    <td style="width: 35%;">: {{ $teacher?->name ?? '-' }}</td>
</tr>
<tr>
    <td><strong>Tahun Ajaran</strong></td>
    <td>: {{ date('Y') }}/{{ date('Y') + 1 }}</td>
    <td><strong>Tanggal Cetak</strong></td>
    <td>: {{ $titiMangsa }}</td>
</tr>
@endsection

@section('content')

@foreach($daysOrder as $day)
    <div style="font-size: 9pt; font-weight: bold; margin-top: 10px; margin-bottom: 3px; background-color: #e5e7eb; padding: 4px 6px; text-transform: uppercase;">
        Hari: {{ $day }}
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">JP Ke-</th>
                <th style="width: 25%;">Waktu (WIB)</th>
                <th style="width: 45%;">Mata Pelajaran</th>
                <th style="width: 20%;">Ruangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($masterTimes as $slot)
                @if($slot['type'] === 'break')
                    {{-- BARIS ISTIRAHAT --}}
                    <tr class="break-row">
                        <td class="text-center">-</td>
                        <td class="text-center">{{ $slot['time'] }}</td>
                        <td colspan="2" class="text-center">☕ {{ $slot['label'] }}</td>
                    </tr>
                @else
                    {{-- BARIS JAM PELAJARAN (1 JP) --}}
                    @php
                        $matchedSchedule = $schedules->first(function($item) use ($day, $slot) {
                            return $item->day_name === $day && 
                                   substr($item->start_time, 0, 5) <= substr($slot['time'], 0, 5) && 
                                   substr($item->end_time, 0, 5) >= substr($slot['time'], 8, 5);
                        });
                    @endphp
                    <tr>
                        <td class="text-center font-bold">JP ke-{{ $slot['period'] }}</td>
                        <td class="text-center">{{ $slot['time'] }}</td>
                        <td>
                            {{ $matchedSchedule->subject->name ?? '-' }}
                        </td>
                        <td class="text-center">
                            {{ $matchedSchedule->room_name ?? '-' }}
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
@endforeach

{{-- TITI MANGSA DAN TANDA TANGAN --}}


@endsection