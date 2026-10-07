-- StudentHub Practical 8 schema
-- Target: MySQL 8+ or a compatible MariaDB release

CREATE DATABASE IF NOT EXISTS studenthub
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE studenthub;

CREATE TABLE IF NOT EXISTS courses (
  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  course_code VARCHAR(12) NOT NULL,
  course_name VARCHAR(100) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_courses_code (course_code),
  UNIQUE KEY uq_courses_name (course_name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS students (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  student_code VARCHAR(24) NOT NULL,
  first_name VARCHAR(60) NOT NULL,
  last_name VARCHAR(60) NOT NULL,
  email VARCHAR(254) NOT NULL,
  mobile VARCHAR(24) NULL,
  course_id SMALLINT UNSIGNED NOT NULL,
  year_of_study TINYINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_students_code (student_code),
  UNIQUE KEY uq_students_email (email),
  KEY idx_students_course_year (course_id, year_of_study),
  CONSTRAINT chk_students_year CHECK (year_of_study BETWEEN 1 AND 4),
  CONSTRAINT fk_students_course
    FOREIGN KEY (course_id) REFERENCES courses (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS events (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(140) NOT NULL,
  description TEXT NULL,
  venue VARCHAR(120) NOT NULL,
  starts_at DATETIME NOT NULL,
  capacity SMALLINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_events_starts_at (starts_at),
  CONSTRAINT chk_events_capacity CHECK (capacity > 0)
) ENGINE=InnoDB;

-- A student can register for many events; each event can have many students.
CREATE TABLE IF NOT EXISTS registrations (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  student_id BIGINT UNSIGNED NOT NULL,
  event_id BIGINT UNSIGNED NOT NULL,
  registered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status ENUM('registered', 'cancelled') NOT NULL DEFAULT 'registered',
  PRIMARY KEY (id),
  UNIQUE KEY uq_registration_student_event (student_id, event_id),
  KEY idx_registrations_event_status (event_id, status),
  CONSTRAINT fk_registrations_student
    FOREIGN KEY (student_id) REFERENCES students (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_registrations_event
    FOREIGN KEY (event_id) REFERENCES events (id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;
