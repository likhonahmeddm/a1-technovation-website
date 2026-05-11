<?php
declare(strict_types=1);

function cms_admin_count(): int
{
    if (!cms_table_exists('admin_users')) {
        return 0;
    }

    return (int) cms_db()->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
}

function cms_setup_required(): bool
{
    return cms_admin_count() === 0;
}

function cms_admin_exists_by_email(string $email): bool
{
    $email = strtolower(trim($email));

    if ($email === '' || !cms_table_exists('admin_users')) {
        return false;
    }

    $statement = cms_db()->prepare('SELECT id FROM admin_users WHERE email = :email LIMIT 1');
    $statement->execute(['email' => $email]);

    return (bool) $statement->fetchColumn();
}

function cms_create_admin(string $fullName, string $email, string $password): int
{
    $fullName = trim($fullName);
    $email = strtolower(trim($email));

    if ($fullName === '') {
        throw new RuntimeException('Admin full name is required.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Enter a valid admin email address.');
    }

    if (strlen($password) < 8) {
        throw new RuntimeException('Admin password must be at least 8 characters long.');
    }

    if (cms_admin_exists_by_email($email)) {
        throw new RuntimeException('An admin account with this email already exists. Please sign in instead.');
    }

    $statement = cms_db()->prepare(
        'INSERT INTO admin_users (full_name, email, password_hash) VALUES (:full_name, :email, :password_hash)'
    );
    $statement->execute([
        'full_name' => $fullName,
        'email' => $email,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    ]);

    return (int) cms_db()->lastInsertId();
}

function cms_current_admin(): ?array
{
    $adminId = $_SESSION['cms_admin_id'] ?? null;

    if (!is_int($adminId) && !ctype_digit((string) $adminId)) {
        return null;
    }

    if (!cms_table_exists('admin_users')) {
        return null;
    }

    $statement = cms_db()->prepare('SELECT id, full_name, email, last_login_at, created_at FROM admin_users WHERE id = :id LIMIT 1');
    $statement->execute(['id' => (int) $adminId]);
    $admin = $statement->fetch();

    return is_array($admin) ? $admin : null;
}

function cms_is_logged_in(): bool
{
    return cms_current_admin() !== null;
}

function cms_login(string $email, string $password): bool
{
    if (!cms_table_exists('admin_users')) {
        throw new RuntimeException('The CMS database tables have not been installed yet.');
    }

    $statement = cms_db()->prepare('SELECT * FROM admin_users WHERE email = :email LIMIT 1');
    $statement->execute(['email' => strtolower(trim($email))]);
    $admin = $statement->fetch();

    if (!is_array($admin) || !password_verify($password, (string) $admin['password_hash'])) {
        return false;
    }

    $_SESSION['cms_admin_id'] = (int) $admin['id'];
    $_SESSION['cms_last_active_at'] = time();

    $updateStatement = cms_db()->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = :id');
    $updateStatement->execute(['id' => (int) $admin['id']]);

    return true;
}

function cms_logout(): void
{
    unset($_SESSION['cms_admin_id'], $_SESSION['cms_last_active_at']);
}

function cms_require_login(): void
{
    if (!cms_is_logged_in()) {
        cms_flash_set('error', 'Please sign in to access the CMS.');
        cms_redirect('admin/login.php');
    }
}
