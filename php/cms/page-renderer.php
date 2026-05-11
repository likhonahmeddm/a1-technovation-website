<?php
declare(strict_types=1);

function cms_render_widget_html(array $widget): string
{
    $type = (string) ($widget['widget_type'] ?? '');
    $payload = is_array($widget['payload'] ?? null) ? $widget['payload'] : [];

    return match ($type) {
        'hero' => cms_widget_hero($payload),
        'text' => cms_widget_text($payload),
        'image' => cms_widget_image($payload),
        'video' => cms_widget_video($payload),
        'cta' => cms_widget_cta($payload),
        'faq' => cms_widget_faq($payload),
        'pricing' => cms_widget_pricing($payload),
        'testimonials' => cms_widget_testimonials($payload),
        'stats' => cms_widget_stats($payload),
        'service_cards' => cms_widget_service_cards($payload),
        'buttons' => cms_widget_buttons($payload),
        'custom_html' => cms_widget_custom_html($payload),
        default => '',
    };
}

function cms_render_widget_list(array $widgets): string
{
    $html = '';
    foreach ($widgets as $widget) {
        if (is_array($widget)) {
            $html .= cms_render_widget_html($widget);
        }
    }
    return $html;
}

function cms_widget_hero(array $p): string
{
    $bg = (string) ($p['background_image'] ?? '');
    $style = $bg !== '' ? ' style="background-image:linear-gradient(rgba(6,11,24,.75),rgba(6,11,24,.75)),url(\'' . cms_e(cms_media_url($bg)) . '\')"' : '';
    return '<section class="page-hero cms-page-hero"' . $style . '><div class="hero-bg"><div class="hero-grid"></div><div class="hero-glow-1"></div><div class="hero-glow-2"></div></div><div class="container"><div class="page-hero-inner"><span class="label label-light label-dot">' . cms_e((string) ($p['eyebrow'] ?? '')) . '</span><h1>' . cms_e((string) ($p['heading'] ?? '')) . ' <span class="text-grad-hero">' . cms_e((string) ($p['highlight'] ?? '')) . '</span></h1><p>' . cms_e((string) ($p['text'] ?? '')) . '</p><div class="cms-btn-row"><a href="' . cms_e(cms_media_url((string) ($p['primary_url'] ?? '#'))) . '" class="btn btn-primary">' . cms_e((string) ($p['primary_label'] ?? 'Get Started')) . '</a><a href="' . cms_e(cms_media_url((string) ($p['secondary_url'] ?? '#'))) . '" class="btn btn-ghost">' . cms_e((string) ($p['secondary_label'] ?? 'Learn More')) . '</a></div></div></div></section>';
}

function cms_widget_text(array $p): string
{
    return '<section class="section"><div class="container"><div class="cms-builder-panel"><div class="section-header reveal"><h2>' . cms_e((string) ($p['heading'] ?? '')) . '</h2></div><div class="cms-rich-text">' . (string) ($p['body'] ?? '') . '</div></div></div></section>';
}

function cms_widget_image(array $p): string
{
    return '<section class="section"><div class="container"><div class="cms-media-panel"><h2>' . cms_e((string) ($p['heading'] ?? '')) . '</h2><img src="' . cms_e(cms_media_url((string) ($p['image_url'] ?? ''))) . '" alt="' . cms_e((string) ($p['alt'] ?? '')) . '" /><p>' . cms_e((string) ($p['caption'] ?? '')) . '</p></div></div></section>';
}

function cms_widget_video(array $p): string
{
    return '<section class="section"><div class="container"><div class="cms-media-panel"><h2>' . cms_e((string) ($p['heading'] ?? '')) . '</h2><video src="' . cms_e(cms_media_url((string) ($p['video_url'] ?? ''))) . '" poster="' . cms_e(cms_media_url((string) ($p['poster_url'] ?? ''))) . '" controls></video><p>' . cms_e((string) ($p['caption'] ?? '')) . '</p></div></div></section>';
}

function cms_widget_cta(array $p): string
{
    return '<section class="section"><div class="container"><div class="newsletter-card reveal cms-cta-panel"><div class="newsletter-grid"></div><div class="newsletter-content"><span class="label label-light label-dot">Call to Action</span><h2 style="color:#fff;margin-bottom:16px">' . cms_e((string) ($p['heading'] ?? '')) . '</h2><p style="color:rgba(255,255,255,.76);margin-bottom:28px">' . cms_e((string) ($p['text'] ?? '')) . '</p><a href="' . cms_e(cms_media_url((string) ($p['button_url'] ?? '#'))) . '" class="btn btn-secondary">' . cms_e((string) ($p['button_label'] ?? 'Learn More')) . '</a></div></div></div></section>';
}

function cms_widget_faq(array $p): string
{
    $items = is_array($p['items'] ?? null) ? $p['items'] : [];
    $cards = '';
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $cards .= '<article class="faq-item"><h4>' . cms_e((string) ($item['question'] ?? '')) . '</h4><p>' . cms_e((string) ($item['answer'] ?? '')) . '</p></article>';
    }
    return '<section class="section section-gray"><div class="container"><div class="section-header reveal"><h2>' . cms_e((string) ($p['heading'] ?? '')) . '</h2></div><div class="cms-grid-two">' . $cards . '</div></div></section>';
}

function cms_widget_pricing(array $p): string
{
    $items = is_array($p['items'] ?? null) ? $p['items'] : [];
    $cards = '';
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $features = '';
        foreach ((array) ($item['features'] ?? []) as $feature) {
            $features .= '<div class="p-feat"><i class="fas fa-check"></i> ' . cms_e((string) $feature) . '</div>';
        }
        $cards .= '<article class="pricing-card cms-simple-pricing"><h4>' . cms_e((string) ($item['name'] ?? '')) . '</h4><div class="price">' . cms_e((string) ($item['price'] ?? '')) . '</div><p>' . cms_e((string) ($item['details'] ?? '')) . '</p><div class="pricing-features">' . $features . '</div></article>';
    }
    return '<section class="section"><div class="container"><div class="section-header reveal"><h2>' . cms_e((string) ($p['heading'] ?? '')) . '</h2></div><div class="pricing-grid">' . $cards . '</div></div></section>';
}

function cms_widget_testimonials(array $p): string
{
    $items = is_array($p['items'] ?? null) ? $p['items'] : [];
    $cards = '';
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $cards .= '<article class="testimonial-card"><p>"' . cms_e((string) ($item['quote'] ?? '')) . '"</p><strong>' . cms_e((string) ($item['name'] ?? '')) . '</strong><span>' . cms_e((string) ($item['role'] ?? '')) . '</span></article>';
    }
    return '<section class="section section-gray"><div class="container"><div class="section-header reveal"><h2>' . cms_e((string) ($p['heading'] ?? '')) . '</h2></div><div class="cms-grid-three">' . $cards . '</div></div></section>';
}

function cms_widget_stats(array $p): string
{
    $items = is_array($p['items'] ?? null) ? $p['items'] : [];
    $cards = '';
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $cards .= '<article class="stat-card"><strong>' . cms_e((string) ($item['number'] ?? '')) . '</strong><p>' . cms_e((string) ($item['label'] ?? '')) . '</p></article>';
    }
    return '<section class="section"><div class="container"><div class="section-header reveal"><h2>' . cms_e((string) ($p['heading'] ?? '')) . '</h2></div><div class="cms-grid-three">' . $cards . '</div></div></section>';
}

function cms_widget_service_cards(array $p): string
{
    $items = is_array($p['items'] ?? null) ? $p['items'] : [];
    $cards = '';
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $cards .= '<article class="feat-card reveal"><h4>' . cms_e((string) ($item['title'] ?? '')) . '</h4><p>' . cms_e((string) ($item['text'] ?? '')) . '</p></article>';
    }
    return '<section class="section"><div class="container"><div class="section-header reveal"><h2>' . cms_e((string) ($p['heading'] ?? '')) . '</h2></div><div class="services-grid">' . $cards . '</div></div></section>';
}

function cms_widget_buttons(array $p): string
{
    $items = is_array($p['items'] ?? null) ? $p['items'] : [];
    $buttons = '';
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $buttons .= '<a href="' . cms_e(cms_media_url((string) ($item['url'] ?? '#'))) . '" class="btn btn-outline">' . cms_e((string) ($item['label'] ?? 'Open')) . '</a>';
    }
    return '<section class="section"><div class="container"><div class="section-header reveal"><h2>' . cms_e((string) ($p['heading'] ?? '')) . '</h2></div><div class="cms-btn-row">' . $buttons . '</div></div></section>';
}

function cms_widget_custom_html(array $p): string
{
    return '<section class="section"><div class="container">' . (string) ($p['html'] ?? '') . '</div></section>';
}
