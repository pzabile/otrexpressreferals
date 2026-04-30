-- Migration: per-referral driver consent for status sharing.
-- Run once via phpMyAdmin -> your DB -> SQL tab -> paste -> Go.

ALTER TABLE referrals
  ADD COLUMN share_with_referrer VARCHAR(10) NOT NULL DEFAULT 'PENDING'
    AFTER rejection_reason,
  ADD COLUMN share_consent_at DATETIME NULL
    AFTER share_with_referrer;
