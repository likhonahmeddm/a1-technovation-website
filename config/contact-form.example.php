<?php
declare(strict_types=1);

return [
    'app' => [
        'base_url' => 'https://a1technovation.com',
        'contact_page' => '/pages/contact.html',
        'timezone' => 'Asia/Dhaka',
    ],
    'database' => [
        'enabled' => false,
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'a1technovation',
        'username' => 'db_user',
        'password' => 'change-me',
    ],
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'smtp-user@example.com',
        'password' => 'app-password',
        'encryption' => 'tls',
        'auth' => true,
        'timeout' => 20,
        'helo_host' => 'a1technovation.com',
    ],
    'mail' => [
        'from_email' => 'noreply@a1technovation.com',
        'from_name' => 'A1 Technovation',
        'notification_email' => 'info.a1technovation@gmail.com',
        'notification_name' => 'A1 Technovation Sales',
        'transport' => 'smtp',
        'fallback_to_native_mail' => true,
        'send_auto_reply' => true,
        'auto_reply_subject' => 'Thanks for contacting A1 Technovation',
    ],
];
