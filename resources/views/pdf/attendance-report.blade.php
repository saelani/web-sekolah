@extends('pdf.layouts.report-master')

@section('title', 'Laporan Rekapitulasi Ketidakhadiran Siswa')

@section('doc-title', 'REKAPITULASI KETIDAKHADIRAN SISWA')

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

    .signature-section {
        width: 100%;
        margin-top: 25px;
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
    <td style="width: 15%;"><strong>Periode</strong></td>
    <td style="width: 35%;">: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} s.d. {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</td>
</tr>
<tr>
    <td><strong>Wali Kelas</strong></td>
    <td>: {{ $teacher?->name ?? '-' }}</td>
    <td><strong>Tanggal Cetak</strong></td>
    <td>: {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</td>
</tr>
@endsection

@section('content')
{{-- TABEL DATA UTAMA REKAP KETIDAKHADIRAN SISWA --}}
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th style="width: 18%;">NISN / NIS</th>
            <th style="width: 37%;">Nama Siswa</th>
            <th style="width: 10%;">Sakit</th>
            <th style="width: 10%;">Izin</th>
            <th style="width: 10%;">Alpa</th>
            <th style="width: 10%;">Jumlah Total</th>
        </tr>
    </thead>
    <tbody>
        @forelse($summary as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $row['student']->nisn ?? $row['student']->nis ?? '-' }}</td>
                <td>{{ $row['student']->name ?? '-' }}</td>
                <td class="text-center">{{ $row['sakit'] }}</td>
                <td class="text-center">{{ $row['izin'] }}</td>
                <td class="text-center">{{ $row['alpa'] }}</td>
                <td class="text-center font-bold">{{ $row['total'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data siswa untuk periode ini.</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" class="text-right font-bold">TOTAL KESELURUHAN:</td>
            <td class="text-center">{{ $summary->sum('sakit') }}</td>
            <td class="text-center">{{ $summary->sum('izin') }}</td>
            <td class="text-center">{{ $summary->sum('alpa') }}</td>
            <td class="text-center font-bold">{{ $summary->sum('total') }}</td>
        </tr>
    </tfoot>
</table>

{{-- TITI MANGSA DAN TANDA TANGAN --}}
<table class="signature-section" style="border-collapse: collapse; border: none;">
    <tr>
        <td class="signature-box" style="text-align: left; vertical-align: top; border: none;">
            <div>Mengetahui,</div>
            <div>Kepala Sekolah</div>
            <br><br><br>
            <div style="font-weight: bold; text-decoration: underline;">{{ $headmaster?->name ?? '..................................' }}</div>
            <div>NIP. {{ $headmaster?->nip ?? '-' }}</div>
        </td>
        <td class="signature-box" style="text-align: right; vertical-align: top; border: none;">
            <div>{{ $school->city ?? 'Kota' }}, {{ $titiMangsa }}</div>
            <div>Wali Kelas</div>
            <br><br><br>
            <div style="font-weight: bold; text-decoration: underline;">{{ $teacher?->name ?? '..................................' }}</div>
            <div>NIP. {{ $teacher?->nip ?? '-' }}</div>
        </td>
    </tr>
</table>
@endsection