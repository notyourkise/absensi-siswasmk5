<?php

return [
    'name'    => 'Absensi SMKN 5 Samarinda',
    'manifest' => [
        'name'             => env('APP_NAME', 'Absensi SMKN 5'),
        'short_name'       => 'Absensi',
        'start_url'        => '/absensi', // Arahkan ke /absensi
        'background_color' => '#ffffff',
        'theme_color'      => '#0056b3',
        'display'          => 'standalone',
        'orientation'      => 'portrait',
        'status_bar'       => 'black',
        'icons'            => [
            // Biarkan kosong, nanti script otomatis generate jika logo ada di public
            // Atau isi manual seperti ini:
            [
                'src' => '/logo/sekolah.png', // Sesuaikan path logo Anda
                'type' => 'image/png',
                'sizes' => '512x512',
                'purpose' => 'any maskable'
            ]
        ],
        // 'custom' => [] // Kosongkan saja
    ]
];