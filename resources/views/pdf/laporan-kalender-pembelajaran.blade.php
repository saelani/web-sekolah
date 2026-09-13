@extends('pdf.layouts.master')

@section('orientation', 'landscape')
@section('title', 'Pemetaan Pembelajaran Semester - ' . $class->name)
@section('doc-title', 'PEMETAAN & ALOKASI WAKTU PEMBELAJARAN SEMESTER ' . $semester)

@section('styles')
<style>
    .badge-event {
        display: block;
        padding: 3px 4px;
        margin-bottom: 3px;
        border-radius: 3px;
        font-size: 7.5pt;
        line-height: 1.1;
        text-align: left;
    }
    .badge-kbm   { background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-slm   { background-color: #fef9c3; color: #a16207; border: 1px solid #fef08a; }
    .badge-sls   { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .badge-event { background-color: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .badge-libur { background-color: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; }
    
    .legend-box {
        margin-bottom: 8px;
        font-size: 8pt;
    }
    .legend-item {
        display: inline-block;
        padding: 2px 6px;
        margin-right: 8px;
        border-radius: 3px;
        font-weight: bold;
    }
</style>
@endsection

@section('meta-data')
<tr>
    <td style="width: 12%;"><strong>Kelas</strong></td>
    <td style="width: 38%;">: {{ $class->name }}</td>
    <td style="width: 12%;"><strong>Semester / Thn</strong></td>
    <td style="width: 38%;">: Semester {{ $semester }} (Tahun {{ $year }})</td>
</tr>
<tr>
    <td><strong>Mata Pelajaran</strong></td>
    <td>: {{ $subject->name ?? 'Semua Mata Pelajaran' }}</td>
    <td><strong>Wali Kelas</strong></td>
    <td>: {{ $teacher->name ?? '-' }}</td>
</tr>
@endsection

@section('content')

{{-- KETERANGAN WARNA (LEGEND) --}}
<div class="legend-box">
    <strong>Keterangan Warna:</strong>
    <span class="legend-item badge-kbm">KBM Harian</span>
    <span class="legend-item badge-slm">SLM (Sumatif Bab)</span>
    <span class="legend-item badge-sls">SLS (Sumatif Semester)</span>
    <span class="legend-item badge-event">Event / P5</span>
    <span class="legend-item badge-libur">Libur</span>
</div>

<table class="table-data">
    <thead>
        <tr>
            <th style="width: 12%;">Bulan</th>
            <th style="width: 17.6%;">Minggu Ke-1</th>
            <th style="width: 17.6%;">Minggu Ke-2</th>
            <th style="width: 17.6%;">Minggu Ke-3</th>
            <th style="width: 17.6%;">Minggu Ke-4</th>
            <th style="width: 17.6%;">Minggu Ke-5</th>
        </tr>
    </thead>
    <tbody>
        @foreach($monthlyMatrix as $mNum => $data)
        <tr>
            <td class="text-center font-bold" style="background-color: #f9fafb; vertical-align: middle;">
                {{ $data['name'] }}
            </td>

            @for($w = 1; $w <= 5; $w++)
            <td style="vertical-align: top; height: 55px; padding: 3px;">
                @forelse($data['weeks'][$w] as $item)
                    @php
                        $badgeClass = match($item->activity_type) {
                            'kbm'   => 'badge-kbm',
                            'slm'   => 'badge-slm',
                            'sls'   => 'badge-sls',
                            'event' => 'badge-event',
                            default => 'badge-libur',
                        };
                        $tgl = \Carbon\Carbon::parse($item->start_date)->format('d/m');
                    @endphp

                    <div class="badge-event {{ $badgeClass }}">
                        <strong>[{{ $tgl }}]</strong> 
                        @if(!$subject) <em>({{ $item->subject->name ?? 'Umum' }})</em> @endif
                        <br>
                        {{ $item->title }}
                        @if($item->learningObjective)
                            <br><small style="font-size: 6.8pt; color: #4b5563;">TP: {{ $item->learningObjective->code ?? '' }}</small>
                        @endif
                    </div>
                @empty
                    {{-- Kolom kosong --}}
                @endforelse
            </td>
            @endfor
        </tr>
        @endforeach
    </tbody>
</table>
@endsection