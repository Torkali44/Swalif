-- =============================================================================
-- نفّذ هذا لوحده في phpMyAdmin (مهم جداً)
-- جدول تتبع أسئلة اللاعب — بدونه الصفحات/اللعب ممكن تبوظ
-- لو ظهر "already exists" معناها الجدول موجود خلاص ✓
-- =============================================================================

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
  KEY `user_category_question_plays_question_id_foreign` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- لو العمود character_id مش موجود في users (تجاهل الخطأ لو موجود):
-- ALTER TABLE `users` ADD COLUMN `character_id` bigint unsigned NULL DEFAULT NULL AFTER `avatar`;

-- للتأكد: لازم يظهر الجدول في قائمة الجداول
-- SHOW TABLES LIKE 'user_category_question_plays';
