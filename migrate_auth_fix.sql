-- ============================================================
-- MIGRATION: migrate_auth_fix.sql
-- PURPOSE   : Add OTP-based password reset columns and clean
--             stale tokens. Safe to re-run (IF NOT EXISTS).
-- PROJECT   : ARS eCommerce (ars_ecommerce database)
-- DATE      : 2026-04-10
-- SAFE FOR  : MySQL 5.7+ / 8.0  (zero-downtime, no DROP)
-- ROLLBACK  : See ROLLBACK section at the bottom
-- ============================================================

USE ars_ecommerce;

-- ============================================================
-- STEP 0 — PRE-MIGRATION DRY-RUN CHECKS
-- Run these SELECTs manually before applying anything.
-- ============================================================
/*
-- Check which reset columns already exist
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'ars_ecommerce'
  AND TABLE_NAME   = 'users'
  AND COLUMN_NAME IN ('reset_token','reset_expires','reset_token_used_at',
                      'otp_attempts','email_verified_at','verification_token');

-- How many users have a non-expired active OTP token?
SELECT COUNT(*) AS active_otp_tokens
FROM users
WHERE reset_expires > NOW()
  AND reset_token IS NOT NULL;

-- How many users have a stale (expired but not cleared) token?
SELECT COUNT(*) AS stale_tokens
FROM users
WHERE reset_expires < NOW()
  AND reset_token IS NOT NULL;

-- Any users with a NULL or empty password hash?
SELECT COUNT(*) AS users_with_null_password
FROM users
WHERE password IS NULL OR password = '' OR LENGTH(password) < 20;
*/


-- ============================================================
-- STEP 1 — ADD MISSING COLUMNS (idempotent with IF NOT EXISTS)
-- otp_attempts: tracks failed OTP entries; max 5 before lockout
-- ============================================================

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS reset_token          VARCHAR(255)  NULL          AFTER role,
    ADD COLUMN IF NOT EXISTS reset_expires        DATETIME      NULL          AFTER reset_token,
    ADD COLUMN IF NOT EXISTS reset_token_used_at  DATETIME      NULL          AFTER reset_expires,
    ADD COLUMN IF NOT EXISTS otp_attempts         TINYINT       NOT NULL DEFAULT 0 AFTER reset_token_used_at,
    ADD COLUMN IF NOT EXISTS email_verified_at    TIMESTAMP     NULL          AFTER otp_attempts,
    ADD COLUMN IF NOT EXISTS verification_token   VARCHAR(255)  NULL          AFTER email_verified_at;


-- ============================================================
-- STEP 2 — CLEAN UP STALE / USED TOKENS (non-blocking UPDATE)
-- ============================================================

-- Clear expired tokens
UPDATE users
SET reset_token    = NULL,
    reset_expires  = NULL,
    otp_attempts   = 0
WHERE reset_expires < NOW()
  AND reset_token IS NOT NULL;

-- Clear tokens that were already consumed (used_at is set)
UPDATE users
SET reset_token   = NULL,
    reset_expires = NULL
WHERE reset_token_used_at IS NOT NULL
  AND reset_token IS NOT NULL;


-- ============================================================
-- STEP 3 — ADD INDEX FOR OTP LOOKUPS (INPLACE, no table lock)
-- ============================================================

ALTER TABLE users DROP INDEX IF EXISTS idx_users_reset_expires;

ALTER TABLE users
    ADD INDEX idx_users_reset_expires (reset_expires);


-- ============================================================
-- STEP 4 — POST-MIGRATION VALIDATION
-- ============================================================
/*
-- All required columns exist?
SELECT COLUMN_NAME
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'ars_ecommerce'
  AND TABLE_NAME   = 'users'
  AND COLUMN_NAME IN ('reset_token','reset_expires','reset_token_used_at',
                      'otp_attempts','email_verified_at','verification_token')
ORDER BY ORDINAL_POSITION;
-- Expected: 6 rows

-- No stale tokens remain?
SELECT COUNT(*) AS stale_after_migration
FROM users
WHERE reset_expires < NOW() AND reset_token IS NOT NULL;
-- Expected: 0

-- Index present?
SHOW INDEX FROM users WHERE Key_name = 'idx_users_reset_expires';
-- Expected: 1 row
*/


-- ============================================================
-- ROLLBACK (only if run within the last few minutes and no
-- OTP-reset traffic has occurred since)
-- ============================================================
/*
ALTER TABLE users DROP INDEX IF EXISTS idx_users_reset_expires;

-- Only drop if columns were newly added AND hold no data worth keeping
ALTER TABLE users
    DROP COLUMN IF EXISTS reset_token,
    DROP COLUMN IF EXISTS reset_expires,
    DROP COLUMN IF EXISTS reset_token_used_at,
    DROP COLUMN IF EXISTS otp_attempts,
    DROP COLUMN IF EXISTS email_verified_at,
    DROP COLUMN IF EXISTS verification_token;
*/
