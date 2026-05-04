<?php
declare(strict_types=1);

function project_root(string $path = ''): string
{
    $root = dirname(__DIR__);

    if ($path === '') {
        return $root;
    }

    $normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

    return $root . DIRECTORY_SEPARATOR . ltrim($normalized, DIRECTORY_SEPARATOR);
}

function load_contact_form_config(): array
{
    $configPath = project_root('config/contact-form.php');

    if (!is_file($configPath)) {
        throw new RuntimeException('Missing config/contact-form.php. Copy config/contact-form.example.php and fill in your SMTP and database credentials.');
    }

    $config = require $configPath;

    if (!is_array($config)) {
        throw new RuntimeException('The contact form config file must return an array.');
    }

    require_once project_root('php/SmtpMailer.php');

    date_default_timezone_set($config['app']['timezone'] ?? 'Asia/Dhaka');

    return $config;
}

function contact_form_page_path(?array $config = null): string
{
    return $config['app']['contact_page'] ?? '/pages/contact.html';
}

function contact_form_service_label(string $value): string
{
    $labels = [
        'seo' => 'SEO Services',
        'web' => 'Web Design & Development',
        'ppc' => 'PPC Advertising',
        'social' => 'Social Media Marketing',
        'full' => 'Full Digital Marketing Package',
        'other' => 'Other / Not Sure Yet',
    ];

    return $labels[$value] ?? ucwords(str_replace(['-', '_'], ' ', $value));
}

function config_bool(mixed $value): bool
{
    if (is_bool($value)) {
        return $value;
    }

    if (is_string($value)) {
        return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }

    return (bool) $value;
}
