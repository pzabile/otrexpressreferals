-- Migration for an already-installed database (2026-04-25).
-- Makes emails optional for both referrers and referrals, and drops the
-- UNIQUE constraint on referrers.email so multiple referrers without an
-- email don't collide.
--
-- Run this once via phpMyAdmin → your DB → SQL tab → paste → Go.
-- Safe to re-run on a fresh schema (statements are guarded where MySQL
-- supports it).

-- 1) Allow NULL emails everywhere.
ALTER TABLE referrers  MODIFY email        VARCHAR(190) NULL;
ALTER TABLE referrals  MODIFY driver_email VARCHAR(190) NULL;

-- 2) Drop the old UNIQUE index on referrer email (idempotent guard:
--    if it doesn't exist this will error; ignore that error).
ALTER TABLE referrers DROP INDEX uq_referrer_email;

-- 3) Replace it with a non-unique index so lookups stay fast.
ALTER TABLE referrers ADD INDEX idx_referrer_email (email);

-- 4) Convert any pre-existing empty-string emails to NULL so the lookup
--    logic works consistently.
UPDATE referrers  SET email        = NULL WHERE email        = '';
UPDATE referrals  SET driver_email = NULL WHERE driver_email = '';
