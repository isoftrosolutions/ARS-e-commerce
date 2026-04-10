-- ============================================================
-- MIGRATION: migrate_production_fixes.sql
-- PURPOSE   : (1) Add remember_token column for persistent login
--             (2) Normalize existing mobile numbers to digit-only
--                 local format (strip +977 country code prefix)
--             (3) Remove stale is_verified / verification_expires
--                 references are now handled by email_verified_at
-- PROJECT   : ARS eCommerce (ars_ecommerce database)
-- DATE      : 2026-04-10
-- SAFE FOR  : MySQL 5.7+ / 8.0  (idempotent, uses IF NOT EXISTS)
-- RUN AFTER : migrate_auth_fix.sql (which adds OTP columns)
-- ============================================================

USE ars_ecommerce;

-- ============================================================
-- STEP 0 — PRE-MIGRATION DRY-RUN CHECKS
-- Run these SELECTs manually before applying anything.
-- ============================================================
/*
-- Check how many mobiles have the +977 prefix (to be normalised)
SELECT COUNT(*) AS rows_to_normalise
FROM users
WHERE mobile REGEXP '^\\+977';

-- Check how many mobiles have dashes/spaces (also to be normalised)
SELECT COUNT(*) AS rows_with_non_digits
FROM users
WHERE mobile REGEXP '[^0-9]';

-- Preview what the normalised values would look like
SELECT id, mobile,
       REGEXP_REPLACE(
           CASE
               WHEN mobile REGEXP '^\\+?977[0-9]{9,10}$'
               THEN SUBSTR(REGEXP_REPLACE(mobile, '[^0-9]', ''), 4)
               ELSE REGEXP_REPLACE(mobile, '[^0-9]', '')
           END,
           '[^0-9]', ''
       ) AS normalised_mobile
FROM users
WHERE mobile REGEXP '[^0-9]';

-- Does the remember_token column already exist?
SELECT COLUMN_NAME FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'ars_ecommerce'
  AND TABLE_NAME   = 'users'
  AND COLUMN_NAME  = 'remember_token';
*/


-- ============================================================
-- STEP 1 — ADD remember_token COLUMN (idempotent)
-- Used by the "Remember Me" persistent-login feature.
-- ============================================================

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS remember_token VARCHAR(64) NULL DEFAULT NULL
        COMMENT 'SHA-256 hash of the persistent remember-me cookie token'
        AFTER verification_token;

CREATE INDEX IF NOT EXISTS idx_users_remember_token
    ON users (remember_token);


-- ============================================================
-- STEP 2 — NORMALISE EXISTING MOBILE NUMBERS
--
-- Strategy:
--   a. Strip all non-digit characters (+, -, spaces, parentheses)
--   b. If the result is 13 digits starting with 977 (Nepal E.164
--      without leading +), strip the first 3 digits.
--   c. The remaining value is the canonical 10-digit local number.
--
-- This fixes existing users who registered with +9779811144402
-- so they can now log in with 9811144402.
-- ============================================================

-- Create a helper procedure to run the normalisation safely
-- (Procedures allow us to use variables in the UPDATE logic)
DROP PROCEDURE IF EXISTS ars_normalise_mobiles;

DELIMITER $$
CREATE PROCEDURE ars_normalise_mobiles()
BEGIN
    -- Strip non-digits + remove leading 977 country code
    UPDATE users
    SET mobile = (
        CASE
            -- 13-digit E.164: digits start with 977 → strip first 3 digits
            WHEN LENGTH(REGEXP_REPLACE(mobile, '[^0-9]', '')) = 13
             AND REGEXP_REPLACE(mobile, '[^0-9]', '') REGEXP '^977'
            THEN SUBSTR(REGEXP_REPLACE(mobile, '[^0-9]', ''), 4)

            -- Otherwise just strip non-digits
            ELSE REGEXP_REPLACE(mobile, '[^0-9]', '')
        END
    )
    WHERE mobile REGEXP '[^0-9]'          -- has non-digit chars
       OR (
           LENGTH(REGEXP_REPLACE(mobile, '[^0-9]', '')) = 13
           AND REGEXP_REPLACE(mobile, '[^0-9]', '') REGEXP '^977'
       );
END$$
DELIMITER ;

CALL ars_normalise_mobiles();
DROP PROCEDURE IF EXISTS ars_normalise_mobiles;


-- ============================================================
-- STEP 3 — POST-MIGRATION VALIDATION
-- ============================================================
/*
-- No un-normalised mobiles remain?
SELECT COUNT(*) AS remaining_non_digits
FROM users
WHERE mobile REGEXP '[^0-9]';
-- Expected: 0

-- No 13-digit Nepal E.164 leftovers?
SELECT COUNT(*) AS remaining_e164
FROM users
WHERE LENGTH(mobile) = 13 AND mobile REGEXP '^977';
-- Expected: 0

-- remember_token column present?
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'ars_ecommerce'
  AND TABLE_NAME   = 'users'
  AND COLUMN_NAME  = 'remember_token';
-- Expected: 1 row

-- Spot check normalised data
SELECT id, full_name, mobile FROM users ORDER BY id LIMIT 10;
*/


-- ============================================================
-- ROLLBACK (only if applied in last few minutes and no logins
-- have occurred since that wrote remember_token)
-- ============================================================
/*
DROP INDEX IF EXISTS idx_users_remember_token ON users;
ALTER TABLE users DROP COLUMN IF EXISTS remember_token;

-- Mobile normalisation CANNOT be automatically rolled back.
-- Restore from a pre-migration backup if needed.
*/
