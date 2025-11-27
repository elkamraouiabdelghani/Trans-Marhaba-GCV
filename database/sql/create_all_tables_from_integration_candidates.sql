-- SQL Script to create all tables from integration_candidates migration onwards
-- This script includes DROP TABLE IF EXISTS before each CREATE TABLE
-- Run this in phpMyAdmin or MySQL command line

-- ============================================
-- 1. integration_candidates
-- ============================================
DROP TABLE IF EXISTS `integration_candidates`;

CREATE TABLE `integration_candidates` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` ENUM('driver', 'administration') NOT NULL,
  `driver_id` BIGINT UNSIGNED NULL,
  `identification_besoin` TEXT NULL,
  `poste_type` ENUM('chauffeur', 'administration') NOT NULL,
  `description_poste` TEXT NULL,
  `prospection_method` ENUM('reseaux_social', 'bouche_a_oreil', 'bureau_recrutement', 'autre') NULL,
  `prospection_date` DATE NULL,
  `notes_prospection` TEXT NULL,
  `status` ENUM('draft', 'in_progress', 'rejected', 'validated') NOT NULL DEFAULT 'draft',
  `current_step` TINYINT NOT NULL DEFAULT 1 COMMENT 'Current step number (1-8)',
  `validated_by` BIGINT UNSIGNED NULL,
  `validated_at` TIMESTAMP NULL,
  `rejected_at` TIMESTAMP NULL,
  `rejection_reason` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `integration_candidates_driver_id_foreign` (`driver_id`),
  KEY `integration_candidates_validated_by_foreign` (`validated_by`),
  CONSTRAINT `integration_candidates_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `integration_candidates_validated_by_foreign` FOREIGN KEY (`validated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. integration_steps
-- ============================================
DROP TABLE IF EXISTS `integration_steps`;

CREATE TABLE `integration_steps` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `integration_candidate_id` BIGINT UNSIGNED NOT NULL,
  `step_number` TINYINT NOT NULL COMMENT 'Step number (1-8)',
  `step_data` JSON NULL COMMENT 'Stores all form inputs for the step',
  `status` ENUM('pending', 'validated', 'rejected') NOT NULL DEFAULT 'pending',
  `validated_by` BIGINT UNSIGNED NULL,
  `validated_at` TIMESTAMP NULL,
  `rejected_at` TIMESTAMP NULL,
  `rejection_reason` TEXT NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `integration_steps_integration_candidate_id_foreign` (`integration_candidate_id`),
  KEY `integration_steps_validated_by_foreign` (`validated_by`),
  CONSTRAINT `integration_steps_integration_candidate_id_foreign` FOREIGN KEY (`integration_candidate_id`) REFERENCES `integration_candidates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `integration_steps_validated_by_foreign` FOREIGN KEY (`validated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note: If JSON is not supported, replace `step_data` JSON with `step_data` TEXT

-- ============================================
-- 3. formations
-- ============================================
DROP TABLE IF EXISTS `formations`;

CREATE TABLE `formations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` ENUM('mondatory','optionnel','complimentaire','other') NOT NULL DEFAULT 'mondatory',
  `flotte_id` BIGINT UNSIGNED NULL,
  `theme` VARCHAR(255) NOT NULL,
  `code` VARCHAR(255) NOT NULL,
  `planned_year` YEAR NULL,
  `description` TEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `reference_value` INT UNSIGNED NULL,
  `reference_unit` ENUM('months', 'years') NULL,
  `warning_alert_percent` TINYINT UNSIGNED NULL,
  `critical_alert_percent` TINYINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `formations_theme_unique` (`theme`),
  UNIQUE KEY `formations_code_unique` (`code`),
  KEY `formations_flotte_id_foreign` (`flotte_id`),
  CONSTRAINT `formations_flotte_id_foreign` FOREIGN KEY (`flotte_id`) REFERENCES `flottes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. driver_activities
-- ============================================
DROP TABLE IF EXISTS `driver_activities`;

CREATE TABLE `driver_activities` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `driver_id` BIGINT UNSIGNED NOT NULL,
  `activity_date` DATE NOT NULL,
  `flotte` VARCHAR(255) NULL,
  `asset_description` VARCHAR(255) NULL,
  `driver_name` VARCHAR(255) NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `work_time` TIME NULL,
  `driving_time` TIME NULL,
  `rest_time` TIME NULL,
  `rest_daily` TIME NULL,
  `raison` TEXT NULL,
  `start_location` VARCHAR(255) NULL,
  `overnight_location` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `driver_activities_driver_id_foreign` (`driver_id`),
  CONSTRAINT `driver_activities_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. formation_processes
-- ============================================
DROP TABLE IF EXISTS `formation_processes`;

CREATE TABLE `formation_processes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `driver_id` BIGINT UNSIGNED NOT NULL,
  `formation_id` BIGINT UNSIGNED NOT NULL,
  `site` VARCHAR(255) NULL,
  `flotte_id` BIGINT UNSIGNED NULL,
  `theme` VARCHAR(255) NULL,
  `status` ENUM('draft', 'in_progress', 'rejected', 'validated') NOT NULL DEFAULT 'draft',
  `current_step` TINYINT NOT NULL DEFAULT 1 COMMENT 'Current step number (1-8)',
  `validated_by` BIGINT UNSIGNED NULL,
  `validated_at` TIMESTAMP NULL,
  `rejected_at` TIMESTAMP NULL,
  `rejection_reason` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `formation_processes_driver_id_foreign` (`driver_id`),
  KEY `formation_processes_formation_id_foreign` (`formation_id`),
  KEY `formation_processes_flotte_id_foreign` (`flotte_id`),
  KEY `formation_processes_validated_by_foreign` (`validated_by`),
  CONSTRAINT `formation_processes_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `formation_processes_formation_id_foreign` FOREIGN KEY (`formation_id`) REFERENCES `formations` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `formation_processes_flotte_id_foreign` FOREIGN KEY (`flotte_id`) REFERENCES `flottes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `formation_processes_validated_by_foreign` FOREIGN KEY (`validated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. formation_steps
-- ============================================
DROP TABLE IF EXISTS `formation_steps`;

CREATE TABLE `formation_steps` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `formation_process_id` BIGINT UNSIGNED NOT NULL,
  `step_number` TINYINT NOT NULL COMMENT 'Step number (1-8)',
  `step_data` JSON NULL COMMENT 'Stores all form inputs for the step',
  `status` ENUM('pending', 'validated', 'rejected') NOT NULL DEFAULT 'pending',
  `validated_by` BIGINT UNSIGNED NULL,
  `validated_at` TIMESTAMP NULL,
  `rejected_at` TIMESTAMP NULL,
  `rejection_reason` TEXT NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `formation_steps_formation_process_id_foreign` (`formation_process_id`),
  KEY `formation_steps_validated_by_foreign` (`validated_by`),
  CONSTRAINT `formation_steps_formation_process_id_foreign` FOREIGN KEY (`formation_process_id`) REFERENCES `formation_processes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `formation_steps_validated_by_foreign` FOREIGN KEY (`validated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note: If JSON is not supported, replace `step_data` JSON with `step_data` TEXT

-- ============================================
-- 8. driver_formations
-- ============================================
DROP TABLE IF EXISTS `driver_formations`;

CREATE TABLE `driver_formations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `driver_id` BIGINT UNSIGNED NOT NULL,
  `formation_id` BIGINT UNSIGNED NOT NULL,
  `formation_process_id` BIGINT UNSIGNED NULL,
  `status` ENUM('done', 'planned') NOT NULL DEFAULT 'planned',
  `planned_at` DATE NULL,
  `due_at` DATE NULL,
  `done_at` DATE NULL,
  `progress_percent` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `validation_status` ENUM('pending', 'in_progress', 'validated', 'rejected') NOT NULL DEFAULT 'pending',
  `certificate_path` VARCHAR(255) NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `driver_formations_driver_id_foreign` (`driver_id`),
  KEY `driver_formations_formation_id_foreign` (`formation_id`),
  KEY `driver_formations_formation_process_id_foreign` (`formation_process_id`),
  CONSTRAINT `driver_formations_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `driver_formations_formation_id_foreign` FOREIGN KEY (`formation_id`) REFERENCES `formations` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `driver_formations_formation_process_id_foreign` FOREIGN KEY (`formation_process_id`) REFERENCES `formation_processes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. turnovers
-- ============================================
DROP TABLE IF EXISTS `turnovers`;

CREATE TABLE `turnovers` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `departure_date` DATE NOT NULL,
  `flotte` VARCHAR(255) NULL,
  `driver_id` BIGINT UNSIGNED NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `position` VARCHAR(255) NULL,
  `departure_reason` TEXT NOT NULL,
  `interview_notes` TEXT NULL,
  `interviewed_by` VARCHAR(255) NULL,
  `interview_answers` JSON NULL,
  `observations` TEXT NULL,
  `turnover_pdf_path` VARCHAR(255) NULL,
  `status` ENUM('pending', 'confirmed') NOT NULL DEFAULT 'pending',
  `confirmed_at` TIMESTAMP NULL,
  `confirmed_by` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `turnovers_driver_id_foreign` (`driver_id`),
  KEY `turnovers_user_id_foreign` (`user_id`),
  KEY `turnovers_confirmed_by_foreign` (`confirmed_by`),
  CONSTRAINT `turnovers_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `turnovers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `turnovers_confirmed_by_foreign` FOREIGN KEY (`confirmed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note: If JSON is not supported, replace `interview_answers` JSON with `interview_answers` TEXT

-- ============================================
-- 10. driver_concerns
-- ============================================
DROP TABLE IF EXISTS `driver_concerns`;

CREATE TABLE `driver_concerns` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `reported_at` DATE NOT NULL,
  `driver_id` BIGINT UNSIGNED NOT NULL,
  `vehicle_licence_plate` VARCHAR(255) NULL,
  `concern_type` VARCHAR(255) NULL,
  `description` TEXT NULL,
  `immediate_action` TEXT NULL,
  `responsible_party` VARCHAR(255) NULL,
  `status` ENUM('open', 'in_progress', 'resolved', 'closed') NOT NULL DEFAULT 'open',
  `resolution_comments` TEXT NULL,
  `completion_date` DATE NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `driver_concerns_driver_id_foreign` (`driver_id`),
  CONSTRAINT `driver_concerns_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 11. organigram_members
-- ============================================
DROP TABLE IF EXISTS `organigram_members`;

CREATE TABLE `organigram_members` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `position` ENUM('DG', 'DGA', 'COMPTABILITE', 'IT', 'OBC', 'HSSE', 'DEPOT_ET_EXPLOITATION', 'MAINTENANCE', 'MONITEUR', 'DEPOT', 'CHAUFFEURS') NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `revision` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `organigram_members_position_index` (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 12. changement_types
-- ============================================
DROP TABLE IF EXISTS `changement_types`;

CREATE TABLE `changement_types` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `code` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `changement_types_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 13. principale_cretaires
-- ============================================
DROP TABLE IF EXISTS `principale_cretaires`;

CREATE TABLE `principale_cretaires` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `changement_type_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `code` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `principale_cretaires_changement_type_id_code_unique` (`changement_type_id`, `code`),
  KEY `principale_cretaires_changement_type_id_foreign` (`changement_type_id`),
  CONSTRAINT `principale_cretaires_changement_type_id_foreign` FOREIGN KEY (`changement_type_id`) REFERENCES `changement_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 14. sous_cretaires
-- ============================================
DROP TABLE IF EXISTS `sous_cretaires`;

CREATE TABLE `sous_cretaires` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `principale_cretaire_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sous_cretaires_principale_cretaire_id_name_unique` (`principale_cretaire_id`, `name`),
  KEY `sous_cretaires_principale_cretaire_id_foreign` (`principale_cretaire_id`),
  CONSTRAINT `sous_cretaires_principale_cretaire_id_foreign` FOREIGN KEY (`principale_cretaire_id`) REFERENCES `principale_cretaires` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 15. changements
-- ============================================
DROP TABLE IF EXISTS `changements`;

CREATE TABLE `changements` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `changement_type_id` BIGINT UNSIGNED NOT NULL,
  `date_changement` DATE NOT NULL,
  `description_changement` TEXT NOT NULL,
  `responsable_changement` ENUM('RH', 'DGA', 'QHSE') NOT NULL,
  `impact` TEXT NULL,
  `action` TEXT NULL,
  `status` ENUM('draft', 'in_progress', 'completed', 'approved') NOT NULL DEFAULT 'draft',
  `check_list_path` VARCHAR(255) NULL,
  `current_step` TINYINT NOT NULL DEFAULT 1,
  `created_by` VARCHAR(255) NULL,
  `validated_by` VARCHAR(255) NULL,
  `validated_at` TIMESTAMP NULL,
  `rejected_at` TIMESTAMP NULL,
  `rejection_reason` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `changements_changement_type_id_foreign` (`changement_type_id`),
  CONSTRAINT `changements_changement_type_id_foreign` FOREIGN KEY (`changement_type_id`) REFERENCES `changement_types` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 16. changement_steps
-- ============================================
DROP TABLE IF EXISTS `changement_steps`;

CREATE TABLE `changement_steps` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `changement_id` BIGINT UNSIGNED NOT NULL,
  `step_number` TINYINT NOT NULL,
  `step_data` JSON NULL,
  `status` ENUM('pending', 'validated', 'rejected') NOT NULL DEFAULT 'pending',
  `validated_by` VARCHAR(255) NULL,
  `validated_at` TIMESTAMP NULL,
  `rejected_at` TIMESTAMP NULL,
  `rejection_reason` TEXT NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `changement_steps_changement_id_foreign` (`changement_id`),
  CONSTRAINT `changement_steps_changement_id_foreign` FOREIGN KEY (`changement_id`) REFERENCES `changements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note: If JSON is not supported, replace `step_data` JSON with `step_data` TEXT

-- ============================================
-- 17. changement_checklist_results
-- ============================================
DROP TABLE IF EXISTS `changement_checklist_results`;

CREATE TABLE `changement_checklist_results` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `changement_id` BIGINT UNSIGNED NOT NULL,
  `sous_cretaire_id` BIGINT UNSIGNED NOT NULL,
  `status` ENUM('OK', 'KO', 'N/A') NOT NULL DEFAULT 'N/A',
  `observation` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chg_checklist_chg_sous_unique` (`changement_id`, `sous_cretaire_id`),
  KEY `changement_checklist_results_changement_id_foreign` (`changement_id`),
  KEY `changement_checklist_results_sous_cretaire_id_foreign` (`sous_cretaire_id`),
  CONSTRAINT `changement_checklist_results_changement_id_foreign` FOREIGN KEY (`changement_id`) REFERENCES `changements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `changement_checklist_results_sous_cretaire_id_foreign` FOREIGN KEY (`sous_cretaire_id`) REFERENCES `sous_cretaires` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 18. coaching_sessions
-- ============================================
DROP TABLE IF EXISTS `coaching_sessions`;

CREATE TABLE `coaching_sessions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `driver_id` BIGINT UNSIGNED NOT NULL,
  `flotte_id` BIGINT UNSIGNED NULL,
  `date` DATE NULL,
  `date_fin` DATE NULL,
  `type` ENUM('initial', 'suivi', 'correctif', 'route_analysing', 'obc_suite', 'other') NOT NULL DEFAULT 'initial',
  `route_taken` VARCHAR(255) NULL,
  `moniteur` VARCHAR(255) NULL,
  `assessment` TEXT NULL,
  `status` ENUM('planned', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'planned',
  `validity_days` INT NOT NULL DEFAULT 3,
  `next_planning_session` DATE NULL,
  `score` INT NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `coaching_sessions_driver_id_foreign` (`driver_id`),
  KEY `coaching_sessions_flotte_id_foreign` (`flotte_id`),
  CONSTRAINT `coaching_sessions_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `coaching_sessions_flotte_id_foreign` FOREIGN KEY (`flotte_id`) REFERENCES `flottes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- END OF SCRIPT
-- ============================================
-- Notes:
-- 1. If your MySQL/MariaDB version doesn't support JSON type, 
--    replace all `JSON` with `TEXT` in the above CREATE TABLE statements
-- 2. Make sure all referenced tables (drivers, users, flottes, etc.) exist before running this script
-- 3. Foreign key constraints will fail if referenced tables don't exist
-- 4. Run this script in order as dependencies are respected

