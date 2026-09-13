@extends('pdf.layouts.master')

@section('title', 'Laporan Presensi Siswa - ' . $class->name)
@section('doc-title', 'REKAPITULASI PRESENSI SISWA')

@section('meta-data')
<tr>
    <td style="width: 15%;"><strong>Kelas</strong></td>
    <td style="width: 35%;">: {{ $class->name }}</td>
    <td style="width: 15%;"><strong>Periode</strong></td>
    <td style="width: 35%;">: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}</td>
</tr>
<tr>
    <td><strong>Wali Kelas</strong></td>
    <td>: {{ $teacher->name ?? $class->homeroomTeacher->name ?? '-' }}</td>
    <td><strong>Dicetak</strong></td>
    <td>: {{ date('d/m/Y H:i') }}</td>
</tr>
@endsection

@section('content')
<table class="table-data">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th style="width: 20%;">NISN / ID</th>
            <th style="width: 35%;">Nama Siswa</th>
            <th style="width: 10%;">Hadir</th>
            <th style="width: 10%;">Sakit</th>
            <th style="width: 10%;">Izin</th>
            <th style="width: 10%;">Alpa</th>
        </tr>
    </thead>
    <tbody>
        @forelse($summary as $index => $row)
        <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td class="text-center">{{ $row['student']->nisn ?? '-' }}</td>
            <td>{{ $row['student']->name }}</td>
            <td class="text-center">{{ $row['hadir'] }}</td>
            <td class="text-center">{{ $row['sakit'] }}</td>
            <td class="text-center">{{ $row['izin'] }}</td>
            <td class="text-center">{{ $row['alpa'] }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center">Tidak ada data presensi pada periode ini.</td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection