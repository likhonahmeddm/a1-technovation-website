<?php
declare(strict_types=1);

require_once __DIR__ . '/../php/cms/bootstrap.php';

cms_require_login();

$pageTitle = 'Templates';
$activeNav = 'templates';
$currentAdmin = cms_current_admin();
$errorMessage = '';

if (cms_is_post()) {
    try {
        cms_require_csrf();
        $action = (string) cms_post('action');
        if ($action === 'save_template') {
            cms_save_section_template($_POST, (int) ($currentAdmin['id'] ?? 0));
            cms_flash_set('success', 'Reusable section saved.');
            cms_redirect('admin/templates.php');
        }
        if ($action === 'delete_template') {
            cms_delete_section_template((int) cms_post('id'));
            cms_flash_set('success', 'Template deleted.');
            cms_redirect('admin/templates.php');
        }
    } catch (Throwable $exception) {
        $errorMessage = $exception->getMessage();
    }
}

$templates = cms_get_section_templates();

require __DIR__ . '/includes/header.php';
?>
<section class="admin-topbar">
  <div>
    <span class="admin-kicker"><i class="fas fa-layer-group"></i> Reusable Templates</span>
    <h1 class="admin-page-title">Save widgets for repeat use</h1>
    <p class="admin-page-copy">Store FAQ blocks, CTA sections, hero content, pricing cards, and other widget payloads so future service pages can reuse them fast.</p>
  </div>
</section>
<?php if ($errorMessage !== ''): ?>
  <div class="flash flash-error"><?= cms_e($errorMessage) ?></div>
<?php endif; ?>
<section class="split-grid wide" style="display:grid;gap:20px">
  <article class="admin-card">
    <h3>Create reusable section</h3>
    <form method="post" class="form-grid">
      <input type="hidden" name="_token" value="<?= cms_e(cms_csrf_token()) ?>" />
      <input type="hidden" name="action" value="save_template" />
      <div class="field">
        <label for="name">Template name</label>
        <input id="name" type="text" name="name" placeholder="SEO FAQ block" required />
      </div>
      <div class="field">
        <label for="widget_type">Widget type</label>
        <select id="widget_type" name="widget_type">
          <?php foreach (cms_widget_catalog() as $key => $label): ?>
            <option value="<?= cms_e($key) ?>"><?= cms_e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="payload_json">Payload JSON</label>
        <textarea id="payload_json" name="payload_json" style="min-height:280px"><?= cms_e(cms_json_encode(cms_default_widget_payload('faq'))) ?></textarea>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Save Template</button>
    </form>
  </article>
  <article class="table-card">
    <h3>Saved templates</h3>
    <?php if ($templates === []): ?>
      <div class="empty-state">No reusable sections saved yet.</div>
    <?php else: ?>
      <div class="template-list">
        <?php foreach ($templates as $template): ?>
          <article class="template-card">
            <strong><?= cms_e((string) $template['name']) ?></strong>
            <p class="muted"><?= cms_e((string) ucfirst(str_replace('_', ' ', (string) $template['widget_type']))) ?></p>
            <textarea readonly style="min-height:180px"><?= cms_e((string) $template['payload_json']) ?></textarea>
            <form method="post" onsubmit="return confirm('Delete this reusable section?');">
              <input type="hidden" name="_token" value="<?= cms_e(cms_csrf_token()) ?>" />
              <input type="hidden" name="action" value="delete_template" />
              <input type="hidden" name="id" value="<?= (int) $template['id'] ?>" />
              <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete</button>
            </form>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </article>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
