-- ============================================================
-- MIGRATION: migrate_auth_v2.sql
-- PURPOSE   : Auth audit fixes — DB-level changes only
-- PROJECT   : ARS eCommerce (ars_ecommerce)
-- DATE      : 2026-04-11
-- RUN AFTER : migrate_auth_fix.sql, migrate_production_fixes.sql
-- SAFE FOR  : MySQL 8 / MariaDB 10.x (idempotent)
-- ============================================================

USE ars_ecommerce;

-- ============================================================
-- STEP 1 — Prevent duplicate reviews per user per product
-- Without this a user can submit unlimited reviews for the
-- same product, inflating ratings.
-- ============================================================

ALTER TABLE product_reviews
    ADD UNIQUE KEY IF NOT EXISTS `uq_user_product_review` (`user_id`, `product_id`);


-- ============================================================
-- STEP 2 — Add FK on user_sessions.user_id if not yet present
-- Uses a stored procedure for MySQL 5.7 / MariaDB compatibility
-- (those versions don't support ADD CONSTRAINT IF NOT EXISTS).
-- ============================================================

DROP PROCEDURE IF EXISTS ars_add_session_fk;
DELIMITER $$
CREATE PROCEDURE ars_add_session_fk()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA    = DATABASE()
          AND TABLE_NAME      = 'user_sessions'
          AND CONSTRAINT_NAME = 'fk_user_sessions_user'
    ) THEN
        ALTER TABLE user_sessions
            ADD CONSTRAINT `fk_user_sessions_user`
                FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
                ON DELETE CASCADE;
    END IF;
END$$
DELIMITER ;
CALL ars_add_session_fk();
DROP PROCEDURE IF EXISTS ars_add_session_fk;


-- ============================================================
-- POST-MIGRATION CHECKS
-- ============================================================
/*
-- UNIQUE on product_reviews present?
SHOW INDEX FROM product_reviews WHERE Key_name = 'uq_user_product_review';
-- Expected: 1 row

-- FK on user_sessions present?
SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'ars_ecommerce'
  AND TABLE_NAME   = 'user_sessions'
  AND CONSTRAINT_NAME LIKE 'fk_%';
-- Expected: fk_user_sessions_user
*/


-- ============================================================
-- ROLLBACK
-- ============================================================
/*
ALTER TABLE product_reviews DROP INDEX IF EXISTS `uq_user_product_review`;
ALTER TABLE user_sessions   DROP FOREIGN KEY IF EXISTS `fk_user_sessions_user`;
*/
