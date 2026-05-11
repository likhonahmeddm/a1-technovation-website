<?php
declare(strict_types=1);

require_once __DIR__ . '/../php/cms/bootstrap.php';

cms_require_login();

$pageTitle = 'Blogs';
$activeNav = 'blogs';
$currentAdmin = cms_current_admin();
$errorMessage = '';

$blankPost = [
    'id' => 0,
    'title' => '',
    'slug' => '',
    'status' => 'draft',
    'excerpt' => '',
    'content_html' => '',
    'featured_image' => '',
    'author_name' => (string) cms_config('app.default_author', 'A1 Technovation'),
    'category' => '',
    'tags' => '',
    'meta_title' => '',
    'meta_description' => '',
    'published_at' => '',
];

$editingPost = $blankPost;

if (cms_is_post()) {
    try {
        cms_require_csrf();
        $action = (string) cms_post('action');

        if ($action === 'save_post') {
            $postId = cms_save_post($_POST, (int) ($currentAdmin['id'] ?? 0));
            cms_flash_set('success', 'Blog post saved successfully.');
            cms_redirect('admin/blogs.php?edit=' . $postId);
        }

        if ($action === 'delete_post') {
            cms_delete_post((int) cms_post('id'));
            cms_flash_set('success', 'Blog post deleted.');
            cms_redirect('admin/blogs.php');
        }
    } catch (Throwable $exception) {
        $errorMessage = $exception->getMessage();
        $editingPost = array_merge($blankPost, $_POST);
    }
}

$editId = cms_query_int('edit');
if ($editId > 0 && $errorMessage === '') {
    $loadedPost = cms_get_post($editId);
    if (is_array($loadedPost)) {
        $editingPost = array_merge($blankPost, $loadedPost);
    }
}

$editingPost['published_at'] = isset($editingPost['published_at']) && $editingPost['published_at'] !== ''
    ? str_replace(' ', 'T', substr((string) $editingPost['published_at'], 0, 16))
    : '';

$posts = cms_get_posts_admin();
$recentMedia = array_slice(cms_get_recent_media(), 0, 10);

require __DIR__ . '/includes/header.php';
?>
<section class="admin-topbar">
  <div>
    <span class="admin-kicker"><i class="fas fa-newspaper"></i> Blog Management</span>
    <h1 class="admin-page-title">Write, schedule, and publish</h1>
    <p class="admin-page-copy">Create long-form blog content with CKEditor, set featured images from the media library, and publish directly to the live clean blog URLs.</p>
  </div>
  <div class="card-actions">
    <a href="<?= cms_e(cms_blog_index_url()) ?>" target="_blank" rel="noopener" class="btn btn-secondary btn-sm"><i class="fas fa-globe"></i> View Public Blog</a>
  </div>
</section>

<?php if ($errorMessage !== ''): ?>
  <div class="flash flash-error"><?= cms_e($errorMessage) ?></div>
<?php endif; ?>

<section class="split-grid wide" style="display:grid;gap:20px">
  <article class="editor-card">
    <div class="toolbar">
      <div>
        <h3><?= (int) $editingPost['id'] > 0 ? 'Edit post' : 'Create a new post' ?></h3>
        <p class="muted">Draft it now, publish it when ready, and keep SEO fields alongside the content.</p>
      </div>
    </div>

    <form method="post" class="form-grid">
      <input type="hidden" name="_token" value="<?= cms_e(cms_csrf_token()) ?>" />
      <input type="hidden" name="action" value="save_post" />
      <input type="hidden" name="id" value="<?= (int) $editingPost['id'] ?>" />

      <div class="form-grid two-col">
        <div class="field">
          <label for="title">Post title</label>
          <input id="title" type="text" name="title" value="<?= cms_e((string) $editingPost['title']) ?>" required data-slug-source />
        </div>
        <div class="field">
          <label for="slug">Slug</label>
          <input id="slug" type="text" name="slug" value="<?= cms_e((string) $editingPost['slug']) ?>" data-slug-target />
        </div>
      </div>

      <div class="form-grid two-col">
        <div class="field">
          <label for="status">Status</label>
          <select id="status" name="status">
            <option value="draft" <?= (string) $editingPost['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
            <option value="published" <?= (string) $editingPost['status'] === 'published' ? 'selected' : '' ?>>Published</option>
          </select>
        </div>
        <div class="field">
          <label for="published_at">Publish date</label>
          <input id="published_at" type="datetime-local" name="published_at" value="<?= cms_e((string) $editingPost['published_at']) ?>" />
        </div>
      </div>

      <div class="form-grid two-col">
        <div class="field">
          <label for="author_name">Author name</label>
          <input id="author_name" type="text" name="author_name" value="<?= cms_e((string) $editingPost['author_name']) ?>" />
        </div>
        <div class="field">
          <label for="category">Category</label>
          <input id="category" type="text" name="category" value="<?= cms_e((string) $editingPost['category']) ?>" placeholder="SEO, PPC, Web Design..." />
        </div>
      </div>

      <div class="field">
        <label for="tags">Tags</label>
        <input id="tags" type="text" name="tags" value="<?= cms_e((string) $editingPost['tags']) ?>" placeholder="seo, content marketing, social media" />
      </div>

      <div class="field">
        <label for="featured_image">Featured image URL</label>
        <input id="featured_image" type="text" name="featured_image" value="<?= cms_e((string) $editingPost['featured_image']) ?>" placeholder="Paste a media library URL or external image URL" />
      </div>

      <div class="field">
        <label for="excerpt">Excerpt</label>
        <textarea id="excerpt" name="excerpt" placeholder="Leave blank to auto-generate from the article content."><?= cms_e((string) $editingPost['excerpt']) ?></textarea>
      </div>

      <div class="field">
        <label for="content_html">Article content</label>
        <textarea id="content_html" class="editor-textarea" name="content_html" data-ckeditor><?= cms_e((string) $editingPost['content_html']) ?></textarea>
      </div>

      <div class="field">
        <label for="custom_html">Custom HTML <span class="field-optional">(optional)</span></label>
        <p class="helper-text" style="margin-top:-4px">Inject raw HTML rendered below the article body — useful for embeds, iframes, maps, or custom elements.</p>
        <div class="code-editor-wrap">
          <div class="code-editor-bar"><i class="fas fa-code"></i> HTML</div>
          <textarea id="custom_html" name="custom_html" data-codemirror="html"><?= cms_e((string) $editingPost['custom_html']) ?></textarea>
        </div>
      </div>

      <div class="form-grid two-col">
        <div class="field">
          <label for="meta_title">Meta title</label>
          <input id="meta_title" type="text" name="meta_title" value="<?= cms_e((string) $editingPost['meta_title']) ?>" />
        </div>
        <div class="field">
          <label for="meta_description">Meta description</label>
          <textarea id="meta_description" name="meta_description" style="min-height:110px"><?= cms_e((string) $editingPost['meta_description']) ?></textarea>
        </div>
      </div>

      <div class="inline-actions">
        <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Save Post</button>
        <?php if ((int) $editingPost['id'] > 0 && (string) $editingPost['status'] === 'published'): ?>
          <a href="<?= cms_e(cms_blog_post_url((string) $editingPost['slug'])) ?>" target="_blank" rel="noopener" class="btn btn-secondary"><i class="fas fa-arrow-up-right-from-square"></i> Preview Live</a>
        <?php endif; ?>
      </div>
    </form>
  </article>

  <div class="content-grid" style="display:grid;gap:20px">
    <article class="table-card">
      <div class="toolbar">
        <div>
          <h3>Media shortcuts</h3>
          <p class="muted">Use these files as featured images or inline assets inside CKEditor.</p>
        </div>
        <a href="<?= cms_e(cms_url('admin/media.php')) ?>" class="btn btn-secondary btn-sm"><i class="fas fa-upload"></i> Manage Media</a>
      </div>

      <?php if ($recentMedia === []): ?>
        <div class="empty-state">The media library is empty. Upload files from the media page first.</div>
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
                  <i class="fas fa-file"></i>
                <?php endif; ?>
              </div>
              <div class="media-body">
                <strong><?= cms_e((string) $media['title']) ?></strong>
                <input class="media-url" type="text" readonly value="<?= cms_e($mediaUrl) ?>" />
                <div class="inline-actions">
                  <button type="button" class="btn btn-secondary btn-sm" data-copy-text="<?= cms_e($mediaUrl) ?>">Copy URL</button>
                  <button type="button" class="btn btn-ghost btn-sm" data-fill-target="#featured_image" data-fill-value="<?= cms_e($mediaUrl) ?>">Use as Featured</button>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </article>

    <article class="table-card">
      <div class="toolbar">
        <div>
          <h3>All posts</h3>
          <p class="muted">Edit an existing post or remove stale drafts.</p>
        </div>
      </div>

      <?php if ($posts === []): ?>
        <div class="empty-state">No posts created yet. The form on the left is ready for the first one.</div>
      <?php else: ?>
        <div class="table-scroll">
          <table>
            <thead>
              <tr>
                <th>Title</th>
                <th>Status</th>
                <th>Published</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($posts as $post): ?>
                <tr>
                  <td>
                    <strong><?= cms_e((string) $post['title']) ?></strong>
                    <p><?= cms_e((string) ($post['category'] ?: 'Uncategorised')) ?></p>
                  </td>
                  <td><span class="status-badge status-<?= cms_e((string) $post['status']) ?>"><?= cms_e((string) $post['status']) ?></span></td>
                  <td><?= cms_e(cms_format_date((string) ($post['published_at'] ?? ''))) ?></td>
                  <td>
                    <div class="inline-actions">
                      <a href="<?= cms_e(cms_url('admin/blogs.php?edit=' . (int) $post['id'])) ?>" class="btn btn-secondary btn-sm"><i class="fas fa-pen"></i> Edit</a>
                      <?php if ((string) $post['status'] === 'published'): ?>
                        <a href="<?= cms_e(cms_blog_post_url((string) $post['slug'])) ?>" target="_blank" rel="noopener" class="btn btn-ghost btn-sm"><i class="fas fa-eye"></i> View</a>
                      <?php endif; ?>
                      <form method="post" onsubmit="return confirm('Delete this post permanently?');">
                        <input type="hidden" name="_token" value="<?= cms_e(cms_csrf_token()) ?>" />
                        <input type="hidden" name="action" value="delete_post" />
                        <input type="hidden" name="id" value="<?= (int) $post['id'] ?>" />
                        <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </article>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
