-- ============================================================================
-- CareerCraft - fresh installation schema
--
-- Creates an EMPTY database for CareerCraft. It contains no users, no passwords and no data.
--
--   * Requires MySQL 5.7+ or MariaDB 10.3+ (InnoDB, utf8mb4).
--   * Safe to run: it only uses "IF NOT EXISTS". It never drops, truncates or alters
--     anything, so running it against a database that already has these tables changes nothing.
--   * The tables and columns were derived from the SQL queries in the PHP code
--     (users, profiles, skills, projects, resume). Column sizes match the limits that the
--     application validates (for example 100 characters for a name, 255 for a link).
--
-- How to use it (phpMyAdmin): open phpMyAdmin -> "Import" tab (server level) -> choose this file -> Go.
-- Then copy includes/config.example.php to includes/config.php and enter your MySQL settings.
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `careercraft`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `careercraft`;

-- ---------------------------------------------------------------------------
-- users: one row per account.
--   password       holds the PHP password_hash() value (never the real password)
--   profile_image  file name inside uploads/profiles/ (NULL = no picture)
--   email          unique; the application also compares it case-insensitively
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fullname`      VARCHAR(100) NOT NULL,
  `email`         VARCHAR(100) NOT NULL,
  `password`      VARCHAR(255) NOT NULL,
  `profile_image` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- profiles: at most one row per user (the application updates it if it exists).
--   phone is private: the public portfolio never reads it.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `profiles` (
  `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`   INT UNSIGNED NOT NULL,
  `job_title` VARCHAR(100) NOT NULL DEFAULT '',
  `phone`     VARCHAR(20)  NOT NULL DEFAULT '',
  `bio`       TEXT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_profiles_user` (`user_id`),
  CONSTRAINT `fk_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- skills: many per user. The "id" column is needed for editing and deleting.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `skills` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED NOT NULL,
  `skill_name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_skills_user` (`user_id`),
  CONSTRAINT `fk_skills_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- projects: many per user.
--   technologies   optional comma-separated list (the application also works without this column)
--   project_link   http/https address, or an empty string
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `projects` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`      INT UNSIGNED NOT NULL,
  `project_name` VARCHAR(100) NOT NULL,
  `description`  TEXT NOT NULL,
  `technologies` VARCHAR(255) NULL DEFAULT NULL,
  `project_link` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_projects_user` (`user_id`),
  CONSTRAINT `fk_projects_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- resume: the application keeps one current PDF per user (an upload replaces the old one).
--   file_name   file name inside uploads/resumes/
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `resume` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED NOT NULL,
  `file_name`   VARCHAR(255) NOT NULL,
  `uploaded_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_resume_user` (`user_id`),
  CONSTRAINT `fk_resume_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
