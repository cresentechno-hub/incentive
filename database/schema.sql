-- ============================================================
-- Agent & Salesperson Incentive Management System
-- Database Schema — MySQL 8.0+ / MariaDB 10.4+
-- Local dev: run public/setup.php once to create DB, import schema, and seed demo users.
-- Production: import this file (or a clean dump) via phpMyAdmin, then create the admin user.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ── Roles ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `roles` (
    `id`          TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`        ENUM('Admin','Manager','Agent') NOT NULL UNIQUE,
    `permissions` JSON,
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Users ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `role_id`       TINYINT UNSIGNED NOT NULL,
    `team_id`       INT UNSIGNED DEFAULT NULL,
    `name`          VARCHAR(100) NOT NULL,
    `email`         VARCHAR(150) NOT NULL UNIQUE,
    `username`      VARCHAR(50) DEFAULT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `status`        ENUM('active','inactive') DEFAULT 'active',
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT,
    INDEX `idx_email` (`email`),
    INDEX `idx_role`  (`role_id`),
    INDEX `idx_team`  (`team_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Teams ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `teams` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(100) NOT NULL,
    `manager_id` INT UNSIGNED DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`manager_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `users`
    ADD CONSTRAINT `fk_users_team` FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE SET NULL;

-- ── Deals ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `deals` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `agent_id`        INT UNSIGNED NOT NULL,
    `client_name`     VARCHAR(150) NOT NULL,
    `product_service` VARCHAR(100) DEFAULT NULL,
    `deal_value`      DECIMAL(15,2) NOT NULL,
    `closing_date`    DATE NOT NULL,
    `status`          ENUM('Pending','Approved','Rejected','Paid') DEFAULT 'Pending',
    `notes`           TEXT DEFAULT NULL,
    `approved_by`     INT UNSIGNED DEFAULT NULL,
    `approved_at`     DATETIME DEFAULT NULL,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`agent_id`)    REFERENCES `users`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_agent`        (`agent_id`),
    INDEX `idx_status`       (`status`),
    INDEX `idx_closing_date` (`closing_date`),
    INDEX `idx_period`       (`agent_id`, `closing_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Incentive Rules ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `incentive_rules` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`          VARCHAR(100) NOT NULL,
    `description`   TEXT DEFAULT NULL,
    `rule_type`     ENUM('flat','percentage','tier') NOT NULL,
    `target_metric` ENUM('deal_value','deal_count','period_total') DEFAULT 'deal_value',
    `rate_value`    DECIMAL(10,4) NOT NULL COMMENT 'Fixed amount (flat) or % rate (percentage/tier)',
    `min_threshold` DECIMAL(15,2) DEFAULT 0.00,
    `max_threshold` DECIMAL(15,2) DEFAULT NULL COMMENT 'NULL = no upper limit',
    `start_date`    DATE NOT NULL,
    `end_date`      DATE DEFAULT NULL,
    `is_active`     TINYINT(1) DEFAULT 1,
    `created_by`    INT UNSIGNED DEFAULT NULL,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_active`     (`is_active`, `start_date`, `end_date`),
    INDEX `idx_rule_type`  (`rule_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Agent Incentives (Ledger) ────────────────────────────────
CREATE TABLE IF NOT EXISTS `agent_incentives` (
    `id`                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `agent_id`           INT UNSIGNED NOT NULL,
    `deal_id`            INT UNSIGNED DEFAULT NULL,
    `rule_id`            INT UNSIGNED NOT NULL,
    `earned_amount`      DECIMAL(15,2) NOT NULL,
    `status`             ENUM('pending','approved','rejected','paid') DEFAULT 'pending',
    `calculation_notes`  TEXT DEFAULT NULL,
    `payout_period`      CHAR(7) NOT NULL COMMENT 'YYYY-MM',
    `payout_batch_id`    INT UNSIGNED DEFAULT NULL,
    `override_by`        INT UNSIGNED DEFAULT NULL,
    `override_at`        DATETIME DEFAULT NULL,
    `created_at`         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`agent_id`)        REFERENCES `users`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`deal_id`)         REFERENCES `deals`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`rule_id`)         REFERENCES `incentive_rules`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`override_by`)     REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_agent_period` (`agent_id`, `payout_period`),
    INDEX `idx_status`       (`status`),
    INDEX `idx_batch`        (`payout_batch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Payout Batches ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `payout_batches` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `batch_date`    DATE NOT NULL,
    `payout_period` CHAR(7) NOT NULL COMMENT 'YYYY-MM',
    `total_amount`  DECIMAL(15,2) DEFAULT 0.00,
    `agent_count`   SMALLINT UNSIGNED DEFAULT 0,
    `status`        ENUM('draft','processing','completed','cancelled') DEFAULT 'draft',
    `processed_by`  INT UNSIGNED DEFAULT NULL,
    `notes`         TEXT DEFAULT NULL,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`processed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_period` (`payout_period`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `agent_incentives`
    ADD CONSTRAINT `fk_incentives_batch`
    FOREIGN KEY (`payout_batch_id`) REFERENCES `payout_batches`(`id`) ON DELETE SET NULL;

-- ── Audit Log ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `audit_log` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT UNSIGNED DEFAULT NULL,
    `action`     VARCHAR(100) NOT NULL,
    `table_name` VARCHAR(50)  DEFAULT NULL,
    `record_id`  INT UNSIGNED DEFAULT NULL,
    `old_values` JSON DEFAULT NULL,
    `new_values` JSON DEFAULT NULL,
    `ip_address` VARCHAR(45)  DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_action`  (`action`),
    INDEX `idx_table`   (`table_name`, `record_id`),
    INDEX `idx_user`    (`user_id`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ── Seed: Roles ──────────────────────────────────────────────
INSERT INTO `roles` (`id`, `name`, `permissions`) VALUES
(1, 'Admin',   '{"all": true}'),
(2, 'Manager', '{"deals": ["read","update"], "agents": ["read"], "payouts": ["read","create"]}'),
(3, 'Agent',   '{"deals": ["read","create"], "incentives": ["read"]}');

-- ── Seed: Incentive Rules ────────────────────────────────────
-- Rules are seeded without created_by (set via setup.php after users exist)
INSERT INTO `incentive_rules`
    (`name`, `description`, `rule_type`, `target_metric`, `rate_value`, `min_threshold`, `max_threshold`, `start_date`, `end_date`, `is_active`)
VALUES
-- Flat bonus: close any deal ≥ $1,000 → earn $50 flat
('Starter Bonus',        'Flat $50 bonus for every deal ≥ $1,000',          'flat',       'deal_value',   50.0000,  1000.00,  NULL,      '2024-01-01', NULL, 1),
-- Flat bonus: close any deal ≥ $10,000 → earn $250 flat
('High-Value Bonus',     'Flat $250 bonus for deals ≥ $10,000',              'flat',       'deal_value', 250.0000, 10000.00,  NULL,      '2024-01-01', NULL, 1),
-- Percentage: 5% on all approved deals
('Standard Commission',  'Base 5% commission on every approved deal',        'percentage', 'deal_value',   5.0000,      0.00,  NULL,      '2024-01-01', NULL, 1),
-- Percentage: extra 3% on deals ≥ $5,000
('Premium Commission',   'Extra 3% commission on deals ≥ $5,000',           'percentage', 'deal_value',   3.0000,  5000.00,  NULL,      '2024-01-01', NULL, 1),
-- Tier: if monthly total sales $0–$9,999 → 2% kicker
('Tier Bronze',          'Monthly total $0–$9,999: 2% kicker on each deal', 'tier',       'period_total', 2.0000,      0.00,  9999.99,   '2024-01-01', NULL, 1),
-- Tier: monthly total $10k–$24,999 → 4% kicker
('Tier Silver',          'Monthly total $10k–$24,999: 4% kicker per deal',  'tier',       'period_total', 4.0000, 10000.00, 24999.99,   '2024-01-01', NULL, 1),
-- Tier: monthly total ≥ $25k → 6% kicker
('Tier Gold',            'Monthly total ≥ $25,000: 6% kicker per deal',     'tier',       'period_total', 6.0000, 25000.00,  NULL,      '2024-01-01', NULL, 1);
