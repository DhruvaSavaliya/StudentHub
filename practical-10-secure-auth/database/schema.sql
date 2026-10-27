-- Practical 10: run once after importing Practical 9's users table.
USE studenthub;

-- Intermediate requirement: record the last successful login.
ALTER TABLE users
  ADD COLUMN last_login_at DATETIME NULL DEFAULT NULL;

-- Remember-me values are opaque random tokens. Store only the validator hash.
CREATE TABLE IF NOT EXISTS auth_remember_tokens (
  selector CHAR(24) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  validator_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_used_at DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (selector),
  KEY idx_auth_remember_user (user_id),
  KEY idx_auth_remember_expiry (expires_at),
  CONSTRAINT fk_auth_remember_user
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
