@extends('pdf.layouts.report-master')

@section('title', 'Laporan Rekapitulasi Absensi Siswa')

@section('doc-title', 'REKAPITULASI ABSENSI SISWA')

@section('styles')
<style>
    /* Table Styling */
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
</style>
@endsection

@section('meta-data')
<tr>
    <td style="width: 15%;"><strong>Kelas</strong></td>
    <td style="width: 35%;">: {{ $selectedClass?->name ?? 'Semua Kelas' }}</td>
    <td style="width: 15%;"><strong>Bulan / Tahun</strong></td>
    <td style="width: 35%;">: {{ $monthName }} {{ $year }} ({{ $effectiveDays }} Hari)</td>
</tr>
<tr>
    <td><strong>Tahun Ajaran</strong></td>
    <td>: {{ $academicYear?->year ?? '-' }} (Semester {{ $semester }})</td>
    <td><strong>Tanggal Cetak</strong></td>
    <td>: {{ $generatedAt }}</td>
</tr>
@endsection

@section('content')
{{-- TABEL DATA UTAMA REKAP ABSENSI SISWA --}}
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 4%;">No</th>
            <th style="width: 15%;">NISN / NIS</th>
            <th style="width: 33%;">Nama Siswa</th>
            <th style="width: 9%;">Hadir*</th>
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
                <td class="text-center">{{ $row['nisn'] }}</td>
                <td>{{ $row['student_name'] }}</td>
                <td class="text-center">{{ $row['hadir'] }}</td>
                <td class="text-center">{{ $row['sakit'] }}</td>
                <td class="text-center">{{ $row['izin'] }}</td>
                <td class="text-center">{{ $row['alpa'] }}</td>
                <td class="text-center font-bold">{{ $row['percentage'] }}%</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center">Tidak ada data siswa / absensi untuk periode ini.</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" class="text-right font-bold">TOTAL HARI KELAS:</td>
            <td class="text-center">{{ $totalHadir }}</td>
            <td class="text-center">{{ $totalSakit }}</td>
            <td class="text-center">{{ $totalIzin }}</td>
            <td class="text-center">{{ $totalAlpa }}</td>
            <td class="text-center font-bold">{{ $classAttendanceRate }}%</td>
        </tr>
    </tfoot>
</table>

<div style="font-size: 7.5pt; font-style: italic; margin-top: 3px; margin-bottom: 8px;">
    * Catatan: Siswa tanpa keterangan Sakit, Izin, dan Alpa otomatis dihitung Hadir ({{ $effectiveDays }} Hari Bulan {{ $monthName }}).
</div>

{{-- TABEL PERSENTASE & STATISTIK DI BAWAH TABEL --}}
<div class="summary-box">
    <div class="summary-title">PERSENTASE KEHADIRAN KELAS BULAN {{ strtoupper($monthName) }} {{ $year }}</div>
    <table class="summary-table">
        <thead>
            <tr>
                <th style="width: 25%;">Kategori</th>
                <th style="width: 20%;">Total Hari Hari Ini</th>
                <th style="width: 20%;">Persentase (%)</th>
                <th style="width: 35%;">Keterangan Perhitungan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Hadir (H)</strong></td>
                <td class="text-center">{{ $totalHadir }}</td>
                <td class="text-center font-bold" style="color: green;">{{ $classAttendanceRate }}%</td>
                <td>({{ $totalHadir }} / {{ $totalExpectedDays }}) × 100%</td>
            </tr>
            <tr>
                <td><strong>Sakit (S)</strong></td>
                <td class="text-center">{{ $totalSakit }}</td>
                <td class="text-center">{{ $classSakitRate }}%</td>
                <td>({{ $totalSakit }} / {{ $totalExpectedDays }}) × 100%</td>
            </tr>
            <tr>
                <td><strong>Izin (I)</strong></td>
                <td class="text-center">{{ $totalIzin }}</td>
                <td class="text-center">{{ $classIzinRate }}%</td>
                <td>({{ $totalIzin }} / {{ $totalExpectedDays }}) × 100%</td>
            </tr>
            <tr>
                <td><strong>Alpa (A)</strong></td>
                <td class="text-center">{{ $totalAlpa }}</td>
                <td class="text-center" style="color: red;">{{ $classAlpaRate }}%</td>
                <td>({{ $totalAlpa }} / {{ $totalExpectedDays }}) × 100%</td>
            </tr>
            <tr class="bg-highlight font-bold">
                <td><strong>RATA-RATA KEHADIRAN KELAS</strong></td>
                <td class="text-center">{{ $totalHadir }} / {{ $totalExpectedDays }}</td>
                <td class="text-center font-bold" style="font-size: 9pt;">{{ $classAttendanceRate }}%</td>
                <td>
                    {{ $totalStudents }} Siswa × {{ $effectiveDays }} Hari Bulan Berjalan = {{ $totalExpectedDays }} Target Hari
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection