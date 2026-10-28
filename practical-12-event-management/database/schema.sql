-- Practical 12: run once after importing Practical 8's schema.
USE studenthub;

ALTER TABLE events
  ADD COLUMN category VARCHAR(60) NOT NULL DEFAULT 'General',
  ADD COLUMN event_status ENUM('open', 'closed') NOT NULL DEFAULT 'open',
  ADD COLUMN poster_filename VARCHAR(80) NULL;

CREATE INDEX idx_events_category_status_starts
  ON events (category, event_status, starts_at);

CREATE TABLE IF NOT EXISTS event_audit_log (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  admin_user_id BIGINT UNSIGNED NULL,
  event_id BIGINT UNSIGNED NULL,
  event_title VARCHAR(140) NOT NULL,
  action ENUM('created', 'updated', 'deleted') NOT NULL,
  occurred_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_event_audit_time (occurred_at),
  KEY idx_event_audit_title (event_title),
  CONSTRAINT fk_event_audit_admin
    FOREIGN KEY (admin_user_id) REFERENCES users (id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_event_audit_event
    FOREIGN KEY (event_id) REFERENCES events (id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
