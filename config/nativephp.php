<?php

return [
    'version' => env('NATIVEPHP_APP_VERSION', '1.0.0'),
    'app_id' => 'com.kemendikbud.erapords',
    'name' => 'E-Rapor SD Kurikulum Merdeka',

    /*
    |--------------------------------------------------------------------------
    | Window & Viewport Settings (Android Target)
    |--------------------------------------------------------------------------
    */
    'mobile' => [
        'orientation' => 'portrait', // Kunci orientasi ke Portrait
        'soft_input_mode' => 'adjustResize', // Mencegah Keyboard menutup Form Input Nilai
        'deep_linking' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Native Features
    |--------------------------------------------------------------------------
    */
    'features' => [
        'haptic' => true,      // Getar lembut saat tombol simpan ditekan
        'notification' => true, // Push notification lokal Android saat cetak rapor selesai
    ],
];
