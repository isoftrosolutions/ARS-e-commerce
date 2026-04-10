-- ============================================================
-- MIGRATION: migrate_auth_fix.sql
-- PURPOSE   : Fix login failures caused by missing password-reset
--             columns and stale/invalid reset tokens
-- PROJECT   : ARS eCommerce (ars_ecommerce database)
-- DATE      : 2026-04-10
-- SAFE FOR  : MySQL 5.7+ / 8.0  (zero-downtime, no DROP)
-- ROLLBACK  : See ROLLBACK section at the bottom
-- ============================================================

USE ars_ecommerce;

-- ============================================================
-- STEP 0 — PRE-MIGRATION DRY-RUN CHECKS
-- Run these SELECTs manually before applying anything.
-- They tell you what state the schema and data are in.
-- ============================================================
/*
-- Check which reset columns already exist
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'ars_ecommerce'
  AND TABLE_NAME   = 'users'
  AND COLUMN_NAME IN ('reset_token','reset_expires','reset_token_used_at',
                      'email_verified_at','verification_token');

-- How many users have a non-expired active reset token?
SELECT COUNT(*) AS active_reset_tokens
FROM users
WHERE reset_expires > NOW()
  AND reset_token IS NOT NULL;

-- How many users have a stale (expired but not cleared) token?
SELECT COUNT(*) AS stale_reset_tokens
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
-- All added as NULL so no existing rows are affected.
-- ============================================================

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS reset_token          VARCHAR(255)  NULL AFTER role,
    ADD COLUMN IF NOT EXISTS reset_expires        DATETIME      NULL AFTER reset_token,
    ADD COLUMN IF NOT EXISTS reset_token_used_at  DATETIME      NULL AFTER reset_expires,
    ADD COLUMN IF NOT EXISTS email_verified_at    TIMESTAMP     NULL AFTER reset_token_used_at,
    ADD COLUMN IF NOT EXISTS verification_token   VARCHAR(255)  NULL AFTER email_verified_at;


-- ============================================================
-- STEP 2 — CLEAN UP STALE OTP / RESET TOKENS (non-blocking)
-- Clears expired tokens so they cannot be mistakenly accepted
-- if the expiry-check logic ever has an off-by-one regression.
-- ============================================================

UPDATE users
SET reset_token         = NULL,
    reset_expires       = NULL
WHERE reset_expires < NOW()
  AND reset_token IS NOT NULL;


-- ============================================================
-- STEP 3 — CLEAR ANY TOKENS ALREADY CONSUMED
-- (reset_token_used_at set = token was spent; clear the hash)
-- ============================================================

UPDATE users
SET reset_token   = NULL,
    reset_expires = NULL
WHERE reset_token_used_at IS NOT NULL
  AND reset_token IS NOT NULL;


-- ============================================================
-- STEP 4 — ADD INDEX FOR RESET TOKEN LOOKUPS (INPLACE, no lock)
-- Speeds up the full-table scan in reset-password.php
-- ============================================================

-- Drop first if it already exists (safe re-run)
ALTER TABLE users
    DROP INDEX IF EXISTS idx_users_reset_expires;

ALTER TABLE users
    ADD INDEX idx_users_reset_expires (reset_expires);


-- ============================================================
-- STEP 5 — POST-MIGRATION VALIDATION
-- Run these after applying to confirm success.
-- ============================================================
/*
-- All five columns now exist?
SELECT COLUMN_NAME
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'ars_ecommerce'
  AND TABLE_NAME   = 'users'
  AND COLUMN_NAME IN ('reset_token','reset_expires','reset_token_used_at',
                      'email_verified_at','verification_token')
ORDER BY ORDINAL_POSITION;
-- Expected: 5 rows

-- No stale tokens remain?
SELECT COUNT(*) AS stale_after_migration
FROM users
WHERE reset_expires < NOW()
  AND reset_token IS NOT NULL;
-- Expected: 0

-- Index exists?
SHOW INDEX FROM users WHERE Key_name = 'idx_users_reset_expires';
-- Expected: 1 row
*/


-- ============================================================
-- ROLLBACK (only if applied in the last few minutes and no
-- password-reset traffic has hit since)
-- ============================================================
/*
-- Remove the index
ALTER TABLE users DROP INDEX IF EXISTS idx_users_reset_expires;

-- Remove added columns (ONLY if they were newly added and hold
-- no critical data — verify with the dry-run SELECT first)
ALTER TABLE users
    DROP COLUMN IF EXISTS reset_token,
    DROP COLUMN IF EXISTS reset_expires,
    DROP COLUMN IF EXISTS reset_token_used_at,
    DROP COLUMN IF EXISTS email_verified_at,
    DROP COLUMN IF EXISTS verification_token;
*/
