-- Hostinger phpMyAdmin: run this on the LIVE database
-- Fixes: {"success":false,"message":"Run migrations: template_purchases table missing."}

CREATE TABLE IF NOT EXISTS `template_purchases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint unsigned NOT NULL,
  `business_template_id` bigint unsigned DEFAULT NULL,
  `template_slug` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `file_url` varchar(255) DEFAULT NULL,
  `price_paid` decimal(12,2) NOT NULL DEFAULT 0.00,
  `fee_percent` decimal(5,2) DEFAULT NULL,
  `platform_fee` decimal(12,2) DEFAULT NULL,
  `seller_amount` decimal(12,2) DEFAULT NULL,
  `payment_method` varchar(255) DEFAULT NULL,
  `payment_status` varchar(255) NOT NULL DEFAULT 'pending',
  `download_token` varchar(64) DEFAULT NULL,
  `download_token_expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_purchases_download_token_unique` (`download_token`),
  KEY `template_purchases_customer_id_index` (`customer_id`),
  KEY `template_purchases_business_template_id_index` (`business_template_id`),
  KEY `template_purchases_template_slug_index` (`template_slug`),
  KEY `template_purchases_payment_status_index` (`payment_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Also create quote requests if missing (template fill-in service)
CREATE TABLE IF NOT EXISTS `template_quote_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `template_title` varchar(255) DEFAULT NULL,
  `template_slug` varchar(255) DEFAULT NULL,
  `template_id` bigint unsigned DEFAULT NULL,
  `file_url` varchar(255) DEFAULT NULL,
  `vertical` varchar(64) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'new',
  `admin_notes` text,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `template_quote_requests_user_id_index` (`user_id`),
  KEY `template_quote_requests_template_id_index` (`template_id`),
  KEY `template_quote_requests_vertical_index` (`vertical`),
  KEY `template_quote_requests_status_index` (`status`),
  KEY `template_quote_requests_assigned_to_index` (`assigned_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Business admin flag (safe if already present)
SET @col_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'users'
    AND COLUMN_NAME = 'is_business_admin'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE `users` ADD COLUMN `is_business_admin` tinyint(1) NOT NULL DEFAULT 0',
  'SELECT ''is_business_admin already exists'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
