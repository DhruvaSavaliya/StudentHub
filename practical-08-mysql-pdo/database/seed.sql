-- Fictional classroom data. Import schema.sql before this file.
USE studenthub;

INSERT INTO courses (id, course_code, course_name) VALUES
  (1, 'IT', 'Information Technology'),
  (2, 'CE', 'Computer Engineering'),
  (3, 'CSE', 'Computer Science')
ON DUPLICATE KEY UPDATE course_name = VALUES(course_name);

INSERT INTO students
  (id, student_code, first_name, last_name, email, mobile, course_id, year_of_study)
VALUES
  (101, 'DEMO-IT-001', 'Aarav', 'Patel', 'aarav.patel@example.edu', '+91 90000 00001', 1, 3),
  (102, 'DEMO-CE-002', 'Diya', 'Shah', 'diya.shah@example.edu', '+91 90000 00002', 2, 2),
  (103, 'DEMO-CS-003', 'Reyansh', 'Mehta', 'reyansh.mehta@example.edu', '+91 90000 00003', 3, 1)
ON DUPLICATE KEY UPDATE email = VALUES(email);

INSERT INTO events (id, title, description, venue, starts_at, capacity) VALUES
  (201, 'Student Coding Meetup', 'A peer-led coding and project session.', 'Seminar Hall A', '2026-11-15 10:00:00', 80),
  (202, 'AI Workshop', 'An introductory workshop on applied AI.', 'Innovation Lab', '2026-11-20 13:30:00', 45),
  (203, 'Campus Hackathon', 'A one-day team programming challenge.', 'Main Auditorium', '2026-12-05 09:00:00', 120)
ON DUPLICATE KEY UPDATE title = VALUES(title);

INSERT INTO registrations (student_id, event_id, status) VALUES
  (101, 201, 'registered'),
  (102, 201, 'registered'),
  (101, 202, 'registered'),
  (103, 203, 'registered')
ON DUPLICATE KEY UPDATE status = VALUES(status);
