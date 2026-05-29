USE ictts;

UPDATE tickets SET status = 'Completed' WHERE status = 'Completed by Technical Personnel';
UPDATE ticket_status_logs SET new_status = 'Completed' WHERE new_status = 'Completed by Technical Personnel';
UPDATE ticket_status_logs SET old_status = 'Completed' WHERE old_status = 'Completed by Technical Personnel';
UPDATE tickets SET status = 'Confirmed Completed' WHERE status = 'Closed';
UPDATE ticket_status_logs SET new_status = 'Confirmed Completed' WHERE new_status = 'Closed';
UPDATE ticket_status_logs SET old_status = 'Confirmed Completed' WHERE old_status = 'Closed';

ALTER TABLE tickets
    MODIFY status ENUM('Submitted','Assigned','In Progress','Pending','Completed','Confirmed Completed','Cancelled') NOT NULL DEFAULT 'Submitted';
