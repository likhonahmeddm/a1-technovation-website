<?php
declare(strict_types=1);

require_once __DIR__ . '/php/cms/bootstrap.php';
require_once __DIR__ . '/php/cms/public-layout.php';
require_once __DIR__ . '/php/cms/page-renderer.php';

$context = cms_page_request_context();
$pageType = match ($context['prefix']) {
    'services' => 'service',
    'landing' => 'landing',
    default => 'page',
};

$page = cms_get_published_page_by_slug((string) $context['slug'], $pageType);

if (!is_array($page)) {
    http_response_code(404);
}

$title = is_array($page) ? (string) ($page['meta_title'] ?: $page['title']) : 'Page Not Found';
$description = is_array($page) ? (string) ($page['meta_description'] ?: $page['excerpt']) : 'The requested page could not be found.';
$canonical = is_array($page) && (string) ($page['canonical_url'] ?? '') !== ''
    ? (string) $page['canonical_url']
    : (is_array($page) ? cms_page_url((string) $page['slug'], (string) $page['page_type']) : cms_url(''));
$ogImage = is_array($page) ? (string) ($page['og_image'] ?: $page['featured_image']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="<?= cms_e($description) ?>" />
  <meta property="og:title" content="<?= cms_e($title) ?>" />
  <meta property="og:description" content="<?= cms_e($description) ?>" />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="<?= cms_e($canonical) ?>" />
  <?php if ($ogImage !== ''): ?><meta property="og:image" content="<?= cms_e(cms_media_url($ogImage)) ?>" /><?php endif; ?>
  <title><?= cms_e($title) ?></title>
  <link rel="canonical" href="<?= cms_e($canonical) ?>" />
  <link rel="icon" type="image/png" href="<?= cms_e(cms_url('assets/images/favicon.png')) ?>" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="<?= cms_e(cms_url('css/style.css?v=20260505')) ?>" />
  <link rel="stylesheet" href="<?= cms_e(cms_url('css/responsive.css?v=20260505')) ?>" />
  <link rel="stylesheet" href="<?= cms_e(cms_url('css/cms-pages.css?v=20260504')) ?>" />
  <?php if (is_array($page) && trim((string) ($page['schema_json'] ?? '')) !== ''): ?><script type="application/ld+json"><?= (string) $page['schema_json'] ?></script><?php endif; ?>
  <?php if (is_array($page) && trim((string) ($page['custom_css'] ?? '')) !== ''): ?><style><?= (string) $page['custom_css'] ?></style><?php endif; ?>
</head>
<body>
<?php cms_public_nav('services'); ?>
<?php if (!is_array($page)): ?>
  <section class="section" style="padding-top:140px"><div class="container"><div class="cms-empty-state"><h2>Page not found</h2><p>The page you requested does not exist or is not published yet.</p></div></div></section>
<?php else: ?>
  <?= cms_render_widget_list((array) ($page['builder'] ?? [])) ?>
  <?php if (trim((string) ($page['custom_html'] ?? '')) !== ''): ?><section class="section"><div class="container"><?= (string) $page['custom_html'] ?></div></section><?php endif; ?>
  <?php if (trim((string) ($page['custom_js'] ?? '')) !== ''): ?><script><?= (string) $page['custom_js'] ?></script><?php endif; ?>
<?php endif; ?>
<?php cms_public_footer(); ?>
</body>
</html>
