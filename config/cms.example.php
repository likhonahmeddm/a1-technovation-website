<?php
declare(strict_types=1);

return [
    'app' => [
        'name' => 'A1 Technovation CMS',
        'base_url' => 'https://a1technovation.com',
        'timezone' => 'Asia/Dhaka',
        'session_name' => 'a1tech_cms',
        'default_author' => 'A1 Technovation',
        'media_path' => 'assets/uploads/media',
        'ckeditor_cdn' => 'https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js',
    ],
    'database' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'a1technovation',
        'username' => 'db_user',
        'password' => 'change-me',
        'charset' => 'utf8mb4',
    ],
];
