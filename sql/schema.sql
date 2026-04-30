-- OTR Express Driver Referrals — MySQL schema
-- Import this via phpMyAdmin (Import tab) or run install.php.

CREATE TABLE IF NOT EXISTS admin_users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admin_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS referrers (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(190) NULL,
  phone VARCHAR(30) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_referrer_email (email),
  KEY idx_referrer_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS referrals (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  referrer_id INT UNSIGNED NOT NULL,
  driver_name VARCHAR(255) NOT NULL,
  driver_email VARCHAR(190) NULL,
  driver_phone VARCHAR(30) NOT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'SUBMITTED',
  rejection_reason TEXT NULL,
  share_with_referrer VARCHAR(10) NOT NULL DEFAULT 'PENDING',
  share_consent_at DATETIME NULL,
  started_working_at DATETIME NULL,
  payout_eligible_at DATETIME NULL,
  paid_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_referrals_referrer (referrer_id),
  KEY idx_referrals_status (status),
  CONSTRAINT fk_referrals_referrer
    FOREIGN KEY (referrer_id) REFERENCES referrers(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comments (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  referral_id INT UNSIGNED NOT NULL,
  author VARCHAR(20) NOT NULL DEFAULT 'ADMIN',
  body TEXT NOT NULL,
  visible_to_referrer TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_comments_referral (referral_id),
  CONSTRAINT fk_comments_referral
    FOREIGN KEY (referral_id) REFERENCES referrals(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS stage_updates (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  referral_id INT UNSIGNED NOT NULL,
  status VARCHAR(40) NOT NULL,
  note TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_stage_updates_referral (referral_id),
  CONSTRAINT fk_stage_updates_referral
    FOREIGN KEY (referral_id) REFERENCES referrals(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
