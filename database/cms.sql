CREATE TABLE IF NOT EXISTS admin_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS media_files (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    title VARCHAR(190) NULL,
    alt_text VARCHAR(190) NULL,
    file_path VARCHAR(255) NOT NULL,
    mime_type VARCHAR(120) NOT NULL,
    file_size INT UNSIGNED NOT NULL DEFAULT 0,
    uploaded_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_media_uploaded_by
        FOREIGN KEY (uploaded_by) REFERENCES admin_users(id)
        ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS blog_posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(190) NOT NULL,
    slug VARCHAR(190) NOT NULL UNIQUE,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    excerpt TEXT NULL,
    content_html MEDIUMTEXT NOT NULL,
    featured_image VARCHAR(255) NULL,
    author_name VARCHAR(120) NOT NULL,
    category VARCHAR(120) NULL,
    tags VARCHAR(255) NULL,
    meta_title VARCHAR(190) NULL,
    meta_description VARCHAR(255) NULL,
    published_at DATETIME NULL,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_posts_created_by
        FOREIGN KEY (created_by) REFERENCES admin_users(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_posts_updated_by
        FOREIGN KEY (updated_by) REFERENCES admin_users(id)
        ON DELETE SET NULL
);

CREATE INDEX idx_posts_status_published_at ON blog_posts(status, published_at);
CREATE INDEX idx_posts_category ON blog_posts(category);
CREATE INDEX idx_media_created_at ON media_files(created_at);

CREATE TABLE IF NOT EXISTS cms_pages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(190) NOT NULL,
    slug VARCHAR(190) NOT NULL UNIQUE,
    page_type ENUM('page', 'service', 'landing') NOT NULL DEFAULT 'page',
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    excerpt TEXT NULL,
    template_key VARCHAR(80) NOT NULL DEFAULT 'service',
    featured_image VARCHAR(255) NULL,
    page_builder_json LONGTEXT NOT NULL,
    custom_html LONGTEXT NULL,
    custom_css MEDIUMTEXT NULL,
    custom_js MEDIUMTEXT NULL,
    show_in_nav TINYINT(1) NOT NULL DEFAULT 0,
    nav_group VARCHAR(80) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    meta_title VARCHAR(190) NULL,
    meta_description VARCHAR(255) NULL,
    canonical_url VARCHAR(255) NULL,
    og_image VARCHAR(255) NULL,
    schema_json LONGTEXT NULL,
    published_at DATETIME NULL,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pages_created_by
        FOREIGN KEY (created_by) REFERENCES admin_users(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_pages_updated_by
        FOREIGN KEY (updated_by) REFERENCES admin_users(id)
        ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS cms_section_templates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(190) NOT NULL,
    widget_type VARCHAR(80) NOT NULL,
    payload_json LONGTEXT NOT NULL,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_section_templates_created_by
        FOREIGN KEY (created_by) REFERENCES admin_users(id)
        ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS cms_settings (
    setting_key VARCHAR(120) PRIMARY KEY,
    setting_value LONGTEXT NOT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE INDEX idx_pages_type_status_order ON cms_pages(page_type, status, sort_order);
CREATE INDEX idx_pages_nav ON cms_pages(show_in_nav, nav_group, sort_order);
