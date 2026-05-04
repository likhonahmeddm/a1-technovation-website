<?php
declare(strict_types=1);

function cms_is_absolute_url(?string $value): bool
{
    if (!is_string($value) || $value === '') {
        return false;
    }

    return (bool) preg_match('#^https?://#i', $value);
}

function cms_url(string $path = ''): string
{
    $baseUrl = rtrim((string) cms_config('app.base_url', ''), '/');
    $normalized = ltrim($path, '/');

    if ($baseUrl !== '') {
        return $normalized === '' ? $baseUrl : $baseUrl . '/' . $normalized;
    }

    return $normalized === '' ? '/' : '/' . $normalized;
}

function cms_admin_url(string $path = ''): string
{
    $normalized = ltrim($path, '/');

    return cms_url($normalized === '' ? 'admin' : 'admin/' . $normalized);
}

function cms_blog_index_url(): string
{
    return cms_url('blog');
}

function cms_blog_post_url(string $slug): string
{
    return cms_url('blog/' . rawurlencode($slug));
}

function cms_page_url(string $slug, string $pageType = 'page'): string
{
    $prefix = match ($pageType) {
        'service' => 'services',
        'landing' => 'landing',
        default => 'page',
    };

    return cms_url($prefix . '/' . rawurlencode($slug));
}

function cms_redirect(string $path): never
{
    $location = cms_is_absolute_url($path) ? $path : cms_url($path);
    header('Location: ' . $location);
    exit;
}

function cms_e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function cms_post(string $key, mixed $default = ''): mixed
{
    return $_POST[$key] ?? $default;
}

function cms_query(string $key, mixed $default = ''): mixed
{
    return $_GET[$key] ?? $default;
}

function cms_current_path(): string
{
    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
    $path = parse_url($uri, PHP_URL_PATH);

    return is_string($path) && $path !== '' ? $path : '/';
}

function cms_blog_slug_from_request(): string
{
    $slug = trim((string) cms_query('slug'));

    if ($slug !== '') {
        return $slug;
    }

    $path = trim(cms_current_path(), '/');

    if (preg_match('#^blog/([^/]+)$#', $path, $matches) === 1) {
        return urldecode($matches[1]);
    }

    return '';
}

function cms_page_request_context(): array
{
    $path = trim(cms_current_path(), '/');
    if (preg_match('#^(page|services|landing)/([^/]+)$#', $path, $matches) === 1) {
        return [
            'prefix' => $matches[1],
            'slug' => urldecode($matches[2]),
        ];
    }

    return [
        'prefix' => '',
        'slug' => trim((string) cms_query('slug')),
    ];
}

function cms_query_int(string $key, int $default = 0): int
{
    return max(0, (int) cms_query($key, $default));
}

function cms_is_post(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function cms_flash_set(string $type, string $message): void
{
    $_SESSION['cms_flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function cms_flash_get(): ?array
{
    if (!isset($_SESSION['cms_flash']) || !is_array($_SESSION['cms_flash'])) {
        return null;
    }

    $flash = $_SESSION['cms_flash'];
    unset($_SESSION['cms_flash']);

    return $flash;
}

function cms_csrf_token(): string
{
    if (!isset($_SESSION['cms_csrf_token']) || !is_string($_SESSION['cms_csrf_token'])) {
        $_SESSION['cms_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['cms_csrf_token'];
}

function cms_require_csrf(): void
{
    $submitted = (string) cms_post('_token', '');
    $stored = $_SESSION['cms_csrf_token'] ?? '';

    if (!is_string($stored) || $stored === '' || !hash_equals($stored, $submitted)) {
        throw new RuntimeException('Your form session expired. Refresh the page and try again.');
    }
}

function cms_make_slug(string $value): string
{
    $normalized = strtolower(trim($value));
    $normalized = preg_replace('/[^a-z0-9]+/i', '-', $normalized) ?? '';
    $normalized = trim($normalized, '-');

    return $normalized !== '' ? $normalized : 'post';
}

function cms_post_slug_exists(string $slug, ?int $ignoreId = null): bool
{
    if (!cms_table_exists('blog_posts')) {
        return false;
    }

    $sql = 'SELECT COUNT(*) FROM blog_posts WHERE slug = :slug';
    $params = ['slug' => $slug];

    if ($ignoreId !== null) {
        $sql .= ' AND id != :id';
        $params['id'] = $ignoreId;
    }

    $statement = cms_db()->prepare($sql);
    $statement->execute($params);

    return (int) $statement->fetchColumn() > 0;
}

function cms_unique_post_slug(string $title, ?int $ignoreId = null, ?string $provided = null): string
{
    $base = cms_make_slug($provided !== null && $provided !== '' ? $provided : $title);
    $slug = $base;
    $suffix = 2;

    while (cms_post_slug_exists($slug, $ignoreId)) {
        $slug = $base . '-' . $suffix;
        $suffix++;
    }

    return $slug;
}

function cms_clean_html(string $html): string
{
    $cleaned = preg_replace('#<script\b[^>]*>(.*?)</script>#is', '', $html) ?? $html;
    $cleaned = preg_replace('#\son[a-z]+\s*=\s*("|\').*?\1#is', '', $cleaned) ?? $cleaned;
    $cleaned = preg_replace('#\s(href|src)\s*=\s*("|\')\s*javascript:.*?\2#is', '', $cleaned) ?? $cleaned;

    return trim($cleaned);
}

function cms_excerpt_from_html(string $html, int $length = 180): string
{
    $text = trim(html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8'));
    $text = preg_replace('/\s+/', ' ', $text) ?? $text;

    if (mb_strlen($text) <= $length) {
        return $text;
    }

    return rtrim(mb_substr($text, 0, $length - 1)) . '…';
}

function cms_normalize_tags(string $tags): string
{
    $parts = array_filter(array_map(
        static fn (string $item): string => trim($item),
        explode(',', $tags)
    ));

    return implode(', ', array_values(array_unique($parts)));
}

function cms_json_decode_array(?string $json, array $fallback = []): array
{
    if (!is_string($json) || trim($json) === '') {
        return $fallback;
    }

    $decoded = json_decode($json, true);

    return is_array($decoded) ? $decoded : $fallback;
}

function cms_json_encode(mixed $value): string
{
    return (string) json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

function cms_format_date(?string $value, string $format = 'M j, Y'): string
{
    if (!$value) {
        return '';
    }

    try {
        return (new DateTimeImmutable($value))->format($format);
    } catch (Throwable) {
        return '';
    }
}

function cms_normalize_datetime_input(string $value): ?string
{
    $value = trim($value);

    if ($value === '') {
        return null;
    }

    try {
        return (new DateTimeImmutable($value))->format('Y-m-d H:i:s');
    } catch (Throwable) {
        return null;
    }
}

function cms_media_disk_dir(): string
{
    return cms_root((string) cms_config('app.media_path', 'assets/uploads/media'));
}

function cms_media_web_path(): string
{
    return trim((string) cms_config('app.media_path', 'assets/uploads/media'), '/');
}

function cms_ensure_media_dir(): string
{
    $directory = cms_media_disk_dir();

    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('Unable to create the media upload directory.');
    }

    return $directory;
}

function cms_media_url(?string $path): string
{
    if ($path === null || $path === '') {
        return '';
    }

    if (cms_is_absolute_url($path)) {
        return $path;
    }

    return cms_url(str_replace('\\', '/', ltrim($path, '/')));
}

function cms_allowed_media_types(): array
{
    return [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'image/svg+xml',
        'video/mp4',
        'video/webm',
        'application/pdf',
    ];
}

function cms_upload_media(array $file, int $adminId, string $title = '', string $altText = ''): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Choose a file before uploading.');
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');

    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        throw new RuntimeException('The uploaded file could not be verified.');
    }

    $mimeType = (string) mime_content_type($tmpName);

    if (!in_array($mimeType, cms_allowed_media_types(), true)) {
        throw new RuntimeException('Unsupported media type. Upload JPG, PNG, WebP, GIF, SVG, MP4, WebM, or PDF files.');
    }

    $directory = cms_ensure_media_dir();
    $originalName = (string) ($file['name'] ?? 'upload');
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $safeBase = cms_make_slug(pathinfo($originalName, PATHINFO_FILENAME));
    $storedName = $safeBase . '-' . bin2hex(random_bytes(6)) . ($extension !== '' ? '.' . $extension : '');
    $targetPath = $directory . DIRECTORY_SEPARATOR . $storedName;

    if (!move_uploaded_file($tmpName, $targetPath)) {
        throw new RuntimeException('The uploaded file could not be moved into the media library.');
    }

    $relativePath = cms_media_web_path() . '/' . $storedName;
    $statement = cms_db()->prepare(
        'INSERT INTO media_files (original_name, stored_name, title, alt_text, file_path, mime_type, file_size, uploaded_by)
         VALUES (:original_name, :stored_name, :title, :alt_text, :file_path, :mime_type, :file_size, :uploaded_by)'
    );
    $statement->execute([
        'original_name' => $originalName,
        'stored_name' => $storedName,
        'title' => trim($title) !== '' ? trim($title) : pathinfo($originalName, PATHINFO_FILENAME),
        'alt_text' => trim($altText),
        'file_path' => $relativePath,
        'mime_type' => $mimeType,
        'file_size' => (int) ($file['size'] ?? 0),
        'uploaded_by' => $adminId,
    ]);

    return cms_get_media((int) cms_db()->lastInsertId()) ?? [];
}

function cms_get_media(int $id): ?array
{
    if (!cms_table_exists('media_files')) {
        return null;
    }

    $statement = cms_db()->prepare('SELECT * FROM media_files WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $id]);
    $media = $statement->fetch();

    return is_array($media) ? $media : null;
}

function cms_get_recent_media(int $limit = 24): array
{
    if (!cms_table_exists('media_files')) {
        return [];
    }

    $limit = max(1, min($limit, 100));
    $statement = cms_db()->query('SELECT * FROM media_files ORDER BY created_at DESC LIMIT ' . $limit);

    return $statement->fetchAll() ?: [];
}

function cms_delete_media(int $id): void
{
    $media = cms_get_media($id);

    if (!$media) {
        return;
    }

    $absolutePath = cms_root((string) $media['file_path']);

    if (is_file($absolutePath)) {
        @unlink($absolutePath);
    }

    $statement = cms_db()->prepare('DELETE FROM media_files WHERE id = :id');
    $statement->execute(['id' => $id]);
}

function cms_get_post(int $id): ?array
{
    if (!cms_table_exists('blog_posts')) {
        return null;
    }

    $statement = cms_db()->prepare('SELECT * FROM blog_posts WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $id]);
    $post = $statement->fetch();

    return is_array($post) ? $post : null;
}

function cms_get_posts_admin(): array
{
    if (!cms_table_exists('blog_posts')) {
        return [];
    }

    $sql = 'SELECT bp.*, au.full_name AS admin_name
            FROM blog_posts bp
            LEFT JOIN admin_users au ON au.id = bp.updated_by
            ORDER BY COALESCE(bp.published_at, bp.updated_at) DESC';

    return cms_db()->query($sql)->fetchAll() ?: [];
}

function cms_save_post(array $input, int $adminId): int
{
    $id = isset($input['id']) ? max(0, (int) $input['id']) : 0;
    $title = trim((string) ($input['title'] ?? ''));
    $status = (string) ($input['status'] ?? 'draft');
    $providedSlug = trim((string) ($input['slug'] ?? ''));
    $contentHtml = cms_clean_html((string) ($input['content_html'] ?? ''));
    $excerpt = trim((string) ($input['excerpt'] ?? ''));

    if ($title === '') {
        throw new RuntimeException('Post title is required.');
    }

    if ($contentHtml === '') {
        throw new RuntimeException('Post content is required.');
    }

    if (!in_array($status, ['draft', 'published'], true)) {
        $status = 'draft';
    }

    $slug = cms_unique_post_slug($title, $id > 0 ? $id : null, $providedSlug);
    $authorName = trim((string) ($input['author_name'] ?? cms_config('app.default_author', 'A1 Technovation')));
    $category = trim((string) ($input['category'] ?? ''));
    $tags = cms_normalize_tags((string) ($input['tags'] ?? ''));
    $featuredImage = trim((string) ($input['featured_image'] ?? ''));
    $metaTitle = trim((string) ($input['meta_title'] ?? ''));
    $metaDescription = trim((string) ($input['meta_description'] ?? ''));

    if ($excerpt === '') {
        $excerpt = cms_excerpt_from_html($contentHtml);
    }

    if ($metaTitle === '') {
        $metaTitle = $title;
    }

    if ($metaDescription === '') {
        $metaDescription = $excerpt;
    }

    $publishedAt = cms_normalize_datetime_input((string) ($input['published_at'] ?? ''));

    if ($status === 'published' && $publishedAt === null) {
        $publishedAt = (new DateTimeImmutable())->format('Y-m-d H:i:s');
    }

    $payload = [
        'title' => $title,
        'slug' => $slug,
        'status' => $status,
        'excerpt' => $excerpt,
        'content_html' => $contentHtml,
        'featured_image' => $featuredImage,
        'author_name' => $authorName !== '' ? $authorName : (string) cms_config('app.default_author', 'A1 Technovation'),
        'category' => $category,
        'tags' => $tags,
        'meta_title' => $metaTitle,
        'meta_description' => $metaDescription,
        'published_at' => $publishedAt,
        'updated_by' => $adminId,
    ];

    if ($id > 0) {
        $payload['id'] = $id;

        $statement = cms_db()->prepare(
            'UPDATE blog_posts
             SET title = :title, slug = :slug, status = :status, excerpt = :excerpt, content_html = :content_html,
                 featured_image = :featured_image, author_name = :author_name, category = :category, tags = :tags,
                 meta_title = :meta_title, meta_description = :meta_description, published_at = :published_at,
                 updated_by = :updated_by
             WHERE id = :id'
        );
        $statement->execute($payload);

        return $id;
    }

    $payload['created_by'] = $adminId;

    $statement = cms_db()->prepare(
        'INSERT INTO blog_posts
         (title, slug, status, excerpt, content_html, featured_image, author_name, category, tags, meta_title, meta_description, published_at, created_by, updated_by)
         VALUES
         (:title, :slug, :status, :excerpt, :content_html, :featured_image, :author_name, :category, :tags, :meta_title, :meta_description, :published_at, :created_by, :updated_by)'
    );
    $statement->execute($payload);

    return (int) cms_db()->lastInsertId();
}

function cms_delete_post(int $id): void
{
    if (!cms_table_exists('blog_posts')) {
        return;
    }

    $statement = cms_db()->prepare('DELETE FROM blog_posts WHERE id = :id');
    $statement->execute(['id' => $id]);
}

function cms_get_dashboard_stats(): array
{
    if (!cms_table_exists('blog_posts')) {
        return [
            'posts_total' => 0,
            'posts_published' => 0,
            'posts_draft' => 0,
            'media_total' => 0,
            'pages_total' => 0,
            'pages_published' => 0,
            'templates_total' => 0,
        ];
    }

    $pdo = cms_db();

    return [
        'posts_total' => (int) $pdo->query('SELECT COUNT(*) FROM blog_posts')->fetchColumn(),
        'posts_published' => (int) $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'published'")->fetchColumn(),
        'posts_draft' => (int) $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'draft'")->fetchColumn(),
        'media_total' => cms_table_exists('media_files') ? (int) $pdo->query('SELECT COUNT(*) FROM media_files')->fetchColumn() : 0,
        'pages_total' => cms_table_exists('cms_pages') ? (int) $pdo->query('SELECT COUNT(*) FROM cms_pages')->fetchColumn() : 0,
        'pages_published' => cms_table_exists('cms_pages') ? (int) $pdo->query("SELECT COUNT(*) FROM cms_pages WHERE status = 'published'")->fetchColumn() : 0,
        'templates_total' => cms_table_exists('cms_section_templates') ? (int) $pdo->query('SELECT COUNT(*) FROM cms_section_templates')->fetchColumn() : 0,
    ];
}

function cms_get_published_posts(int $limit = 50): array
{
    if (!cms_table_exists('blog_posts')) {
        return [];
    }

    $limit = max(1, min($limit, 200));
    $sql = "SELECT * FROM blog_posts WHERE status = 'published' AND published_at IS NOT NULL ORDER BY published_at DESC LIMIT " . $limit;

    return cms_db()->query($sql)->fetchAll() ?: [];
}

function cms_get_published_post_by_slug(string $slug): ?array
{
    if (!cms_table_exists('blog_posts')) {
        return null;
    }

    $statement = cms_db()->prepare(
        "SELECT * FROM blog_posts WHERE slug = :slug AND status = 'published' AND published_at IS NOT NULL LIMIT 1"
    );
    $statement->execute(['slug' => $slug]);
    $post = $statement->fetch();

    return is_array($post) ? $post : null;
}

function cms_get_related_posts(int $postId, string $category = '', int $limit = 3): array
{
    if (!cms_table_exists('blog_posts')) {
        return [];
    }

    $limit = max(1, min($limit, 6));
    $params = ['id' => $postId];
    $sql = "SELECT * FROM blog_posts WHERE status = 'published' AND published_at IS NOT NULL AND id != :id";

    if ($category !== '') {
        $sql .= ' AND category = :category';
        $params['category'] = $category;
    }

    $sql .= ' ORDER BY published_at DESC LIMIT ' . $limit;

    $statement = cms_db()->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll() ?: [];
}

function cms_page_templates(): array
{
    return [
        'service' => 'Service Page',
        'landing' => 'Landing Page',
        'standard' => 'Standard Page',
    ];
}

function cms_widget_catalog(): array
{
    return [
        'hero' => 'Hero',
        'text' => 'Text',
        'image' => 'Image',
        'video' => 'Video',
        'cta' => 'CTA',
        'faq' => 'FAQ',
        'pricing' => 'Pricing',
        'testimonials' => 'Testimonials',
        'stats' => 'Stats',
        'service_cards' => 'Service Cards',
        'buttons' => 'Buttons',
        'custom_html' => 'Custom HTML',
    ];
}

function cms_default_widget_payload(string $widgetType): array
{
    return match ($widgetType) {
        'hero' => ['eyebrow' => 'New Service', 'heading' => 'Service page heading', 'highlight' => 'that converts', 'text' => 'Explain the offer clearly.', 'primary_label' => 'Get Started', 'primary_url' => '/pages/contact.html', 'secondary_label' => 'See Pricing', 'secondary_url' => '#pricing', 'background_image' => '', 'theme' => 'dark'],
        'text' => ['heading' => 'Section heading', 'body' => '<p>Add your rich text content here.</p>'],
        'image' => ['heading' => 'Visual section', 'image_url' => '', 'alt' => '', 'caption' => ''],
        'video' => ['heading' => 'Video section', 'video_url' => '', 'poster_url' => '', 'caption' => ''],
        'cta' => ['heading' => 'Ready to grow faster?', 'text' => 'Add a strong call to action.', 'button_label' => 'Book a Free Call', 'button_url' => 'https://wa.me/8801799976295'],
        'faq' => ['heading' => 'Frequently asked questions', 'items' => [['question' => 'Question one', 'answer' => 'Answer one']]],
        'pricing' => ['heading' => 'Packages', 'items' => [['name' => 'Starter', 'price' => '$499', 'details' => 'Great for getting started', 'features' => ['Feature one', 'Feature two']]]],
        'testimonials' => ['heading' => 'Client proof', 'items' => [['quote' => 'Great work and fast communication.', 'name' => 'Client Name', 'role' => 'Business Owner']]],
        'stats' => ['heading' => 'Performance highlights', 'items' => [['number' => '250%', 'label' => 'Growth'], ['number' => '90 days', 'label' => 'Average turnaround']]],
        'service_cards' => ['heading' => 'What is included', 'items' => [['title' => 'Audit', 'text' => 'Technical and growth audit'], ['title' => 'Execution', 'text' => 'Hands-on implementation']]],
        'buttons' => ['heading' => 'Helpful links', 'items' => [['label' => 'Contact Us', 'url' => '/pages/contact.html'], ['label' => 'Our Work', 'url' => '/pages/portfolio.html']]],
        'custom_html' => ['heading' => 'Custom HTML block', 'html' => '<div class="custom-block">Paste any trusted HTML here.</div>'],
        default => ['heading' => 'New section'],
    };
}

function cms_sanitize_widget_payload(string $widgetType, mixed $payload): array
{
    $data = is_array($payload) ? $payload : [];

    if (in_array($widgetType, ['text', 'custom_html'], true)) {
        if (isset($data['body'])) {
            $data['body'] = cms_clean_html((string) $data['body']);
        }
        if (isset($data['html'])) {
            $data['html'] = cms_clean_html((string) $data['html']);
        }
    }

    foreach (['items'] as $listKey) {
        if (isset($data[$listKey]) && is_array($data[$listKey])) {
            $normalized = [];
            foreach ($data[$listKey] as $item) {
                if (is_array($item)) {
                    $normalized[] = $item;
                }
            }
            $data[$listKey] = $normalized;
        }
    }

    return $data;
}

function cms_normalize_widget_rows(mixed $rows): array
{
    $normalized = [];
    if (!is_array($rows)) {
        return $normalized;
    }

    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }

        $widgetType = (string) ($row['widget_type'] ?? '');
        if ($widgetType === '') {
            continue;
        }

        $normalized[] = [
            'widget_type' => $widgetType,
            'payload' => cms_sanitize_widget_payload($widgetType, $row['payload'] ?? []),
        ];
    }

    return $normalized;
}

function cms_page_slug_exists(string $slug, ?int $ignoreId = null): bool
{
    if (!cms_table_exists('cms_pages')) {
        return false;
    }

    $sql = 'SELECT COUNT(*) FROM cms_pages WHERE slug = :slug';
    $params = ['slug' => $slug];
    if ($ignoreId !== null) {
        $sql .= ' AND id != :id';
        $params['id'] = $ignoreId;
    }
    $statement = cms_db()->prepare($sql);
    $statement->execute($params);

    return (int) $statement->fetchColumn() > 0;
}

function cms_unique_page_slug(string $title, ?int $ignoreId = null, ?string $provided = null): string
{
    $base = cms_make_slug($provided !== null && $provided !== '' ? $provided : $title);
    $slug = $base;
    $suffix = 2;

    while (cms_page_slug_exists($slug, $ignoreId)) {
        $slug = $base . '-' . $suffix;
        $suffix++;
    }

    return $slug;
}

function cms_get_pages_admin(?string $pageType = null): array
{
    if (!cms_table_exists('cms_pages')) {
        return [];
    }

    $sql = 'SELECT * FROM cms_pages';
    $params = [];
    if ($pageType !== null && $pageType !== '') {
        $sql .= ' WHERE page_type = :page_type';
        $params['page_type'] = $pageType;
    }
    $sql .= ' ORDER BY sort_order ASC, COALESCE(published_at, updated_at) DESC';

    $statement = cms_db()->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll() ?: [];
}

function cms_get_page(int $id): ?array
{
    if (!cms_table_exists('cms_pages')) {
        return null;
    }
    $statement = cms_db()->prepare('SELECT * FROM cms_pages WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $id]);
    $page = $statement->fetch();

    if (!is_array($page)) {
        return null;
    }

    $page['builder'] = cms_json_decode_array((string) ($page['page_builder_json'] ?? ''), []);
    return $page;
}

function cms_get_published_page_by_slug(string $slug, ?string $pageType = null): ?array
{
    if (!cms_table_exists('cms_pages')) {
        return null;
    }

    $sql = "SELECT * FROM cms_pages WHERE slug = :slug AND status = 'published'";
    $params = ['slug' => $slug];
    if ($pageType !== null && $pageType !== '') {
        $sql .= ' AND page_type = :page_type';
        $params['page_type'] = $pageType;
    }
    $sql .= ' LIMIT 1';
    $statement = cms_db()->prepare($sql);
    $statement->execute($params);
    $page = $statement->fetch();

    if (!is_array($page)) {
        return null;
    }

    $page['builder'] = cms_json_decode_array((string) ($page['page_builder_json'] ?? ''), []);
    return $page;
}

function cms_save_page(array $input, int $adminId): int
{
    $id = isset($input['id']) ? max(0, (int) $input['id']) : 0;
    $title = trim((string) ($input['title'] ?? ''));
    $pageType = (string) ($input['page_type'] ?? 'page');
    $status = (string) ($input['status'] ?? 'draft');
    $providedSlug = trim((string) ($input['slug'] ?? ''));
    $builder = cms_normalize_widget_rows(cms_json_decode_array((string) ($input['builder_json'] ?? ''), []));
    $customHtml = cms_clean_html((string) ($input['custom_html'] ?? ''));

    if ($title === '') {
        throw new RuntimeException('Page title is required.');
    }
    if (!in_array($pageType, ['page', 'service', 'landing'], true)) {
        $pageType = 'page';
    }
    if (!in_array($status, ['draft', 'published'], true)) {
        $status = 'draft';
    }
    if ($builder === [] && $customHtml === '') {
        throw new RuntimeException('Add at least one widget section or custom HTML block.');
    }

    $slug = cms_unique_page_slug($title, $id > 0 ? $id : null, $providedSlug);
    $excerpt = trim((string) ($input['excerpt'] ?? ''));
    $metaTitle = trim((string) ($input['meta_title'] ?? ''));
    $metaDescription = trim((string) ($input['meta_description'] ?? ''));
    $featuredImage = trim((string) ($input['featured_image'] ?? ''));
    $ogImage = trim((string) ($input['og_image'] ?? ''));
    $canonicalUrl = trim((string) ($input['canonical_url'] ?? ''));
    $schemaJson = trim((string) ($input['schema_json'] ?? ''));
    $templateKey = (string) ($input['template_key'] ?? 'service');
    if (!array_key_exists($templateKey, cms_page_templates())) {
        $templateKey = 'service';
    }
    $sortOrder = (int) ($input['sort_order'] ?? 0);
    $showInNav = cms_post('show_in_nav') ? 1 : 0;
    $navGroup = trim((string) ($input['nav_group'] ?? ''));
    $customCss = trim((string) ($input['custom_css'] ?? ''));
    $customJs = trim((string) ($input['custom_js'] ?? ''));
    $publishedAt = cms_normalize_datetime_input((string) ($input['published_at'] ?? ''));
    if ($status === 'published' && $publishedAt === null) {
        $publishedAt = (new DateTimeImmutable())->format('Y-m-d H:i:s');
    }

    if ($excerpt === '') {
        $excerpt = cms_excerpt_from_html($customHtml !== '' ? $customHtml : cms_json_encode($builder));
    }
    if ($metaTitle === '') {
        $metaTitle = $title;
    }
    if ($metaDescription === '') {
        $metaDescription = $excerpt;
    }

    $payload = [
        'title' => $title,
        'slug' => $slug,
        'page_type' => $pageType,
        'status' => $status,
        'excerpt' => $excerpt,
        'template_key' => $templateKey,
        'featured_image' => $featuredImage,
        'page_builder_json' => cms_json_encode($builder),
        'custom_html' => $customHtml,
        'custom_css' => $customCss,
        'custom_js' => $customJs,
        'show_in_nav' => $showInNav,
        'nav_group' => $navGroup !== '' ? $navGroup : null,
        'sort_order' => $sortOrder,
        'meta_title' => $metaTitle,
        'meta_description' => $metaDescription,
        'canonical_url' => $canonicalUrl,
        'og_image' => $ogImage,
        'schema_json' => $schemaJson,
        'published_at' => $publishedAt,
        'updated_by' => $adminId,
    ];

    if ($id > 0) {
        $payload['id'] = $id;
        $statement = cms_db()->prepare(
            'UPDATE cms_pages
             SET title = :title, slug = :slug, page_type = :page_type, status = :status, excerpt = :excerpt,
                 template_key = :template_key, featured_image = :featured_image, page_builder_json = :page_builder_json,
                 custom_html = :custom_html, custom_css = :custom_css, custom_js = :custom_js, show_in_nav = :show_in_nav,
                 nav_group = :nav_group, sort_order = :sort_order, meta_title = :meta_title, meta_description = :meta_description,
                 canonical_url = :canonical_url, og_image = :og_image, schema_json = :schema_json, published_at = :published_at,
                 updated_by = :updated_by
             WHERE id = :id'
        );
        $statement->execute($payload);
        return $id;
    }

    $payload['created_by'] = $adminId;
    $statement = cms_db()->prepare(
        'INSERT INTO cms_pages
         (title, slug, page_type, status, excerpt, template_key, featured_image, page_builder_json, custom_html, custom_css, custom_js, show_in_nav, nav_group, sort_order, meta_title, meta_description, canonical_url, og_image, schema_json, published_at, created_by, updated_by)
         VALUES
         (:title, :slug, :page_type, :status, :excerpt, :template_key, :featured_image, :page_builder_json, :custom_html, :custom_css, :custom_js, :show_in_nav, :nav_group, :sort_order, :meta_title, :meta_description, :canonical_url, :og_image, :schema_json, :published_at, :created_by, :updated_by)'
    );
    $statement->execute($payload);

    return (int) cms_db()->lastInsertId();
}

function cms_delete_page(int $id): void
{
    if (!cms_table_exists('cms_pages')) {
        return;
    }
    $statement = cms_db()->prepare('DELETE FROM cms_pages WHERE id = :id');
    $statement->execute(['id' => $id]);
}

function cms_duplicate_page(int $id, int $adminId): int
{
    $page = cms_get_page($id);
    if (!$page) {
        throw new RuntimeException('The page you tried to duplicate could not be found.');
    }

    $page['id'] = 0;
    $page['title'] = (string) $page['title'] . ' Copy';
    $page['slug'] = '';
    $page['status'] = 'draft';
    $page['published_at'] = null;
    $page['builder_json'] = cms_json_encode($page['builder'] ?? []);

    return cms_save_page($page, $adminId);
}

function cms_get_section_templates(): array
{
    if (!cms_table_exists('cms_section_templates')) {
        return [];
    }
    return cms_db()->query('SELECT * FROM cms_section_templates ORDER BY updated_at DESC')->fetchAll() ?: [];
}

function cms_save_section_template(array $input, int $adminId): int
{
    if (!cms_table_exists('cms_section_templates')) {
        throw new RuntimeException('Section templates table is missing. Re-run the CMS schema.');
    }
    $id = isset($input['id']) ? max(0, (int) $input['id']) : 0;
    $name = trim((string) ($input['name'] ?? ''));
    $widgetType = (string) ($input['widget_type'] ?? 'text');
    $payload = cms_sanitize_widget_payload($widgetType, cms_json_decode_array((string) ($input['payload_json'] ?? ''), []));
    if ($name === '') {
        throw new RuntimeException('Template name is required.');
    }
    $params = [
        'name' => $name,
        'widget_type' => $widgetType,
        'payload_json' => cms_json_encode($payload),
        'created_by' => $adminId,
    ];
    if ($id > 0) {
        $params['id'] = $id;
        $statement = cms_db()->prepare('UPDATE cms_section_templates SET name = :name, widget_type = :widget_type, payload_json = :payload_json WHERE id = :id');
        $statement->execute($params);
        return $id;
    }
    $statement = cms_db()->prepare('INSERT INTO cms_section_templates (name, widget_type, payload_json, created_by) VALUES (:name, :widget_type, :payload_json, :created_by)');
    $statement->execute($params);
    return (int) cms_db()->lastInsertId();
}

function cms_delete_section_template(int $id): void
{
    if (!cms_table_exists('cms_section_templates')) {
        return;
    }
    $statement = cms_db()->prepare('DELETE FROM cms_section_templates WHERE id = :id');
    $statement->execute(['id' => $id]);
}

function cms_setting(string $key, mixed $default = null): mixed
{
    if (!cms_table_exists('cms_settings')) {
        return $default;
    }
    $statement = cms_db()->prepare('SELECT setting_value FROM cms_settings WHERE setting_key = :setting_key LIMIT 1');
    $statement->execute(['setting_key' => $key]);
    $value = $statement->fetchColumn();
    if ($value === false) {
        return $default;
    }
    if (is_array($default)) {
        return cms_json_decode_array((string) $value, $default);
    }
    return $value;
}

function cms_save_setting(string $key, string $value): void
{
    if (!cms_table_exists('cms_settings')) {
        throw new RuntimeException('Settings table is missing. Re-run the CMS schema.');
    }
    $statement = cms_db()->prepare(
        'INSERT INTO cms_settings (setting_key, setting_value) VALUES (:setting_key, :setting_value)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $statement->execute([
        'setting_key' => $key,
        'setting_value' => $value,
    ]);
}

function cms_default_site_settings(): array
{
    return [
        'company_name' => 'A1 Technovation',
        'phone' => '+880 1799-976295',
        'email' => 'info.a1technovation@gmail.com',
        'whatsapp_url' => 'https://wa.me/8801799976295',
        'footer_text' => "Bangladesh's fastest-growing digital marketing agency.",
        'primary_cta_label' => 'Free Consultation',
        'primary_cta_url' => 'https://wa.me/8801799976295',
    ];
}

function cms_site_settings(): array
{
    return array_merge(cms_default_site_settings(), cms_setting('site_settings', cms_default_site_settings()));
}

function cms_save_site_settings(array $settings): void
{
    cms_save_setting('site_settings', cms_json_encode($settings));
}

function cms_nav_service_pages(): array
{
    if (!cms_table_exists('cms_pages')) {
        return [];
    }
    $statement = cms_db()->query("SELECT title, slug, page_type FROM cms_pages WHERE status = 'published' AND show_in_nav = 1 AND page_type = 'service' ORDER BY sort_order ASC, title ASC");
    return $statement->fetchAll() ?: [];
}
