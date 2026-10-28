-- Practical 11 extension: run once after importing Practical 8's schema.
USE studenthub;

-- Soft-delete state preserves referenced student rows in event registrations.
ALTER TABLE students
  ADD COLUMN status ENUM('active', 'inactive') NOT NULL DEFAULT 'active';

CREATE INDEX idx_students_status_course ON students (status, course_id);
