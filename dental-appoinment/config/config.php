<?php

// ตั้งค่าแอปและฐานข้อมูล รองรับ Docker และ XAMPP
return [
    'app_name' => 'DentiCare',

    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3307',
        'name' => getenv('DB_NAME') ?: 'dental_db',
        'user' => getenv('DB_USER') ?: 'dental_user',
        'pass' => getenv('DB_PASS') ?: 'dental_pass',
    ],
];