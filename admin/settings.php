<?php
declare(strict_types=1);

require_once __DIR__ . '/../php/cms/bootstrap.php';

cms_require_login();

$pageTitle = 'Settings';
$activeNav = 'settings';
$errorMessage = '';
$settings = cms_site_settings();

if (cms_is_post()) {
    try {
        cms_require_csrf();
        $settings = [
            'company_name' => trim((string) cms_post('company_name')),
            'phone' => trim((string) cms_post('phone')),
            'email' => trim((string) cms_post('email')),
            'whatsapp_url' => trim((string) cms_post('whatsapp_url')),
            'footer_text' => trim((string) cms_post('footer_text')),
            'primary_cta_label' => trim((string) cms_post('primary_cta_label')),
            'primary_cta_url' => trim((string) cms_post('primary_cta_url')),
        ];
        cms_save_site_settings($settings);
        cms_flash_set('success', 'Site settings updated.');
        cms_redirect('admin/settings.php');
    } catch (Throwable $exception) {
        $errorMessage = $exception->getMessage();
    }
}

require __DIR__ . '/includes/header.php';
?>
<section class="admin-topbar">
  <div>
    <span class="admin-kicker"><i class="fas fa-sliders"></i> Global Settings</span>
    <h1 class="admin-page-title">Update site-wide contact and CTA content</h1>
    <p class="admin-page-copy">These values feed the dynamic public templates so the same company info and CTA links stay consistent across blog and page-builder output.</p>
  </div>
</section>
<?php if ($errorMessage !== ''): ?>
  <div class="flash flash-error"><?= cms_e($errorMessage) ?></div>
<?php endif; ?>
<section class="admin-card">
  <form method="post" class="form-grid">
    <input type="hidden" name="_token" value="<?= cms_e(cms_csrf_token()) ?>" />
    <div class="form-grid two-col">
      <div class="field">
        <label for="company_name">Company name</label>
        <input id="company_name" type="text" name="company_name" value="<?= cms_e((string) $settings['company_name']) ?>" />
      </div>
      <div class="field">
        <label for="phone">Phone</label>
        <input id="phone" type="text" name="phone" value="<?= cms_e((string) $settings['phone']) ?>" />
      </div>
    </div>
    <div class="form-grid two-col">
      <div class="field">
        <label for="email">Email</label>
        <input id="email" type="text" name="email" value="<?= cms_e((string) $settings['email']) ?>" />
      </div>
      <div class="field">
        <label for="whatsapp_url">WhatsApp URL</label>
        <input id="whatsapp_url" type="text" name="whatsapp_url" value="<?= cms_e((string) $settings['whatsapp_url']) ?>" />
      </div>
    </div>
    <div class="field">
      <label for="footer_text">Footer text</label>
      <textarea id="footer_text" name="footer_text"><?= cms_e((string) $settings['footer_text']) ?></textarea>
    </div>
    <div class="form-grid two-col">
      <div class="field">
        <label for="primary_cta_label">Primary CTA label</label>
        <input id="primary_cta_label" type="text" name="primary_cta_label" value="<?= cms_e((string) $settings['primary_cta_label']) ?>" />
      </div>
      <div class="field">
        <label for="primary_cta_url">Primary CTA URL</label>
        <input id="primary_cta_url" type="text" name="primary_cta_url" value="<?= cms_e((string) $settings['primary_cta_url']) ?>" />
      </div>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Save Settings</button>
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
