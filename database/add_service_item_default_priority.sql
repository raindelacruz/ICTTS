USE ictts;

ALTER TABLE service_items
    ADD COLUMN default_priority ENUM('Low','Medium','High','Critical') NOT NULL DEFAULT 'Medium' AFTER name;

UPDATE service_items
SET default_priority = CASE
    WHEN name IN ('Connectivity Problem', 'E-IFOMIS', 'Cash Monitoring', 'HURIS', 'Payroll') THEN 'High'
    WHEN name IN ('Certifications') THEN 'Low'
    ELSE 'Medium'
END;
