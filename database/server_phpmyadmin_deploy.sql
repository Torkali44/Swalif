-- =============================================================================
-- سوالف — تحديث قاعدة البيانات (phpMyAdmin)
-- نفّذ هذا الملف مرة واحدة على السيرفر إذا لا يوجد Terminal.
-- إذا ظهر خطأ "Duplicate" أو "already exists" تجاهل السطر وكمّل.
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- 1) فهرسة الأداء (أسئلة / فئات / تصنيفات)
-- -----------------------------------------------------------------------------
ALTER TABLE `categories`
  ADD INDEX `categories_active_sort_idx` (`is_active`, `sort_order`);

ALTER TABLE `categories`
  ADD INDEX `categories_class_active_idx` (`classification_id`, `is_active`);

ALTER TABLE `classifications`
  ADD INDEX `classifications_active_sort_idx` (`is_active`, `sort_order`);

ALTER TABLE `questions`
  ADD INDEX `questions_cat_active_level_idx` (`category_id`, `is_active`, `level`);

ALTER TABLE `questions`
  ADD INDEX `questions_active_idx` (`is_active`);

-- -----------------------------------------------------------------------------
-- 2) تتبع أسئلة اللاعب (بنك الأسئلة — باقي X سؤال)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_category_question_plays` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `question_id` bigint unsigned NOT NULL,
  `source` varchar(20) NOT NULL DEFAULT 'game',
  `game_id` bigint unsigned DEFAULT NULL,
  `custom_game_id` bigint unsigned DEFAULT NULL,
  `played_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_category_question_plays_user_id_question_id_unique` (`user_id`,`question_id`),
  KEY `user_category_question_plays_user_id_category_id_index` (`user_id`,`category_id`),
  KEY `user_category_question_plays_category_id_foreign` (`category_id`),
  KEY `user_category_question_plays_question_id_foreign` (`question_id`),
  CONSTRAINT `user_category_question_plays_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_category_question_plays_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_category_question_plays_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 3) شخصيات اللاعبين
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `characters` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name_ar` varchar(80) NOT NULL,
  `name_en` varchar(80) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `icon` varchar(20) DEFAULT NULL,
  `accent_color` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int unsigned NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `characters_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- عمود character_id في users
-- لو العمود موجود: تجاهل السطرين التاليين
ALTER TABLE `users`
  ADD COLUMN `character_id` bigint unsigned NULL DEFAULT NULL AFTER `avatar`;

ALTER TABLE `users`
  ADD CONSTRAINT `users_character_id_foreign`
  FOREIGN KEY (`character_id`) REFERENCES `characters` (`id`) ON DELETE SET NULL;

-- -----------------------------------------------------------------------------
-- 4) شخصيات افتراضية (8 شخصيات)
-- -----------------------------------------------------------------------------
INSERT INTO `characters` (`name_ar`, `name_en`, `slug`, `icon`, `accent_color`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
('أحمد', 'Ahmed', 'ahmed', '🧑', '#0F6B4C', 1, 1, NOW(), NOW()),
('سارة', 'Sara', 'sara', '👩', '#BE185D', 1, 2, NOW(), NOW()),
('خالد', 'Khaled', 'khaled', '🧔', '#1E3A5F', 1, 3, NOW(), NOW()),
('نورا', 'Nora', 'nora', '👧', '#6D28D9', 1, 4, NOW(), NOW()),
('عمر', 'Omar', 'omar', '🧒', '#0369A1', 1, 5, NOW(), NOW()),
('ليلى', 'Layla', 'layla', '👩‍🦱', '#C2410C', 1, 6, NOW(), NOW()),
('فيصل', 'Faisal', 'faisal', '🧑‍💼', '#15803D', 1, 7, NOW(), NOW()),
('مريم', 'Mariam', 'mariam', '👩‍🎓', '#7C2D12', 1, 8, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `name_ar` = VALUES(`name_ar`),
  `icon` = VALUES(`icon`),
  `accent_color` = VALUES(`accent_color`),
  `is_active` = VALUES(`is_active`),
  `sort_order` = VALUES(`sort_order`),
  `updated_at` = NOW();

-- -----------------------------------------------------------------------------
-- 5) تسجيل الـ migrations (اختياري)
-- -----------------------------------------------------------------------------
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_08_24_000001_add_catalog_performance_indexes', 999 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_08_24_000001_add_catalog_performance_indexes');

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_08_24_210000_create_user_category_question_plays_table', 999 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_08_24_210000_create_user_category_question_plays_table');

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_08_24_220000_create_characters_table', 999 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_08_24_220000_create_characters_table');

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_08_24_220001_add_character_id_to_users_table', 999 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_08_24_220001_add_character_id_to_users_table');

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- بعد الرفع على السerver:
-- 1) APP_DEBUG=false  و  APP_ENV=production  في .env
-- 2) احذف public_html/fix-storage.php بعد إنشاء مجلد storage
-- 3) تأكد من وجود: public_html/storage/characters/
-- 4) ارفع public/build/ الجديد
-- =============================================================================
