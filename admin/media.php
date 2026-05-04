<?php
declare(strict_types=1);

require_once __DIR__ . '/../php/cms/bootstrap.php';

cms_require_login();

$pageTitle = 'Media';
$activeNav = 'media';
$currentAdmin = cms_current_admin();
$errorMessage = '';

if (cms_is_post()) {
    try {
        cms_require_csrf();
        $action = (string) cms_post('action');

        if ($action === 'upload_media') {
            cms_upload_media(
                $_FILES['media_file'] ?? [],
                (int) ($currentAdmin['id'] ?? 0),
                (string) cms_post('title'),
                (string) cms_post('alt_text')
            );
            cms_flash_set('success', 'Media uploaded successfully.');
            cms_redirect('admin/media.php');
        }

        if ($action === 'delete_media') {
            cms_delete_media((int) cms_post('id'));
            cms_flash_set('success', 'Media item removed.');
            cms_redirect('admin/media.php');
        }
    } catch (Throwable $exception) {
        $errorMessage = $exception->getMessage();
    }
}

$mediaItems = cms_get_recent_media(60);

require __DIR__ . '/includes/header.php';
?>
<section class="admin-topbar">
  <div>
    <span class="admin-kicker"><i class="fas fa-photo-film"></i> Media Library</span>
    <h1 class="admin-page-title">Upload once, reuse everywhere</h1>
    <p class="admin-page-copy">Store images, PDFs, and video files in one place, then paste their URLs into blog posts or featured image fields without touching the filesystem manually.</p>
  </div>
</section>

<?php if ($errorMessage !== ''): ?>
  <div class="flash flash-error"><?= cms_e($errorMessage) ?></div>
<?php endif; ?>

<section class="split-grid wide" style="display:grid;gap:20px">
  <article class="admin-card">
    <div class="toolbar">
      <div>
        <h3>Upload new media</h3>
        <p class="muted">Supported: JPG, PNG, WebP, GIF, SVG, MP4, WebM, and PDF.</p>
      </div>
    </div>

    <form method="post" enctype="multipart/form-data" class="form-grid">
      <input type="hidden" name="_token" value="<?= cms_e(cms_csrf_token()) ?>" />
      <input type="hidden" name="action" value="upload_media" />
      <div class="field">
        <label for="media_file">Select file</label>
        <input id="media_file" type="file" name="media_file" required />
      </div>
      <div class="form-grid two-col">
        <div class="field">
          <label for="title">Title</label>
          <input id="title" type="text" name="title" placeholder="Homepage hero, SEO infographic..." />
        </div>
        <div class="field">
          <label for="alt_text">Alt text</label>
          <input id="alt_text" type="text" name="alt_text" placeholder="Describe the image for accessibility" />
        </div>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-cloud-arrow-up"></i> Upload to Library</button>
    </form>
  </article>

  <article class="table-card">
    <div class="toolbar">
      <div>
        <h3>How to use this library</h3>
        <p class="muted">Each asset gets a stable public URL under <code>assets/uploads/media</code>.</p>
      </div>
    </div>
    <div class="form-grid">
      <div class="empty-state" style="text-align:left">
        <strong>Featured images</strong>
        <p class="muted">Copy an image URL and paste it into the featured image field on the blogs page.</p>
      </div>
      <div class="empty-state" style="text-align:left">
        <strong>Inside CKEditor</strong>
        <p class="muted">Paste the image or file URL into article content, or link to PDFs and media downloads inside your post body.</p>
      </div>
    </div>
  </article>
</section>

<section class="table-card" style="margin-top:20px">
  <div class="toolbar">
    <div>
      <h3>Library items</h3>
      <p class="muted">Newest uploads appear first.</p>
    </div>
  </div>

  <?php if ($mediaItems === []): ?>
    <div class="empty-state">No media uploaded yet.</div>
  <?php else: ?>
    <div class="media-grid">
      <?php foreach ($mediaItems as $media): ?>
        <?php $mediaUrl = cms_media_url((string) $media['file_path']); ?>
        <article class="media-card">
          <div class="media-preview">
            <?php if (str_starts_with((string) $media['mime_type'], 'image/')): ?>
              <img src="<?= cms_e($mediaUrl) ?>" alt="<?= cms_e((string) ($media['alt_text'] ?: $media['title'])) ?>" />
            <?php elseif (str_starts_with((string) $media['mime_type'], 'video/')): ?>
              <video src="<?= cms_e($mediaUrl) ?>" muted playsinline controls></video>
            <?php else: ?>
              <i class="fas fa-file-pdf"></i>
            <?php endif; ?>
          </div>
          <div class="media-body">
            <strong><?= cms_e((string) $media['title']) ?></strong>
            <span class="helper-text"><?= cms_e((string) $media['original_name']) ?></span>
            <input class="media-url" type="text" readonly value="<?= cms_e($mediaUrl) ?>" />
            <div class="inline-actions">
              <button type="button" class="btn btn-secondary btn-sm" data-copy-text="<?= cms_e($mediaUrl) ?>">Copy URL</button>
              <a href="<?= cms_e($mediaUrl) ?>" class="btn btn-ghost btn-sm" target="_blank" rel="noopener"><i class="fas fa-eye"></i> View</a>
              <form method="post" onsubmit="return confirm('Delete this media item?');">
                <input type="hidden" name="_token" value="<?= cms_e(cms_csrf_token()) ?>" />
                <input type="hidden" name="action" value="delete_media" />
                <input type="hidden" name="id" value="<?= (int) $media['id'] ?>" />
                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete</button>
              </form>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
