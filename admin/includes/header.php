<?php
declare(strict_types=1);

$pageTitle = $pageTitle ?? 'CMS';
$activeNav = $activeNav ?? '';
$currentAdmin = cms_current_admin();
$flash = $flash ?? cms_flash_get();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= cms_e($pageTitle) ?> | A1 Technovation CMS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="<?= cms_e(cms_url('admin/assets/admin.css?v=20260504')) ?>" />
</head>
<body>
<div class="admin-shell">
  <aside class="admin-sidebar">
    <div class="admin-brand">
      <img src="<?= cms_e(cms_url('assets/images/A1Technovation-Light-Background.png')) ?>" alt="A1 Technovation" />
    </div>
    <div class="admin-brand-copy">
      <strong>Content Control Panel</strong>
      <span>Single-admin publishing for the live site blog.</span>
    </div>

    <nav class="admin-nav" aria-label="CMS navigation">
      <a href="<?= cms_e(cms_url('admin/dashboard.php')) ?>" class="<?= $activeNav === 'dashboard' ? 'active' : '' ?>"><i class="fas fa-chart-pie"></i> Dashboard</a>
      <a href="<?= cms_e(cms_url('admin/blogs.php')) ?>" class="<?= $activeNav === 'blogs' ? 'active' : '' ?>"><i class="fas fa-newspaper"></i> Blogs</a>
      <a href="<?= cms_e(cms_url('admin/pages.php')) ?>" class="<?= $activeNav === 'pages' ? 'active' : '' ?>"><i class="fas fa-laptop-code"></i> Pages</a>
      <a href="<?= cms_e(cms_url('admin/templates.php')) ?>" class="<?= $activeNav === 'templates' ? 'active' : '' ?>"><i class="fas fa-layer-group"></i> Templates</a>
      <a href="<?= cms_e(cms_url('admin/settings.php')) ?>" class="<?= $activeNav === 'settings' ? 'active' : '' ?>"><i class="fas fa-sliders"></i> Settings</a>
      <a href="<?= cms_e(cms_url('admin/media.php')) ?>" class="<?= $activeNav === 'media' ? 'active' : '' ?>"><i class="fas fa-photo-film"></i> Media</a>
      <a href="<?= cms_e(cms_blog_index_url()) ?>" target="_blank" rel="noopener"><i class="fas fa-globe"></i> View Public Blog</a>
      <a href="<?= cms_e(cms_url('admin/logout.php')) ?>"><i class="fas fa-right-from-bracket"></i> Logout</a>
    </nav>

    <div class="admin-sidebar-footer">
      <p class="admin-sidebar-note">Current admin: <?= cms_e($currentAdmin['full_name'] ?? 'Admin') ?><br /><?= cms_e($currentAdmin['email'] ?? '') ?></p>
    </div>
  </aside>

  <main class="admin-main">
    <?php if (is_array($flash) && isset($flash['message'], $flash['type'])): ?>
      <div class="flash flash-<?= cms_e((string) $flash['type']) ?>" data-autohide-flash><?= cms_e((string) $flash['message']) ?></div>
    <?php endif; ?>
