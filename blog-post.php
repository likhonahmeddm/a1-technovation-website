<?php
declare(strict_types=1);

require_once __DIR__ . '/php/cms/bootstrap.php';
require_once __DIR__ . '/php/cms/public-layout.php';

$slug = cms_blog_slug_from_request();
$post = null;
$relatedPosts = [];
$dbError = '';

try {
    if ($slug !== '') {
        $post = cms_get_published_post_by_slug($slug);
    }

    if (is_array($post)) {
        $relatedPosts = cms_get_related_posts((int) $post['id'], (string) ($post['category'] ?? ''), 3);
    }
} catch (Throwable $exception) {
    $dbError = $exception->getMessage();
}

if (!is_array($post)) {
    http_response_code(404);
}

$title = is_array($post) ? (string) ($post['meta_title'] ?: $post['title']) : 'Blog Post Not Found';
$description = is_array($post) ? (string) ($post['meta_description'] ?: $post['excerpt']) : 'The requested blog post could not be found.';
$canonicalUrl = is_array($post)
    ? cms_blog_post_url((string) $post['slug'])
    : cms_blog_index_url();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="<?= cms_e($description) ?>" />
  <meta property="og:title" content="<?= cms_e($title) ?>" />
  <meta property="og:description" content="<?= cms_e($description) ?>" />
  <meta property="og:type" content="article" />
  <meta property="og:url" content="<?= cms_e($canonicalUrl) ?>" />
  <meta property="og:image" content="<?= cms_e(is_array($post) ? cms_media_url((string) ($post['featured_image'] ?: 'assets/images/A1Technovation-Light-Background.png')) : cms_url('assets/images/A1Technovation-Light-Background.png')) ?>" />
  <title><?= cms_e($title) ?></title>
  <link rel="canonical" href="<?= cms_e($canonicalUrl) ?>" />
  <link rel="icon" type="image/png" href="<?= cms_e(cms_url('assets/images/favicon.png')) ?>" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="<?= cms_e(cms_url('css/style.css?v=20260504')) ?>" />
  <link rel="stylesheet" href="<?= cms_e(cms_url('css/responsive.css?v=20260504')) ?>" />
  <link rel="stylesheet" href="<?= cms_e(cms_url('css/cms-public.css?v=20260504')) ?>" />
</head>
<body>
<?php cms_public_nav('blog'); ?>

<?php if (!is_array($post)): ?>
  <section class="section" style="padding-top:140px">
    <div class="container">
      <?php if ($dbError !== ''): ?>
        <div class="cms-flash cms-flash-warning"><?= cms_e($dbError) ?></div>
      <?php endif; ?>
      <div class="cms-empty-state">
        <h2 style="margin-bottom:12px">Post not found</h2>
        <p class="muted">This article is missing or has not been published yet.</p>
        <a href="<?= cms_e(cms_blog_index_url()) ?>" class="btn btn-primary" style="margin-top:16px">Back to Blog</a>
      </div>
    </div>
  </section>
<?php else: ?>
  <section class="page-hero">
    <div class="hero-bg"><div class="hero-grid"></div><div class="hero-glow-1"></div><div class="hero-glow-2"></div></div>
    <div class="container"><div class="page-hero-inner">
      <div class="breadcrumb"><a href="<?= cms_e(cms_url('')) ?>">Home</a><i class="fas fa-chevron-right"></i><a href="<?= cms_e(cms_blog_index_url()) ?>">Blog</a><i class="fas fa-chevron-right"></i><span><?= cms_e((string) $post['title']) ?></span></div>
      <span class="label label-light label-dot"><?= cms_e((string) ($post['category'] ?: 'Insights')) ?></span>
      <h1><?= cms_e((string) $post['title']) ?></h1>
      <p><?= cms_e((string) $post['excerpt']) ?></p>
      <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;color:#dbe7ff">
        <span><i class="fas fa-user"></i> <?= cms_e((string) $post['author_name']) ?></span>
        <span><i class="fas fa-calendar"></i> <?= cms_e(cms_format_date((string) $post['published_at'], 'F j, Y')) ?></span>
      </div>
    </div></div>
  </section>

  <section class="section">
    <div class="container">
      <?php if ($dbError !== ''): ?>
        <div class="cms-flash cms-flash-warning"><?= cms_e($dbError) ?></div>
      <?php endif; ?>

      <?php if ((string) ($post['featured_image'] ?? '') !== ''): ?>
        <div class="featured-panel" style="margin-bottom:36px">
          <div class="featured-panel-media" style="width:100%">
            <img src="<?= cms_e(cms_media_url((string) $post['featured_image'])) ?>" alt="<?= cms_e((string) $post['title']) ?>" />
          </div>
        </div>
      <?php endif; ?>

      <article class="cms-article-content">
        <?= (string) $post['content_html'] ?>
      </article>
    </div>
  </section>

  <?php if ($relatedPosts !== []): ?>
    <section class="section section-gray" style="padding-top:0">
      <div class="container">
        <div class="section-header reveal" style="margin-bottom:48px">
          <span class="label label-primary label-dot">More Articles</span>
          <h2>Keep Reading</h2>
        </div>
        <div class="blog-grid">
          <?php foreach ($relatedPosts as $index => $related): ?>
            <div class="blog-card reveal d<?= (($index % 3) + 1) ?>">
              <div class="blog-img-box"><img src="<?= cms_e(cms_media_url((string) ($related['featured_image'] ?: 'assets/images/SEO.webp'))) ?>" alt="<?= cms_e((string) $related['title']) ?>" class="blog-img" loading="lazy" /></div>
              <div class="blog-body">
                <div class="blog-meta"><span class="b-cat"><?= cms_e((string) ($related['category'] ?: 'Insights')) ?></span><span class="b-dot">●</span><span class="b-date"><?= cms_e(cms_format_date((string) $related['published_at'])) ?></span></div>
                <h4><?= cms_e((string) $related['title']) ?></h4>
                <p><?= cms_e((string) $related['excerpt']) ?></p>
                <a href="<?= cms_e(cms_blog_post_url((string) $related['slug'])) ?>" class="btn btn-secondary btn-sm" style="margin-top:18px">Open Post</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>
<?php endif; ?>

<?php cms_public_footer(); ?>
</body>
</html>
