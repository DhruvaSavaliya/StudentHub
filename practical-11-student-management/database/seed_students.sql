-- Fictional Practical 11 dataset. Run schema.sql first and import this file once.
USE studenthub;

INSERT INTO students
  (student_code, first_name, last_name, email, mobile, course_id, year_of_study)
VALUES
  ('P11-DEMO-001', 'Aarav', 'Patel', 'aarav.patel001@example.test', '+91 90000 20001', 1, 1),
  ('P11-DEMO-002', 'Diya', 'Shah', 'diya.shah002@example.test', '+91 90000 20002', 2, 2),
  ('P11-DEMO-003', 'Reyansh', 'Mehta', 'reyansh.mehta003@example.test', '+91 90000 20003', 3, 3),
  ('P11-DEMO-004', 'Anaya', 'Desai', 'anaya.desai004@example.test', '+91 90000 20004', 1, 4),
  ('P11-DEMO-005', 'Vivaan', 'Joshi', 'vivaan.joshi005@example.test', '+91 90000 20005', 2, 1),
  ('P11-DEMO-006', 'Sara', 'Trivedi', 'sara.trivedi006@example.test', '+91 90000 20006', 3, 2),
  ('P11-DEMO-007', 'Arjun', 'Rao', 'arjun.rao007@example.test', '+91 90000 20007', 1, 3),
  ('P11-DEMO-008', 'Myra', 'Bhatt', 'myra.bhatt008@example.test', '+91 90000 20008', 2, 4),
  ('P11-DEMO-009', 'Kabir', 'Nair', 'kabir.nair009@example.test', '+91 90000 20009', 3, 1),
  ('P11-DEMO-010', 'Ira', 'Kapoor', 'ira.kapoor010@example.test', '+91 90000 20010', 1, 2),
  ('P11-DEMO-011', 'Advik', 'Reddy', 'advik.reddy011@example.test', '+91 90000 20011', 2, 3),
  ('P11-DEMO-012', 'Kiara', 'Iyer', 'kiara.iyer012@example.test', '+91 90000 20012', 3, 4),
  ('P11-DEMO-013', 'Ishaan', 'Pillai', 'ishaan.pillai013@example.test', '+91 90000 20013', 1, 1),
  ('P11-DEMO-014', 'Aadhya', 'Saxena', 'aadhya.saxena014@example.test', '+91 90000 20014', 2, 2),
  ('P11-DEMO-015', 'Vihaan', 'Kulkarni', 'vihaan.kulkarni015@example.test', '+91 90000 20015', 3, 3),
  ('P11-DEMO-016', 'Anvi', 'Chopra', 'anvi.chopra016@example.test', '+91 90000 20016', 1, 4),
  ('P11-DEMO-017', 'Krish', 'Malhotra', 'krish.malhotra017@example.test', '+91 90000 20017', 2, 1),
  ('P11-DEMO-018', 'Riya', 'Menon', 'riya.menon018@example.test', '+91 90000 20018', 3, 2),
  ('P11-DEMO-019', 'Atharv', 'Gandhi', 'atharv.gandhi019@example.test', '+91 90000 20019', 1, 3),
  ('P11-DEMO-020', 'Nisha', 'Verma', 'nisha.verma020@example.test', '+91 90000 20020', 2, 4);
