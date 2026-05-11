<?php
declare(strict_types=1);

require_once __DIR__ . '/../php/cms/bootstrap.php';

cms_require_login();

$pageTitle = 'Pages';
$activeNav = 'pages';
$currentAdmin = cms_current_admin();
$errorMessage = '';

$blankPage = [
    'id' => 0,
    'title' => '',
    'slug' => '',
    'page_type' => 'service',
    'status' => 'draft',
    'excerpt' => '',
    'template_key' => 'service',
    'featured_image' => '',
    'custom_html' => '',
    'custom_css' => '',
    'custom_js' => '',
    'show_in_nav' => 1,
    'nav_group' => 'services',
    'sort_order' => 0,
    'meta_title' => '',
    'meta_description' => '',
    'canonical_url' => '',
    'og_image' => '',
    'schema_json' => '',
    'published_at' => '',
    'builder' => [],
];

$editingPage = $blankPage;

if (cms_is_post()) {
    try {
        cms_require_csrf();
        $action = (string) cms_post('action');

        if ($action === 'save_page') {
            $pageId = cms_save_page($_POST, (int) ($currentAdmin['id'] ?? 0));
            cms_flash_set('success', 'Page saved successfully.');
            cms_redirect('admin/pages.php?edit=' . $pageId);
        }

        if ($action === 'delete_page') {
            cms_delete_page((int) cms_post('id'));
            cms_flash_set('success', 'Page deleted.');
            cms_redirect('admin/pages.php');
        }

        if ($action === 'duplicate_page') {
            $copyId = cms_duplicate_page((int) cms_post('id'), (int) ($currentAdmin['id'] ?? 0));
            cms_flash_set('success', 'Page duplicated as a draft copy.');
            cms_redirect('admin/pages.php?edit=' . $copyId);
        }
    } catch (Throwable $exception) {
        $errorMessage = $exception->getMessage();
        $editingPage = array_merge($blankPage, $_POST);
        $editingPage['builder'] = cms_json_decode_array((string) ($_POST['builder_json'] ?? ''), []);
    }
}

$editId = cms_query_int('edit');
if ($editId > 0 && $errorMessage === '') {
    $loadedPage = cms_get_page($editId);
    if (is_array($loadedPage)) {
        $editingPage = array_merge($blankPage, $loadedPage);
    }
}

$editingPage['published_at'] = isset($editingPage['published_at']) && $editingPage['published_at'] !== ''
    ? str_replace(' ', 'T', substr((string) $editingPage['published_at'], 0, 16))
    : '';

$pages = cms_get_pages_admin();
$templates = cms_get_section_templates();
$recentMedia = array_slice(cms_get_recent_media(), 0, 12);

require __DIR__ . '/includes/header.php';
?>
<section class="admin-topbar">
  <div>
    <span class="admin-kicker"><i class="fas fa-laptop-code"></i> Page Builder</span>
    <h1 class="admin-page-title">Build pages with widgets</h1>
    <p class="admin-page-copy">Create service pages, standard pages, and landing pages with reusable blocks, custom HTML, SEO fields, and navigation control while keeping the current A1 template feel.</p>
  </div>
  <div class="card-actions">
    <?php if ((int) $editingPage['id'] > 0 && (string) $editingPage['status'] === 'published'): ?>
      <a href="<?= cms_e(cms_page_url((string) $editingPage['slug'], (string) $editingPage['page_type'])) ?>" target="_blank" rel="noopener" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-up-right-from-square"></i> View Live</a>
    <?php endif; ?>
  </div>
</section>

<?php if ($errorMessage !== ''): ?>
  <div class="flash flash-error"><?= cms_e($errorMessage) ?></div>
<?php endif; ?>

<section class="split-grid wide" style="display:grid;gap:20px">
  <article class="editor-card">
    <div class="toolbar">
      <div>
        <h3><?= (int) $editingPage['id'] > 0 ? 'Edit page' : 'Create a new page' ?></h3>
        <p class="muted">Use widgets like Hero, FAQ, CTA, Pricing, Service Cards, Buttons, Video, and Custom HTML.</p>
      </div>
    </div>

    <form method="post" class="form-grid">
      <input type="hidden" name="_token" value="<?= cms_e(cms_csrf_token()) ?>" />
      <input type="hidden" name="action" value="save_page" />
      <input type="hidden" name="id" value="<?= (int) $editingPage['id'] ?>" />
      <input type="hidden" id="builder_json" name="builder_json" value="<?= cms_e(cms_json_encode($editingPage['builder'] ?? [])) ?>" />

      <div class="form-grid two-col">
        <div class="field">
          <label for="title">Page title</label>
          <input id="title" type="text" name="title" value="<?= cms_e((string) $editingPage['title']) ?>" required data-slug-source />
        </div>
        <div class="field">
          <label for="slug">Slug</label>
          <input id="slug" type="text" name="slug" value="<?= cms_e((string) $editingPage['slug']) ?>" data-slug-target />
        </div>
      </div>

      <div class="form-grid two-col">
        <div class="field">
          <label for="page_type">Page type</label>
          <select id="page_type" name="page_type">
            <option value="service" <?= (string) $editingPage['page_type'] === 'service' ? 'selected' : '' ?>>Service</option>
            <option value="page" <?= (string) $editingPage['page_type'] === 'page' ? 'selected' : '' ?>>Page</option>
            <option value="landing" <?= (string) $editingPage['page_type'] === 'landing' ? 'selected' : '' ?>>Landing</option>
          </select>
        </div>
        <div class="field">
          <label for="template_key">Template style</label>
          <select id="template_key" name="template_key">
            <?php foreach (cms_page_templates() as $key => $label): ?>
              <option value="<?= cms_e($key) ?>" <?= (string) $editingPage['template_key'] === $key ? 'selected' : '' ?>><?= cms_e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-grid two-col">
        <div class="field">
          <label for="status">Status</label>
          <select id="status" name="status">
            <option value="draft" <?= (string) $editingPage['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
            <option value="published" <?= (string) $editingPage['status'] === 'published' ? 'selected' : '' ?>>Published</option>
          </select>
        </div>
        <div class="field">
          <label for="published_at">Publish date</label>
          <input id="published_at" type="datetime-local" name="published_at" value="<?= cms_e((string) $editingPage['published_at']) ?>" />
        </div>
      </div>

      <div class="form-grid two-col">
        <div class="field">
          <label for="featured_image">Featured image URL</label>
          <input id="featured_image" type="text" name="featured_image" value="<?= cms_e((string) $editingPage['featured_image']) ?>" />
        </div>
        <div class="field">
          <label for="og_image">OG image URL</label>
          <input id="og_image" type="text" name="og_image" value="<?= cms_e((string) $editingPage['og_image']) ?>" />
        </div>
      </div>

      <div class="form-grid two-col">
        <div class="checkbox-field">
          <input id="show_in_nav" type="checkbox" name="show_in_nav" value="1" <?= (int) $editingPage['show_in_nav'] === 1 ? 'checked' : '' ?> />
          <label for="show_in_nav">Show this page inside the website navigation</label>
        </div>
        <div class="field">
          <label for="nav_group">Navigation group</label>
          <input id="nav_group" type="text" name="nav_group" value="<?= cms_e((string) $editingPage['nav_group']) ?>" placeholder="services" />
        </div>
      </div>

      <div class="field">
        <label for="sort_order">Sort order</label>
        <input id="sort_order" type="number" name="sort_order" value="<?= cms_e((string) $editingPage['sort_order']) ?>" />
      </div>

      <div class="field">
        <label for="excerpt">Excerpt</label>
        <textarea id="excerpt" name="excerpt"><?= cms_e((string) $editingPage['excerpt']) ?></textarea>
      </div>

      <div class="builder-shell" data-builder-shell>
        <div class="toolbar">
          <div>
            <h3>Widgets</h3>
            <p class="muted">Add, reorder, and remove sections like a light Elementor-style builder.</p>
          </div>
          <div class="card-actions">
            <select id="widget_picker" data-widget-picker>
              <?php foreach (cms_widget_catalog() as $key => $label): ?>
                <option value="<?= cms_e($key) ?>"><?= cms_e($label) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="button" class="btn btn-primary btn-sm" data-add-widget><i class="fas fa-plus"></i> Add Widget</button>
          </div>
        </div>
        <div class="builder-list" data-builder-list></div>
      </div>

      <div class="field">
        <label for="custom_html">Page-level custom HTML <span class="field-optional">(optional)</span></label>
        <p class="helper-text" style="margin-top:-4px">Injected after the widget builder output. Use for embeds, custom sections, or anything not covered by a widget.</p>
        <div class="code-editor-wrap">
          <div class="code-editor-bar"><i class="fas fa-code"></i> HTML</div>
          <textarea id="custom_html" name="custom_html" data-codemirror="html"><?= cms_e((string) $editingPage['custom_html']) ?></textarea>
        </div>
      </div>

      <div class="form-grid two-col">
        <div class="field">
          <label for="meta_title">Meta title</label>
          <input id="meta_title" type="text" name="meta_title" value="<?= cms_e((string) $editingPage['meta_title']) ?>" />
        </div>
        <div class="field">
          <label for="canonical_url">Canonical URL</label>
          <input id="canonical_url" type="text" name="canonical_url" value="<?= cms_e((string) $editingPage['canonical_url']) ?>" />
        </div>
      </div>

      <div class="field">
        <label for="meta_description">Meta description</label>
        <textarea id="meta_description" name="meta_description" style="min-height:110px"><?= cms_e((string) $editingPage['meta_description']) ?></textarea>
      </div>

      <div class="form-grid two-col">
        <div class="field">
          <label for="schema_json">Schema JSON-LD</label>
          <div class="code-editor-wrap">
            <div class="code-editor-bar"><i class="fas fa-brackets-curly"></i> JSON</div>
            <textarea id="schema_json" name="schema_json" data-codemirror="json"><?= cms_e((string) $editingPage['schema_json']) ?></textarea>
          </div>
        </div>
        <div class="field">
          <label for="custom_css">Custom CSS</label>
          <div class="code-editor-wrap">
            <div class="code-editor-bar"><i class="fas fa-paint-brush"></i> CSS</div>
            <textarea id="custom_css" name="custom_css" data-codemirror="css"><?= cms_e((string) $editingPage['custom_css']) ?></textarea>
          </div>
        </div>
      </div>

      <div class="field">
        <label for="custom_js">Custom JavaScript</label>
        <div class="code-editor-wrap">
          <div class="code-editor-bar"><i class="fas fa-terminal"></i> JavaScript</div>
          <textarea id="custom_js" name="custom_js" data-codemirror="js"><?= cms_e((string) $editingPage['custom_js']) ?></textarea>
        </div>
      </div>

      <div class="inline-actions">
        <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Save Page</button>
      </div>
    </form>
  </article>

  <div class="content-grid" style="display:grid;gap:20px">
    <article class="table-card">
      <div class="toolbar">
        <div>
          <h3>Reusable sections</h3>
          <p class="muted">Saved in Templates. Copy their payloads into new pages from the builder panel.</p>
        </div>
        <a href="<?= cms_e(cms_url('admin/templates.php')) ?>" class="btn btn-secondary btn-sm"><i class="fas fa-layer-group"></i> Manage Templates</a>
      </div>
      <?php if ($templates === []): ?>
        <div class="empty-state">No reusable templates yet. Save FAQ, CTA, pricing, or hero blocks from the Templates screen.</div>
      <?php else: ?>
        <div class="template-list">
          <?php foreach ($templates as $template): ?>
            <article class="mini-template-card">
              <strong><?= cms_e((string) $template['name']) ?></strong>
              <p class="muted"><?= cms_e((string) ucfirst(str_replace('_', ' ', (string) $template['widget_type']))) ?></p>
              <button type="button" class="btn btn-ghost btn-sm" data-template-payload="<?= cms_e((string) $template['payload_json']) ?>" data-template-type="<?= cms_e((string) $template['widget_type']) ?>" data-add-template-widget>Use in Builder</button>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </article>

    <article class="table-card">
      <div class="toolbar">
        <div>
          <h3>Media shortcuts</h3>
          <p class="muted">Use these for featured images, hero backgrounds, and content widgets.</p>
        </div>
      </div>
      <?php if ($recentMedia === []): ?>
        <div class="empty-state">Upload files from Media first.</div>
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
                  <button type="button" class="btn btn-ghost btn-sm" data-fill-target="#featured_image" data-fill-value="<?= cms_e($mediaUrl) ?>">Use Featured</button>
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
          <h3>All dynamic pages</h3>
          <p class="muted">Edit, duplicate, or remove service and standard pages.</p>
        </div>
      </div>
      <?php if ($pages === []): ?>
        <div class="empty-state">No pages created yet.</div>
      <?php else: ?>
        <div class="table-scroll">
          <table>
            <thead>
              <tr>
                <th>Title</th>
                <th>Type</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pages as $page): ?>
                <tr>
                  <td><strong><?= cms_e((string) $page['title']) ?></strong><p><?= cms_e((string) $page['slug']) ?></p></td>
                  <td><?= cms_e(ucfirst((string) $page['page_type'])) ?></td>
                  <td><span class="status-badge status-<?= cms_e((string) $page['status']) ?>"><?= cms_e((string) $page['status']) ?></span></td>
                  <td>
                    <div class="inline-actions">
                      <a href="<?= cms_e(cms_url('admin/pages.php?edit=' . (int) $page['id'])) ?>" class="btn btn-secondary btn-sm"><i class="fas fa-pen"></i> Edit</a>
                      <?php if ((string) $page['status'] === 'published'): ?>
                        <a href="<?= cms_e(cms_page_url((string) $page['slug'], (string) $page['page_type'])) ?>" class="btn btn-ghost btn-sm" target="_blank" rel="noopener"><i class="fas fa-eye"></i> View</a>
                      <?php endif; ?>
                      <form method="post">
                        <input type="hidden" name="_token" value="<?= cms_e(cms_csrf_token()) ?>" />
                        <input type="hidden" name="action" value="duplicate_page" />
                        <input type="hidden" name="id" value="<?= (int) $page['id'] ?>" />
                        <button type="submit" class="btn btn-ghost btn-sm"><i class="fas fa-copy"></i> Duplicate</button>
                      </form>
                      <form method="post" onsubmit="return confirm('Delete this page permanently?');">
                        <input type="hidden" name="_token" value="<?= cms_e(cms_csrf_token()) ?>" />
                        <input type="hidden" name="action" value="delete_page" />
                        <input type="hidden" name="id" value="<?= (int) $page['id'] ?>" />
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

<?php
$widgetDefaults = [];
foreach (array_keys(cms_widget_catalog()) as $widgetKey) {
    $widgetDefaults[$widgetKey] = cms_default_widget_payload($widgetKey);
}
?>
<script id="cms-builder-seed" type="application/json"><?= cms_e(cms_json_encode($editingPage['builder'] ?? [])) ?></script>
<script id="cms-builder-catalog" type="application/json"><?= cms_e(cms_json_encode($widgetDefaults)) ?></script>
<?php require __DIR__ . '/includes/footer.php'; ?>
