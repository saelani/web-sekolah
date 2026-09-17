@extends('pdf.layouts.master')

@section('title', 'Laporan Persentase Kehadiran Siswa 1 Bulan')

@section('doc-title', 'REKAPITULASI PERSENTASE KEHADIRAN SISWA')

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
        padding: 4px 5px !important;
        font-size: 8pt !important;
    }

    table.data-table th {
        background-color: #f2f2f2 !important;
        font-weight: bold !important;
        text-align: center !important;
        text-transform: uppercase;
    }

    table.data-table tfoot td {
        background-color: #fafafa !important;
        font-weight: bold !important;
    }

    /* Summary Box Styling */
    .summary-box {
        margin-top: 12px !important;
        width: 100% !important;
        page-break-inside: avoid;
    }

    .summary-title {
        font-size: 8.5pt !important;
        font-weight: bold;
        margin-bottom: 4px;
        text-transform: uppercase;
    }

    table.summary-table {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    table.summary-table td, 
    table.summary-table th {
        border: 1px solid #000000 !important;
        padding: 4px 6px !important;
        font-size: 8pt !important;
    }

    table.summary-table th {
        background-color: #eaeaea !important;
        text-align: center !important;
        font-weight: bold !important;
    }

    .bg-highlight {
        background-color: #f9f9f9 !important;
    }

    .signature-section {
        width: 100%;
        margin-top: 20px;
        page-break-inside: avoid;
    }

    .signature-box {
        width: 45%;
        font-size: 8.5pt;
    }
</style>
@endsection

@section('meta-data')
<tr>
    <td style="width: 15%;"><strong>Kelas</strong></td>
    <td style="width: 35%;">: {{ $class?->name ?? '-' }}</td>
    <td style="width: 15%;"><strong>Periode Bulan</strong></td>
    <td style="width: 35%;">: {{ $monthName }} ({{ $effectiveDays }} Hari Efektif)</td>
</tr>
<tr>
    <td><strong>Wali Kelas</strong></td>
    <td>: {{ $teacher?->name ?? '-' }}</td>
    <td><strong>Tanggal Cetak</strong></td>
    <td>: {{ $titiMangsa }}</>
    <!-- <td>: {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</td> -->
</tr>
@endsection

@section('content')
{{-- TABEL UTAMA REKAP PER SISWA --}}
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th style="width: 17%;">NISN / NIS</th>
            <th style="width: 31%;">Nama Siswa</th>
            <th style="width: 9%;">Hadir</th>
            <th style="width: 9%;">Sakit</th>
            <th style="width: 9%;">Izin</th>
            <th style="width: 9%;">Alpa</th>
            <th style="width: 12%;">% Kehadiran</th>
        </tr>
    </thead>
    <tbody>
        @forelse($summary as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $row['student']->nisn ?? $row['student']->nis ?? '-' }}</td>
                <td>{{ $row['student']->name ?? '-' }}</td>
                <td class="text-center">{{ $row['hadir'] }}</td>
                <td class="text-center">{{ $row['sakit'] }}</td>
                <td class="text-center">{{ $row['izin'] }}</td>
                <td class="text-center">{{ $row['alpa'] }}</td>
                <td class="text-center font-bold">{{ $row['percentage'] }}%</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center">Tidak ada data siswa untuk periode ini.</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" class="text-right font-bold">TOTAL KESELURUHAN:</td>
            <td class="text-center">{{ $summary->sum('hadir') }}</td>
            <td class="text-center">{{ $summary->sum('sakit') }}</td>
            <td class="text-center">{{ $summary->sum('izin') }}</td>
            <td class="text-center">{{ $summary->sum('alpa') }}</td>
            <td class="text-center font-bold">
                {{ $summary->count() > 0 ? round($summary->avg('percentage'), 1) : 0 }}%
            </td>
        </tr>
    </tfoot>
</table>

<div style="font-size: 7.5pt; font-style: italic; margin-top: 3px; margin-bottom: 6px;">
    * Catatan: Jumlah hadir dihitung dari total hari efektif ({{ $effectiveDays }} hari) dikurangi akumulasi ketidakhadiran.
</div>

{{-- TABEL RINGKASAN PERSENTASE KELAS DI BAWAH --}}
@php
    $totalStudents = $summary->count();
    $totalExpectedDays = $totalStudents * $effectiveDays;
    
    $sumHadir = $summary->sum('hadir');
    $sumSakit = $summary->sum('sakit');
    $sumIzin  = $summary->sum('izin');
    $sumAlpa  = $summary->sum('alpa');

    $rateHadir = $totalExpectedDays > 0 ? round(($sumHadir / $totalExpectedDays) * 100, 1) : 0;
    $rateSakit = $totalExpectedDays > 0 ? round(($sumSakit / $totalExpectedDays) * 100, 1) : 0;
    $rateIzin  = $totalExpectedDays > 0 ? round(($sumIzin / $totalExpectedDays) * 100, 1) : 0;
    $rateAlpa  = $totalExpectedDays > 0 ? round(($sumAlpa / $totalExpectedDays) * 100, 1) : 0;
@endphp

<div class="summary-box">
    <div class="summary-title">PERSENTASE KELAS BULAN {{ strtoupper($monthName) }}</div>
    <table class="summary-table">
        <thead>
            <tr>
                <th style="width: 25%;">Kategori</th>
                <th style="width: 20%;">Total Hari</th>
                <th style="width: 20%;">Persentase (%)</th>
                <th style="width: 35%;">Keterangan Perhitungan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Hadir (H)</strong></td>
                <td class="text-center">{{ $sumHadir }}</td>
                <td class="text-center font-bold" style="color: green;">{{ $rateHadir }}%</td>
                <td>({{ $sumHadir }} / {{ $totalExpectedDays }}) × 100%</td>
            </tr>
            <tr>
                <td><strong>Sakit (S)</strong></td>
                <td class="text-center">{{ $sumSakit }}</td>
                <td class="text-center">{{ $rateSakit }}%</td>
                <td>({{ $sumSakit }} / {{ $totalExpectedDays }}) × 100%</td>
            </tr>
            <tr>
                <td><strong>Izin (I)</strong></td>
                <td class="text-center">{{ $sumIzin }}</td>
                <td class="text-center">{{ $rateIzin }}%</td>
                <td>({{ $sumIzin }} / {{ $totalExpectedDays }}) × 100%</td>
            </tr>
            <tr>
                <td><strong>Alpa (A)</strong></td>
                <td class="text-center">{{ $sumAlpa }}</td>
                <td class="text-center" style="color: red;">{{ $rateAlpa }}%</td>
                <td>({{ $sumAlpa }} / {{ $totalExpectedDays }}) × 100%</td>
            </tr>
            <tr class="bg-highlight font-bold">
                <td><strong>RATA-RATA KELAS</strong></td>
                <td class="text-center">{{ $sumHadir }} / {{ $totalExpectedDays }}</td>
                <td class="text-center font-bold" style="font-size: 9pt;">{{ $rateHadir }}%</td>
                <td>{{ $totalStudents }} Siswa × {{ $effectiveDays }} Hari Efektif</td>
            </tr>
        </tbody>
    </table>
</div>

{{-- TITI MANGSA DAN TANDA TANGAN --}}

@endsection