<?php
declare(strict_types=1);

require_once __DIR__ . '/../php/cms/bootstrap.php';

cms_require_login();

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
$stats = cms_get_dashboard_stats();
$recentPosts = array_slice(cms_get_posts_admin(), 0, 5);
$recentMedia = array_slice(cms_get_recent_media(), 0, 6);
$recentPages = array_slice(cms_get_pages_admin(), 0, 5);

require __DIR__ . '/includes/header.php';
?>
<section class="admin-topbar">
  <div>
    <span class="admin-kicker"><i class="fas fa-chart-line"></i> CMS Overview</span>
    <h1 class="admin-page-title">Everything in one place</h1>
    <p class="admin-page-copy">Track content production, jump into the editor, and keep the media library organised without leaving the single-admin control panel.</p>
  </div>
  <div class="admin-user-chip"><i class="fas fa-user-shield"></i> <?= cms_e($currentAdmin['full_name'] ?? 'Admin') ?></div>
</section>

<section class="stats-grid" style="grid-template-columns:repeat(6,minmax(0,1fr));margin-bottom:24px">
  <article class="stats-card">
    <span class="admin-kicker">Posts</span>
    <strong><?= (int) $stats['posts_total'] ?></strong>
    <p>Total blog entries in the CMS database.</p>
  </article>
  <article class="stats-card">
    <span class="admin-kicker">Published</span>
    <strong><?= (int) $stats['posts_published'] ?></strong>
    <p>Articles already live on the public blog page.</p>
  </article>
  <article class="stats-card">
    <span class="admin-kicker">Drafts</span>
    <strong><?= (int) $stats['posts_draft'] ?></strong>
    <p>Posts waiting for review, polish, or scheduling.</p>
  </article>
  <article class="stats-card">
    <span class="admin-kicker">Media</span>
    <strong><?= (int) $stats['media_total'] ?></strong>
    <p>Uploaded images, files, and rich assets ready to reuse.</p>
  </article>
  <article class="stats-card">
    <span class="admin-kicker">Pages</span>
    <strong><?= (int) $stats['pages_total'] ?></strong>
    <p>Dynamic pages and service templates in the builder.</p>
  </article>
  <article class="stats-card">
    <span class="admin-kicker">Sections</span>
    <strong><?= (int) $stats['templates_total'] ?></strong>
    <p>Reusable widget templates saved for future pages.</p>
  </article>
</section>

<section class="dashboard-grid">
  <article class="table-card">
    <div class="toolbar">
      <div>
        <h3>Recent pages</h3>
        <p class="muted">Service pages, landing pages, and standard pages built in the new visual CMS.</p>
      </div>
      <div class="card-actions">
        <a href="<?= cms_e(cms_url('admin/pages.php')) ?>" class="btn btn-primary btn-sm"><i class="fas fa-laptop-code"></i> Manage Pages</a>
      </div>
    </div>

    <?php if ($recentPages === []): ?>
      <div class="empty-state">No dynamic pages yet. Build your first service page from the pages screen.</div>
    <?php else: ?>
      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>Title</th>
              <th>Type</th>
              <th>Status</th>
              <th>Updated</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentPages as $page): ?>
              <tr>
                <td><strong><?= cms_e((string) $page['title']) ?></strong><p><?= cms_e((string) $page['slug']) ?></p></td>
                <td><?= cms_e(ucfirst((string) $page['page_type'])) ?></td>
                <td><span class="status-badge status-<?= cms_e((string) $page['status']) ?>"><?= cms_e((string) $page['status']) ?></span></td>
                <td><?= cms_e(cms_format_date((string) ($page['updated_at'] ?? ''), 'M j, Y g:i A')) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </article>

  <article class="table-card">
    <div class="toolbar">
      <div>
        <h3>Recent blog posts</h3>
        <p class="muted">Jump back into your latest drafts and published entries.</p>
      </div>
      <div class="card-actions">
        <a href="<?= cms_e(cms_url('admin/blogs.php')) ?>" class="btn btn-primary btn-sm"><i class="fas fa-pen"></i> Manage Posts</a>
      </div>
    </div>

    <?php if ($recentPosts === []): ?>
      <div class="empty-state">No posts yet. Create your first draft from the blogs screen.</div>
    <?php else: ?>
      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>Title</th>
              <th>Status</th>
              <th>Category</th>
              <th>Updated</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentPosts as $post): ?>
              <tr>
                <td>
                  <strong><?= cms_e((string) $post['title']) ?></strong>
                  <p><?= cms_e((string) $post['slug']) ?></p>
                </td>
                <td><span class="status-badge status-<?= cms_e((string) $post['status']) ?>"><?= cms_e((string) $post['status']) ?></span></td>
                <td><?= cms_e((string) ($post['category'] ?? 'Uncategorised')) ?></td>
                <td><?= cms_e(cms_format_date((string) ($post['updated_at'] ?? ''), 'M j, Y g:i A')) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </article>

  <article class="table-card">
    <div class="toolbar">
      <div>
        <h3>Latest media</h3>
        <p class="muted">Copy URLs from the library directly into featured images or article content.</p>
      </div>
      <div class="card-actions">
        <a href="<?= cms_e(cms_url('admin/media.php')) ?>" class="btn btn-secondary btn-sm"><i class="fas fa-photo-film"></i> Open Media</a>
      </div>
    </div>

    <?php if ($recentMedia === []): ?>
      <div class="empty-state">Upload the first featured image, PDF, or video into the CMS media library.</div>
    <?php else: ?>
      <div class="media-grid">
        <?php foreach ($recentMedia as $media): ?>
          <?php $mediaUrl = cms_media_url((string) $media['file_path']); ?>
          <article class="media-card">
            <div class="media-preview">
              <?php if (str_starts_with((string) $media['mime_type'], 'image/')): ?>
                <img src="<?= cms_e($mediaUrl) ?>" alt="<?= cms_e((string) ($media['alt_text'] ?: $media['title'])) ?>" />
              <?php elseif (str_starts_with((string) $media['mime_type'], 'video/')): ?>
                <video src="<?= cms_e($mediaUrl) ?>" muted playsinline></video>
              <?php else: ?>
                <i class="fas fa-file-lines"></i>
              <?php endif; ?>
            </div>
            <div class="media-body">
              <strong><?= cms_e((string) $media['title']) ?></strong>
              <input class="media-url" type="text" readonly value="<?= cms_e($mediaUrl) ?>" />
              <button type="button" class="btn btn-secondary btn-sm" data-copy-text="<?= cms_e($mediaUrl) ?>">Copy URL</button>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </article>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
