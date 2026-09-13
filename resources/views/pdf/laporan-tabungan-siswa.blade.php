@extends('pdf.layouts.master')

@section('orientation', 'landscape')
@section('title', 'Laporan Tabungan Siswa - ' . $class->name)
@section('doc-title', 'REKAPITULASI TABUNGAN SISWA')

@section('meta-data')
<tr>
    <td style="width: 15%;"><strong>Kelas</strong></td>
    <td style="width: 35%;">: {{ $class->name }}</td>
    <td style="width: 15%;"><strong>Periode Laporan</strong></td>
    <td style="width: 35%;">: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}</td>
</tr>
<tr>
    <td><strong>Wali Kelas</strong></td>
    <td>: {{ $teacher->name ?? '-' }}</td>
    <td><strong>Tanggal Cetak</strong></td>
    <td>: {{ date('d/m/Y H:i') }}</td>
</tr>
@endsection

@section('content')
<table class="table-data">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th style="width: 15%;">NISN</th>
            <th style="width: 30%;">Nama Siswa</th>
            <th style="width: 16%;">s/d Bulan Lalu (Rp)</th>
            <th style="width: 17%;">Jumlah Sekarang (Rp)</th>
            <th style="width: 17%;">s/d Bulan Ini (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($summary as $index => $row)
        <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td class="text-center">{{ $row['nisn'] }}</td>
            <td>{{ $row['name'] }}</td>
            <td class="text-right">{{ number_format($row['saldo_lalu'], 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($row['setor_sekarang'], 0, ',', '.') }}</td>
            <td class="text-right font-bold">{{ number_format($row['total_sekarang'], 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center">Tidak ada data tabungan pada periode ini.</td>
        </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr style="background-color: #f9fafb; font-weight: bold;">
            <td colspan="3" class="text-center">TOTAL KESELURUHAN</td>
            <td class="text-right">Rp {{ number_format($grandTotalLalu, 0, ',', '.') }}</td>
            <td class="text-right">Rp {{ number_format($grandSetorSekarang, 0, ',', '.') }}</td>
            <td class="text-right">Rp {{ number_format($grandTotalSekarang, 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>
@endsection