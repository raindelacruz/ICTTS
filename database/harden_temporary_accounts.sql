UPDATE users
SET status = 'inactive'
WHERE id_number IN ('TECH-001', 'HEAD-001', 'CHIEF-001', 'ADMIN-001')
  AND name LIKE 'Temporary%';
