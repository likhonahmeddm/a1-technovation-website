<?php
declare(strict_types=1);

require_once __DIR__ . '/php/cms/bootstrap.php';
require_once __DIR__ . '/php/cms/public-layout.php';

$posts = [];
$dbError = '';

try {
    $posts = cms_get_published_posts(24);
} catch (Throwable $exception) {
    $dbError = $exception->getMessage();
}

$featuredPost = $posts[0] ?? null;
$latestPosts = array_slice($posts, $featuredPost ? 1 : 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Read A1 Technovation blog posts on SEO, PPC, social media, web design, and growth strategy." />
  <meta property="og:title" content="A1 Technovation Blog" />
  <meta property="og:description" content="Fresh SEO, web design, PPC, and growth marketing insights from A1 Technovation." />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="<?= cms_e(cms_blog_index_url()) ?>" />
  <meta property="og:image" content="<?= cms_e(cms_url('assets/images/A1Technovation-Light-Background.png')) ?>" />
  <title>A1 Technovation Blog</title>
  <link rel="canonical" href="<?= cms_e(cms_blog_index_url()) ?>" />
  <link rel="icon" type="image/png" href="<?= cms_e(cms_url('assets/images/favicon.png')) ?>" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="<?= cms_e(cms_url('css/style.css?v=20260504')) ?>" />
  <link rel="stylesheet" href="<?= cms_e(cms_url('css/responsive.css?v=20260504')) ?>" />
  <link rel="stylesheet" href="<?= cms_e(cms_url('css/cms-public.css?v=20260504')) ?>" />
</head>
<body>
<?php cms_public_nav('blog'); ?>

<section class="page-hero">
  <div class="hero-bg"><div class="hero-grid"></div><div class="hero-glow-1"></div><div class="hero-glow-2"></div></div>
  <div class="container"><div class="page-hero-inner">
    <div class="breadcrumb"><a href="<?= cms_e(cms_url('')) ?>">Home</a><i class="fas fa-chevron-right"></i><span>Blog</span></div>
    <span class="label label-light label-dot">Live CMS Blog</span>
    <h1>Digital Growth <span class="text-grad-hero">Insights That Ship</span></h1>
    <p>Published directly from the new PHP and MySQL CMS, ready for ongoing SEO content, case studies, and thought leadership.</p>
  </div></div>
</section>

<section class="section">
  <div class="container">
    <?php if ($dbError !== ''): ?>
      <div class="cms-flash cms-flash-warning"><?= cms_e($dbError) ?></div>
    <?php endif; ?>

    <?php if ($featuredPost): ?>
      <div class="section-header reveal">
        <span class="label label-primary label-dot">Featured Article</span>
      </div>
      <div class="featured-panel reveal">
        <div class="featured-panel-media">
          <img src="<?= cms_e(cms_media_url((string) ($featuredPost['featured_image'] ?: 'assets/images/online-marketing.webp'))) ?>" alt="<?= cms_e((string) $featuredPost['title']) ?>" loading="lazy" />
        </div>
        <div class="featured-panel-copy">
          <div class="blog-meta"><span class="b-cat"><?= cms_e((string) ($featuredPost['category'] ?: 'Insights')) ?></span><span class="b-dot">●</span><span class="b-date"><?= cms_e(cms_format_date((string) $featuredPost['published_at'])) ?></span></div>
          <h2 style="font-size:1.875rem"><?= cms_e((string) $featuredPost['title']) ?></h2>
          <p style="font-size:1rem;line-height:1.75;margin:0"><?= cms_e((string) $featuredPost['excerpt']) ?></p>
          <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
            <div class="b-author"><img src="<?= cms_e(cms_url('assets/images/CEO%20of%20A1%20Technovation.webp')) ?>" alt="<?= cms_e((string) $featuredPost['author_name']) ?>" class="b-author-img" /><?= cms_e((string) $featuredPost['author_name']) ?></div>
            <span class="b-read"><i class="fas fa-clock"></i> Live from CMS</span>
          </div>
          <a href="<?= cms_e(cms_blog_post_url((string) $featuredPost['slug'])) ?>" class="btn btn-primary">Read Article <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="section section-gray" style="padding-top:0">
  <div class="container">
    <div class="section-header reveal" style="margin-bottom:48px">
      <span class="label label-primary label-dot">Latest Articles</span>
      <h2>Published from the <span class="text-grad">New CMS</span></h2>
    </div>

    <?php if ($latestPosts === [] && !$featuredPost): ?>
      <div class="cms-empty-state">No posts have been published yet. Sign in to <a href="<?= cms_e(cms_url('admin/login.php')) ?>" style="color:#0f6fec;font-weight:700">the CMS admin</a> and publish the first article.</div>
    <?php else: ?>
      <div class="blog-grid">
        <?php foreach ($latestPosts as $index => $post): ?>
          <div class="blog-card reveal d<?= (($index % 3) + 1) ?>">
            <div class="blog-img-box"><img src="<?= cms_e(cms_media_url((string) ($post['featured_image'] ?: 'assets/images/SEO.webp'))) ?>" alt="<?= cms_e((string) $post['title']) ?>" class="blog-img" loading="lazy" /></div>
            <div class="blog-body">
              <div class="blog-meta"><span class="b-cat"><?= cms_e((string) ($post['category'] ?: 'Insights')) ?></span><span class="b-dot">●</span><span class="b-date"><?= cms_e(cms_format_date((string) $post['published_at'])) ?></span></div>
              <h4><?= cms_e((string) $post['title']) ?></h4>
              <p><?= cms_e((string) $post['excerpt']) ?></p>
              <div class="blog-footer"><div class="b-author"><img src="<?= cms_e(cms_url('assets/images/CEO%20of%20A1%20Technovation.webp')) ?>" alt="<?= cms_e((string) $post['author_name']) ?>" class="b-author-img" /><?= cms_e((string) $post['author_name']) ?></div><span class="b-read"><i class="fas fa-arrow-right"></i> Read now</span></div>
              <a href="<?= cms_e(cms_blog_post_url((string) $post['slug'])) ?>" class="btn btn-secondary btn-sm" style="margin-top:18px">Open Post</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php cms_public_footer(); ?>
</body>
</html>
