<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@yield('title', 'Laporan')</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0mm;
        }

        html {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8.5pt;
            line-height: 1.2;
            color: #000000;
            margin: 0 !important;
            padding: 12mm 15mm 15mm 15mm !important; 
            background-color: #ffffff;
        }

        .text-center { text-align: center !important; }
        .text-left { text-align: left !important; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: bold !important; }

        /* KOP SEKOLAH */
        table.header-table {
            width: 100% !important;
            border-collapse: collapse !important;
            table-layout: fixed !important;
            margin-bottom: 2px !important;
        }

        table.header-table td {
            padding: 0 !important;
            border: none !important;
            vertical-align: middle !important;
        }

        .header-divider {
            border: none;
            border-top: 2px solid #000000;
            border-bottom: 0.8px solid #000000;
            height: 1px;
            margin-top: 4px;
            margin-bottom: 10px;
            width: 100% !important;
        }

        .doc-title {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            margin-bottom: 10px;
        }

        table.meta-table {
            width: 100% !important;
            border-collapse: collapse !important;
            table-layout: fixed !important;
            margin-bottom: 8px !important;
        }

        table.meta-table td {
            padding: 2px 0 !important;
            border: none !important;
            vertical-align: top !important;
            font-size: 8.5pt !important;
        }

        table.table-data {
            width: 100% !important;
            border-collapse: collapse !important;
            margin-bottom: 10px;
        }

        table.table-data tr {
            page-break-inside: avoid;
        }

        table.table-data th, table.table-data td {
            border: 1px solid #000000;
            padding: 4px 5px;
        }

        table.table-data th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }

        table.signature-table {
            width: 100% !important;
            border-collapse: collapse !important;
            table-layout: fixed !important;
            margin-top: 20px !important;
            page-break-inside: avoid;
        }

        table.signature-table td {
            border: none !important;
            text-align: center !important;
            vertical-align: top !important;
        }

        .signature-space {
            height: 45px;
        }
    </style>
    
    @yield('styles')
</head>
<body>

    @php
        // Helper lokal untuk ubah gambar ke Base64 (Anti-Gagal di Dompdf)
        $getLogoBase64 = function ($path) {
            if (!empty($path) && file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                return 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
            return null;
        };

        // 1. LOGO SEKOLAH (Dari profil sekolah) -> UNTUK SEBELAH KIRI
        $schoolLogoPath = storage_path('app/public/' . ($school->logo_path ?? $school->logo ?? ''));
        $schoolLogoBase64 = $getLogoBase64($schoolLogoPath);

        // 2. LOGO KABUPATEN/PEMDA -> UNTUK SEBELAH KANAN
        $kabupatenPath = storage_path('app/public/logo-kuningan.jpg');
        if (!file_exists($kabupatenPath)) {
            $kabupatenPath = storage_path('app/public/logo-kuningan.png');
        }
        $logoKabupatenBase64 = $getLogoBase64($kabupatenPath);
    @endphp

    {{-- KOP SEKOLAH --}}
    <table class="header-table">
        <tr>
            {{-- SEBELAH KIRI: LOGO SEKOLAH --}}
            <td style="width: 12%; text-align: left;">
                @if($schoolLogoBase64)
                    <img src="{{ $schoolLogoBase64 }}" style="width: 48px; height: auto;">
                @endif
            </td>

            {{-- TENGAH: TEKS KOP SURAT --}}
            <td style="width: 76%; text-align: center;">
                <div style="font-size: 9pt; font-weight: bold;">PEMERINTAH KABUPATEN / KOTA {{ strtoupper($school->county ?? $schoolCounty ?? 'KUNINGAN') }}</div>
                <div style="font-size: 11pt; font-weight: bold;">DINAS PENDIDIKAN DAN KEBUDAYAAN</div>
                <div style="font-size: 10.5pt; font-weight: bold;">{{ strtoupper($school->school_name ?? $school->name ?? 'SDN 2 BABAKANMULYA') }}</div>
                <div style="font-size: 7.5pt; font-style: italic;">
                    {{ $school->address ?? 'Jalan Mayor Isma Rt. 08/01 Desa Babakanmulya Kec. Jalaksana Kab. Kuningan' }} | NPSN: {{ $school->npsn ?? '20213175' }}
                </div>
            </td>

            {{-- SEBELAH KANAN: LOGO KABUPATEN / PEMDA --}}
            <td style="width: 12%; text-align: right;">
                @if($logoKabupatenBase64)
                    <img src="{{ $logoKabupatenBase64 }}" style="width: 48px; height: auto;">
                @endif
            </td>
        </tr>
    </table>

    <div class="header-divider"></div>

    <div class="doc-title">
        @yield('doc-title', 'REKAPITULASI PRESENSI SISWA')
    </div>

    @hasSection('meta-data')
        <table class="meta-table">
            @yield('meta-data')
        </table>
    @endif

    <main>
        @yield('content')
    </main>

    @section('signatures')
    <table class="signature-table">
        <tr>
            <td style="width: 50%;">
                Mengetahui,<br>
                Kepala Sekolah
                <div class="signature-space"></div>
                <strong><u>
                    @if(isset($headmaster))
                        {{ $headmaster->front_title ? $headmaster->front_title . ' ' : '' }}{{ $headmaster->name }}{{ $headmaster->back_title ? ', ' . $headmaster->back_title : '' }}
                    @else
                        .........................................
                    @endif
                </u></strong><br>
                NIP. {{ $headmaster->nip ?? '-' }}
            </td>
            <td style="width: 50%;">
                {{ $school->city ?? 'Kuningan' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                Guru / Wali Kelas
                <div class="signature-space"></div>
                <strong><u>
                    @if(isset($teacher))
                        {{ $teacher->front_title ? $teacher->front_title . ' ' : '' }}{{ $teacher->name }}{{ $teacher->back_title ? ', ' . $teacher->back_title : '' }}
                    @else
                        .........................................
                    @endif
                </u></strong><br>
                NIP. {{ $teacher->nip ?? '-' }}
            </td>
        </tr>
    </table>
    @show

</body>
</html>