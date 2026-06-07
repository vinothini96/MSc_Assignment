-- ============================================================
-- Contact Messages table
-- Run this in phpMyAdmin → SQL tab against saree_shop_db
-- ============================================================

USE saree_shop_db;

CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id`           INT            NOT NULL AUTO_INCREMENT,
  `user_id`      INT            DEFAULT NULL,           -- NULL for guest submissions
  `name`         VARCHAR(150)   COLLATE utf8mb4_unicode_ci NOT NULL,
  `email`        VARCHAR(150)   COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject`      VARCHAR(255)   COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message`      TEXT           COLLATE utf8mb4_unicode_ci NOT NULL,
  `status`       ENUM('unread','read','replied') COLLATE utf8mb4_unicode_ci DEFAULT 'unread',
  `admin_reply`  TEXT           COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `replied_at`   TIMESTAMP      NULL DEFAULT NULL,
  `replied_by`   INT            DEFAULT NULL,           -- admins.id who replied
  `created_at`   TIMESTAMP      NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id`    (`user_id`),
  KEY `status`     (`status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
