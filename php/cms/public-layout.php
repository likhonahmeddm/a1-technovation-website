<?php
declare(strict_types=1);

function cms_public_nav(string $active = 'blog'): void
{
    $settings = cms_site_settings();
    $dynamicServicePages = cms_nav_service_pages();
    $home = cms_url('');
    $about = cms_url('pages/about');
    $services = cms_url('pages/services');
    $seo = cms_url('pages/services-seo');
    $localSeo = cms_url('pages/services-local-seo');
    $carRentalSeo = cms_url('pages/services-car-rental-seo');
    $limoSeo = cms_url('pages/services-limousine-business-seo');
    $carRepairSeo = cms_url('pages/services-car-repair-seo');
    $carDealershipSeo = cms_url('pages/services-car-dealership-seo');
    $treeSeo = cms_url('pages/services-tree-service-seo');
    $lawyerSeo = cms_url('pages/services-lawyer-seo');
    $smallBusinessSeo = cms_url('pages/services-small-business-seo');
    $webDev = cms_url('pages/services-web-dev');
    $ppc = cms_url('pages/services-ppc-ads');
    $social = cms_url('pages/services-social-media');
    $portfolio = cms_url('pages/portfolio');
    $blog = cms_blog_index_url();
    $tools = cms_url('pages/tools');
    $contact = cms_url('pages/contact');
    $whatsApp = (string) ($settings['primary_cta_url'] ?? 'https://wa.me/8801799976295');
    $ctaLabel = (string) ($settings['primary_cta_label'] ?? 'Free Consultation');

    $linkClass = static fn (string $name): string => $active === $name ? 'nav-link active' : 'nav-link';
    ?>
<nav class="navbar" id="navbar">
  <div class="container"><div class="navbar-inner">
    <a href="<?= cms_e($home) ?>" class="navbar-logo" aria-label="A1 Technovation home"><span class="logo-mark"><img src="<?= cms_e(cms_url('assets/images/A1Technovation-Dark-Background.png')) ?>" alt="A1 Technovation" class="logo-brand logo-brand-dark" /><img src="<?= cms_e(cms_url('assets/images/A1Technovation-Light-Background.png')) ?>" alt="A1 Technovation" class="logo-brand logo-brand-light" /></span></a>
    <nav class="navbar-nav">
      <a href="<?= cms_e($home) ?>" class="<?= cms_e($linkClass('home')) ?>">Home</a><a href="<?= cms_e($about) ?>" class="<?= cms_e($linkClass('about')) ?>">About</a>
      <div class="nav-item"><a href="<?= cms_e($services) ?>" class="<?= cms_e($linkClass('services')) ?>">Services <i class="fas fa-chevron-down" style="font-size:.7em;opacity:.7"></i></a>
        <div class="dropdown">
          <div class="dropdown-group">
            <a href="<?= cms_e($seo) ?>" class="dropdown-item dropdown-parent-link"><div class="dd-icon"><i class="fas fa-search"></i></div><div class="dd-body"><span class="dd-title">SEO Services</span><span class="dd-desc">Rank higher, get found</span></div><i class="fas fa-chevron-right dd-caret" aria-hidden="true"></i></a>
            <div class="dropdown-submenu">
              <div class="dropdown-submenu-inner">
                <a href="<?= cms_e($localSeo) ?>" class="dropdown-subitem">Local SEO Services</a>
                <a href="<?= cms_e($carRentalSeo) ?>" class="dropdown-subitem">Car Rental SEO Service</a>
                <a href="<?= cms_e($limoSeo) ?>" class="dropdown-subitem">Limousine Business SEO Service</a>
                <a href="<?= cms_e($carRepairSeo) ?>" class="dropdown-subitem">Car Repair SEO Service</a>
                <a href="<?= cms_e($carDealershipSeo) ?>" class="dropdown-subitem">Car Dealership SEO Service</a>
                <a href="<?= cms_e($treeSeo) ?>" class="dropdown-subitem">Tree Service SEO</a>
                <a href="<?= cms_e($lawyerSeo) ?>" class="dropdown-subitem">Lawyer SEO Service</a>
                <a href="<?= cms_e($smallBusinessSeo) ?>" class="dropdown-subitem">Small Business SEO Service</a>
                <?php foreach ($dynamicServicePages as $servicePage): ?>
                  <a href="<?= cms_e(cms_page_url((string) $servicePage['slug'], (string) $servicePage['page_type'])) ?>" class="dropdown-subitem"><?= cms_e((string) $servicePage['title']) ?></a>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          <a href="<?= cms_e($webDev) ?>" class="dropdown-item"><div class="dd-icon"><i class="fas fa-code"></i></div><div class="dd-body"><span class="dd-title">Web Design &amp; Dev</span><span class="dd-desc">Convert visitors to clients</span></div></a>
          <a href="<?= cms_e($ppc) ?>" class="dropdown-item"><div class="dd-icon"><i class="fas fa-bullseye"></i></div><div class="dd-body"><span class="dd-title">PPC Advertising</span><span class="dd-desc">Maximum ROI campaigns</span></div></a>
          <a href="<?= cms_e($social) ?>" class="dropdown-item"><div class="dd-icon"><i class="fas fa-share-nodes"></i></div><div class="dd-body"><span class="dd-title">Social Media</span><span class="dd-desc">Build your community</span></div></a>
        </div>
      </div>
      <a href="<?= cms_e($portfolio) ?>" class="<?= cms_e($linkClass('work')) ?>">Work</a><a href="<?= cms_e($blog) ?>" class="<?= cms_e($linkClass('blog')) ?>">Blog</a><a href="<?= cms_e($tools) ?>" class="<?= cms_e($linkClass('tools')) ?>">Tools</a><a href="<?= cms_e($contact) ?>" class="<?= cms_e($linkClass('contact')) ?>">Contact</a>
    </nav>
    <div class="navbar-right">
      <a href="<?= cms_e($whatsApp) ?>" class="btn btn-primary btn-sm nav-cta" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i> <?= cms_e($ctaLabel) ?></a>
      <button class="mobile-toggle" id="mobileToggle" aria-label="Open menu" aria-controls="mobileMenu" aria-expanded="false"><span></span><span></span><span></span></button>
    </div>
  </div></div>
</nav>
<div class="mobile-menu" id="mobileMenu">
  <a href="<?= cms_e($home) ?>" class="mobile-menu-link">Home</a><a href="<?= cms_e($about) ?>" class="mobile-menu-link">About</a>
  <div class="mobile-menu-group"><a href="<?= cms_e($services) ?>" class="mobile-menu-link">Services</a><div class="mobile-submenu"><a href="<?= cms_e($seo) ?>" class="mobile-submenu-link">SEO Services</a><div class="mobile-submenu-nested"><a href="<?= cms_e($localSeo) ?>" class="mobile-submenu-link mobile-submenu-sublink">Local SEO Services</a><a href="<?= cms_e($carRentalSeo) ?>" class="mobile-submenu-link mobile-submenu-sublink">Car Rental SEO Service</a><a href="<?= cms_e($limoSeo) ?>" class="mobile-submenu-link mobile-submenu-sublink">Limousine Business SEO Service</a><a href="<?= cms_e($carRepairSeo) ?>" class="mobile-submenu-link mobile-submenu-sublink">Car Repair SEO Service</a><a href="<?= cms_e($carDealershipSeo) ?>" class="mobile-submenu-link mobile-submenu-sublink">Car Dealership SEO Service</a><a href="<?= cms_e($treeSeo) ?>" class="mobile-submenu-link mobile-submenu-sublink">Tree Service SEO</a><a href="<?= cms_e($lawyerSeo) ?>" class="mobile-submenu-link mobile-submenu-sublink">Lawyer SEO Service</a><a href="<?= cms_e($smallBusinessSeo) ?>" class="mobile-submenu-link mobile-submenu-sublink">Small Business SEO Service</a><?php foreach ($dynamicServicePages as $servicePage): ?><a href="<?= cms_e(cms_page_url((string) $servicePage['slug'], (string) $servicePage['page_type'])) ?>" class="mobile-submenu-link mobile-submenu-sublink"><?= cms_e((string) $servicePage['title']) ?></a><?php endforeach; ?></div><a href="<?= cms_e($webDev) ?>" class="mobile-submenu-link">Web Design &amp; Development</a><a href="<?= cms_e($ppc) ?>" class="mobile-submenu-link">PPC Advertising</a><a href="<?= cms_e($social) ?>" class="mobile-submenu-link">Social Media Marketing</a></div></div><a href="<?= cms_e($portfolio) ?>" class="mobile-menu-link">Work</a>
  <a href="<?= cms_e($blog) ?>" class="mobile-menu-link">Blog</a><a href="<?= cms_e($tools) ?>" class="mobile-menu-link">Tools</a><a href="<?= cms_e($contact) ?>" class="mobile-menu-link">Contact</a>
  <div class="mobile-menu-footer"><a href="<?= cms_e($whatsApp) ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener" style="justify-content:center"><i class="fab fa-whatsapp"></i> <?= cms_e($ctaLabel) ?></a><p class="mobile-menu-note">Explore our latest insights or reach out for tailored growth support.</p></div>
</div>
    <?php
}

function cms_public_footer(): void
{
    $settings = cms_site_settings();
    ?>
<footer class="footer"><div class="container">
  <div class="footer-top">
    <div class="footer-brand"><a href="<?= cms_e(cms_url('')) ?>" class="footer-logo" aria-label="A1 Technovation home"><img src="<?= cms_e(cms_url('assets/images/A1Technovation-Dark-Background.png')) ?>" alt="A1 Technovation" class="footer-logo-image" /></a><p><?= cms_e((string) ($settings['footer_text'] ?? "Bangladesh's fastest-growing digital marketing agency.")) ?></p><div class="footer-socials"><a href="https://www.facebook.com/a1technovation/" class="f-social" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a><a href="https://www.instagram.com/a1technovation/?hl=en" class="f-social" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a><a href="https://www.linkedin.com/company/92598277/" class="f-social" target="_blank" rel="noopener"><i class="fab fa-linkedin-in"></i></a><a href="<?= cms_e((string) ($settings['whatsapp_url'] ?? 'https://wa.me/8801799976295')) ?>" class="f-social" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i></a><a href="https://t.me/likhonahmedseo" class="f-social" target="_blank" rel="noopener"><i class="fab fa-telegram"></i></a></div></div>
    <div class="footer-col"><h6>Services</h6><div class="footer-links"><a href="<?= cms_e(cms_url('pages/services-seo')) ?>" class="f-link"><i class="fas fa-chevron-right" style="font-size:.625rem"></i> SEO Services</a><a href="<?= cms_e(cms_url('pages/services-web-dev')) ?>" class="f-link"><i class="fas fa-chevron-right" style="font-size:.625rem"></i> Web Design</a><a href="<?= cms_e(cms_url('pages/services-ppc-ads')) ?>" class="f-link"><i class="fas fa-chevron-right" style="font-size:.625rem"></i> PPC Ads</a><a href="<?= cms_e(cms_url('pages/services-social-media')) ?>" class="f-link"><i class="fas fa-chevron-right" style="font-size:.625rem"></i> Social Media</a></div></div>
    <div class="footer-col"><h6>Company</h6><div class="footer-links"><a href="<?= cms_e(cms_url('pages/about')) ?>" class="f-link"><i class="fas fa-chevron-right" style="font-size:.625rem"></i> About Us</a><a href="<?= cms_e(cms_url('pages/portfolio')) ?>" class="f-link"><i class="fas fa-chevron-right" style="font-size:.625rem"></i> Our Work</a><a href="<?= cms_e(cms_blog_index_url()) ?>" class="f-link"><i class="fas fa-chevron-right" style="font-size:.625rem"></i> Blog</a><a href="<?= cms_e(cms_url('pages/contact')) ?>" class="f-link"><i class="fas fa-chevron-right" style="font-size:.625rem"></i> Contact</a></div></div>
    <div class="footer-col"><h6>Contact</h6><div class="f-contact-item"><i class="fas fa-phone"></i><a href="tel:<?= cms_e((string) ($settings['phone'] ?? '+880 1799-976295')) ?>"><?= cms_e((string) ($settings['phone'] ?? '+880 1799-976295')) ?></a></div><div class="f-contact-item"><i class="fas fa-envelope"></i><a href="mailto:<?= cms_e((string) ($settings['email'] ?? 'info.a1technovation@gmail.com')) ?>"><?= cms_e((string) ($settings['email'] ?? 'info.a1technovation@gmail.com')) ?></a></div></div>
  </div>
  <div class="footer-bottom"><p class="f-copy">© <?= date('Y') ?> A1 Technovation. All rights reserved.</p><div class="f-bottom-links"><a href="<?= cms_e(cms_url('pages/privacy')) ?>" class="f-bl">Privacy Policy</a><a href="<?= cms_e(cms_url('pages/terms')) ?>" class="f-bl">Terms and Conditions</a></div></div>
</div></footer>
<button class="scroll-top" id="scrollTop" aria-label="Scroll to top"><i class="fas fa-arrow-up"></i></button>
<script src="<?= cms_e(cms_url('js/main.js?v=20260504')) ?>"></script>
    <?php
}
