<?php

return [
    
    // File Upload Settings
    'upload' => [
        'max_size' => 51200, // 50MB in KB
        'allowed_types' => [
            'documents' => ['pdf', 'doc', 'docx', 'ppt', 'pptx'],
            'images' => ['jpg', 'jpeg', 'png', 'gif'],
            'videos' => ['mp4', 'avi', 'mov'],
        ],
    ],

    // Absensi Settings
    'absensi' => [
        'statuses' => [
            'hadir' => 'Hadir',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alpha' => 'Alpha',
        ],
        'default_status' => 'alpha',
    ],

    // Nilai Settings
    'nilai' => [
        'min' => 0,
        'max' => 100,
        'grades' => [
            'A' => ['min' => 90, 'max' => 100],
            'B' => ['min' => 80, 'max' => 89],
            'C' => ['min' => 70, 'max' => 79],
            'D' => ['min' => 60, 'max' => 69],
            'E' => ['min' => 0, 'max' => 59],
        ],
    ],

    // Kelas Settings
    // 'kelas' => [
    //     1 => 'Kelas 1',
    //     2 => 'Kelas 2',
    //     3 => 'Kelas 3',
    //     4 => 'Kelas 4',
    //     5 => 'Kelas 5',
    //     6 => 'Kelas 6',
    // ],

    'daftar_kelas' => [
        '1A', '1B',
        '2A', '2B',
        '3A', '3B',
        '4A', '4B',
        '5A', '5B',
        '6A', '6B',
    ],

    // Pagination
    'pagination' => [
        'per_page' => 15,
        'materi' => 15,
        'absensi' => 20,
        'jawaban' => 20,
    ],

    // Dashboard
    'dashboard' => [
        'recent_items' => 5,
        'top_siswa' => 5,
        'pending_kuis' => 10,
        'chart_months' => 6,
    ],

    // Notifications
    'notifications' => [
        'kuis_deadline_days' => 3, // Notify 3 days before deadline
        'alpha_threshold' => 3, // Notify if student alpha >= 3 times
    ],
];
