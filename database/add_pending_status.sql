USE ictts;

ALTER TABLE tickets
    MODIFY status ENUM('Submitted','Assigned','In Progress','Pending','Completed','Confirmed Completed','Cancelled') NOT NULL DEFAULT 'Submitted';
