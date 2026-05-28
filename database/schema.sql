CREATE DATABASE IF NOT EXISTS ictts CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ictts;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS requester_confirmation_tokens;
DROP TABLE IF EXISTS email_logs;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS ticket_endorsements;
DROP TABLE IF EXISTS ticket_reopen_logs;
DROP TABLE IF EXISTS ticket_escalations;
DROP TABLE IF EXISTS ticket_feedback;
DROP TABLE IF EXISTS ticket_attachments;
DROP TABLE IF EXISTS ticket_assignees;
DROP TABLE IF EXISTS ticket_status_logs;
DROP TABLE IF EXISTS ticket_assignments;
DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS offices;
DROP TABLE IF EXISTS regions;
DROP TABLE IF EXISTS service_items;
DROP TABLE IF EXISTS service_categories;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS settings;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_number VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(160) NOT NULL,
    position VARCHAR(160) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('technical','unit_head','division_chief','admin') NOT NULL DEFAULT 'technical',
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE service_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL UNIQUE,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE service_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_category_id INT UNSIGNED NOT NULL,
    name VARCHAR(160) NOT NULL,
    default_priority ENUM('Low','Medium','High','Critical') NOT NULL DEFAULT 'Medium',
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY service_item_unique (service_category_id, name),
    CONSTRAINT service_items_category_fk FOREIGN KEY (service_category_id) REFERENCES service_categories(id)
) ENGINE=InnoDB;

CREATE TABLE regions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(160) NOT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE offices (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    region_id INT UNSIGNED NOT NULL,
    name VARCHAR(190) NOT NULL,
    office_type ENUM('Regional Office','Branch Office','Central Office','District Office','Other') NOT NULL DEFAULT 'Branch Office',
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY office_unique (region_id, name),
    CONSTRAINT offices_region_fk FOREIGN KEY (region_id) REFERENCES regions(id)
) ENGINE=InnoDB;

CREATE TABLE tickets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_no VARCHAR(40) NOT NULL UNIQUE,
    requested_at DATETIME NOT NULL,
    requester_name VARCHAR(160) NOT NULL,
    requester_position VARCHAR(160) NULL,
    requester_email VARCHAR(190) NOT NULL,
    requester_contact VARCHAR(50) NOT NULL,
    region_id INT UNSIGNED NOT NULL,
    office_id INT UNSIGNED NOT NULL,
    requested_for DATETIME NOT NULL,
    service_category_id INT UNSIGNED NOT NULL,
    service_item_id INT UNSIGNED NOT NULL,
    responsible_group VARCHAR(160) NULL,
    description TEXT NOT NULL,
    priority ENUM('Low','Medium','High','Critical') NOT NULL DEFAULT 'Medium',
    status ENUM('Submitted','Assigned','In Progress','Pending','Completed','Confirmed Completed','Returned for Further Action','Cancelled') NOT NULL DEFAULT 'Submitted',
    assigned_to INT UNSIGNED NULL,
    assigned_by INT UNSIGNED NULL,
    assigned_at DATETIME NULL,
    response_due_at DATETIME NULL,
    resolution_due_at DATETIME NULL,
    first_responded_at DATETIME NULL,
    sla_status ENUM('Within SLA','Response Overdue','Resolution Overdue','Met','Breached') NOT NULL DEFAULT 'Within SLA',
    sla_breached_at DATETIME NULL,
    completed_by_tech_at DATETIME NULL,
    requester_confirmed_at DATETIME NULL,
    closed_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX tickets_status_idx (status),
    INDEX tickets_priority_idx (priority),
    INDEX tickets_sla_status_idx (sla_status),
    INDEX tickets_resolution_due_idx (resolution_due_at),
    INDEX tickets_requested_at_idx (requested_at),
    INDEX tickets_assigned_to_idx (assigned_to),
    CONSTRAINT tickets_region_fk FOREIGN KEY (region_id) REFERENCES regions(id),
    CONSTRAINT tickets_office_fk FOREIGN KEY (office_id) REFERENCES offices(id),
    CONSTRAINT tickets_category_fk FOREIGN KEY (service_category_id) REFERENCES service_categories(id),
    CONSTRAINT tickets_item_fk FOREIGN KEY (service_item_id) REFERENCES service_items(id),
    CONSTRAINT tickets_assigned_to_fk FOREIGN KEY (assigned_to) REFERENCES users(id),
    CONSTRAINT tickets_assigned_by_fk FOREIGN KEY (assigned_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE ticket_assignments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NOT NULL,
    previous_assignee INT UNSIGNED NULL,
    assigned_to INT UNSIGNED NOT NULL,
    assigned_by INT UNSIGNED NOT NULL,
    assignment_role ENUM('primary','supporting') NOT NULL DEFAULT 'primary',
    action ENUM('assign','reassign','add_support','remove_support') NOT NULL DEFAULT 'assign',
    assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    notes VARCHAR(500) NULL,
    reason VARCHAR(700) NULL,
    CONSTRAINT ticket_assignments_ticket_fk FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    CONSTRAINT ticket_assignments_previous_fk FOREIGN KEY (previous_assignee) REFERENCES users(id),
    CONSTRAINT ticket_assignments_to_fk FOREIGN KEY (assigned_to) REFERENCES users(id),
    CONSTRAINT ticket_assignments_by_fk FOREIGN KEY (assigned_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE ticket_assignees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    assignment_role ENUM('primary','supporting') NOT NULL DEFAULT 'supporting',
    assigned_by INT UNSIGNED NOT NULL,
    assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    removed_at DATETIME NULL,
    notes VARCHAR(500) NULL,
    INDEX ticket_assignees_ticket_idx (ticket_id, removed_at),
    INDEX ticket_assignees_user_idx (user_id, removed_at),
    CONSTRAINT ticket_assignees_ticket_fk FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    CONSTRAINT ticket_assignees_user_fk FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT ticket_assignees_by_fk FOREIGN KEY (assigned_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE ticket_status_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NOT NULL,
    old_status VARCHAR(80) NULL,
    new_status VARCHAR(80) NOT NULL,
    changed_by INT UNSIGNED NULL,
    changed_by_name VARCHAR(160) NULL,
    remarks VARCHAR(700) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT ticket_status_logs_ticket_fk FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    CONSTRAINT ticket_status_logs_user_fk FOREIGN KEY (changed_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    actor_name VARCHAR(160) NULL,
    action VARCHAR(120) NOT NULL,
    entity_type VARCHAR(80) NULL,
    entity_id VARCHAR(80) NULL,
    details TEXT NULL,
    ip_address VARCHAR(60) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX activity_action_idx (action),
    INDEX activity_created_idx (created_at),
    CONSTRAINT activity_logs_user_fk FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(160) NOT NULL,
    message VARCHAR(500) NOT NULL,
    link VARCHAR(255) NULL,
    read_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX notifications_user_read_idx (user_id, read_at),
    INDEX notifications_created_idx (created_at),
    CONSTRAINT notifications_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ticket_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NOT NULL,
    uploaded_by INT UNSIGNED NULL,
    uploaded_by_name VARCHAR(160) NULL,
    source ENUM('requester','technical','manager','admin') NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    mime_type VARCHAR(120) NOT NULL,
    file_size BIGINT UNSIGNED NOT NULL,
    remarks VARCHAR(500) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX ticket_attachments_ticket_idx (ticket_id),
    CONSTRAINT ticket_attachments_ticket_fk FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    CONSTRAINT ticket_attachments_user_fk FOREIGN KEY (uploaded_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE ticket_feedback (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NULL,
    resolved_yes_no ENUM('yes','no') NOT NULL,
    feedback_comments TEXT NULL,
    submitted_by_name VARCHAR(160) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX ticket_feedback_ticket_idx (ticket_id),
    CONSTRAINT ticket_feedback_ticket_fk FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ticket_escalations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NOT NULL,
    escalation_type ENUM('response_overdue','resolution_overdue','manual') NOT NULL,
    escalated_to_role VARCHAR(80) NOT NULL,
    escalated_to_user INT UNSIGNED NULL,
    escalated_by INT UNSIGNED NULL,
    reason VARCHAR(700) NOT NULL,
    notice_key VARCHAR(190) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY ticket_escalations_notice_unique (ticket_id, notice_key),
    INDEX ticket_escalations_ticket_idx (ticket_id),
    CONSTRAINT ticket_escalations_ticket_fk FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    CONSTRAINT ticket_escalations_to_user_fk FOREIGN KEY (escalated_to_user) REFERENCES users(id),
    CONSTRAINT ticket_escalations_by_fk FOREIGN KEY (escalated_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE ticket_reopen_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NOT NULL,
    old_status VARCHAR(80) NOT NULL,
    new_status VARCHAR(80) NOT NULL,
    reopened_by INT UNSIGNED NULL,
    reopened_by_name VARCHAR(160) NULL,
    reason VARCHAR(700) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX ticket_reopen_logs_ticket_idx (ticket_id),
    CONSTRAINT ticket_reopen_logs_ticket_fk FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    CONSTRAINT ticket_reopen_logs_user_fk FOREIGN KEY (reopened_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE ticket_endorsements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NOT NULL,
    from_group VARCHAR(160) NULL,
    to_group VARCHAR(160) NOT NULL,
    old_service_category_id INT UNSIGNED NULL,
    new_service_category_id INT UNSIGNED NULL,
    old_service_item_id INT UNSIGNED NULL,
    new_service_item_id INT UNSIGNED NULL,
    endorsed_by INT UNSIGNED NOT NULL,
    reason VARCHAR(700) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX ticket_endorsements_ticket_idx (ticket_id),
    CONSTRAINT ticket_endorsements_ticket_fk FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    CONSTRAINT ticket_endorsements_old_category_fk FOREIGN KEY (old_service_category_id) REFERENCES service_categories(id),
    CONSTRAINT ticket_endorsements_new_category_fk FOREIGN KEY (new_service_category_id) REFERENCES service_categories(id),
    CONSTRAINT ticket_endorsements_old_item_fk FOREIGN KEY (old_service_item_id) REFERENCES service_items(id),
    CONSTRAINT ticket_endorsements_new_item_fk FOREIGN KEY (new_service_item_id) REFERENCES service_items(id),
    CONSTRAINT ticket_endorsements_by_fk FOREIGN KEY (endorsed_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE email_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NULL,
    recipient_email VARCHAR(190) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body MEDIUMTEXT NOT NULL,
    status ENUM('queued','sent','failed','logged') NOT NULL DEFAULT 'logged',
    error_message TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT email_logs_ticket_fk FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE requester_confirmation_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT confirmation_tokens_ticket_fk FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(120) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO users (id_number, name, position, email, password_hash, role, status) VALUES
('TECH-001', 'Temporary Technical User', 'ICT Support Staff', 'tech@nfa.gov.ph', '$2y$10$atcIkMqtOWtfbckbbDIJG.HQGLdvD0R.jhjDEIj1pNxN/CSl79oQG', 'technical', 'active'),
('HEAD-001', 'Temporary Unit Head', 'Unit Head', 'unithead@nfa.gov.ph', '$2y$10$atcIkMqtOWtfbckbbDIJG.HQGLdvD0R.jhjDEIj1pNxN/CSl79oQG', 'unit_head', 'active'),
('CHIEF-001', 'Temporary Division Chief', 'Division Chief', 'chief@nfa.gov.ph', '$2y$10$atcIkMqtOWtfbckbbDIJG.HQGLdvD0R.jhjDEIj1pNxN/CSl79oQG', 'division_chief', 'active'),
('ADMIN-001', 'Temporary Administrator', 'System Administrator', 'admin@nfa.gov.ph', '$2y$10$atcIkMqtOWtfbckbbDIJG.HQGLdvD0R.jhjDEIj1pNxN/CSl79oQG', 'admin', 'active');

INSERT INTO service_categories (name) VALUES
('Hardware and Network Infrastructure'),
('Systems and Application');

INSERT INTO service_items (service_category_id, name, default_priority) VALUES
(1, 'Certifications', 'Low'),
(1, 'ICT Resource User Access', 'Medium'),
(1, 'Technical Support', 'Medium'),
(1, 'Connectivity Problem', 'High'),
(1, 'Replacement of Parts/Equipment', 'Medium'),
(2, 'E-IFOMIS', 'High'),
(2, 'Cash Monitoring', 'High'),
(2, 'HURIS', 'High'),
(2, 'Payroll', 'High'),
(2, 'Website Posting', 'Medium'),
(2, 'GovMail Support', 'Medium'),
(2, 'Bid Posting', 'Medium');

INSERT INTO regions (code, name) VALUES
('ARMM', 'NFA ARMM Regional Office'),
('CARAGA', 'NFA CARAGA Regional Office'),
('CO', 'NFA Central Office'),
('NCR', 'NFA NCR Regional Office'),
('R1', 'NFA Region I Office'),
('R2', 'NFA Region II Office'),
('R3', 'NFA Region III Office'),
('R4', 'NFA Region IV Office'),
('R9', 'NFA Region IX Office'),
('R5', 'NFA Region V Office'),
('R6', 'NFA Region VI Office'),
('R7', 'NFA Region VII Office'),
('R8', 'NFA Region VIII Office'),
('R10', 'NFA Region X Office'),
('R11', 'NFA Region XI Office'),
('R12', 'NFA Region XII Office');

INSERT INTO offices (region_id, name, office_type) VALUES
(1, 'Maguindanao', 'Branch Office'),
(1, 'Basilan', 'Branch Office'),
(1, 'ARMM Regional Office', 'Regional Office'),
(1, 'Lanao del Sur', 'Branch Office'),
(2, 'Surigao del Sur', 'Branch Office'),
(2, 'Agusan del Sur', 'Branch Office'),
(2, 'NFA CARAGA Regional Office', 'Regional Office'),
(3, 'Administrative and General Services Department', 'Central Office'),
(3, 'Finance Department', 'Central Office'),
(3, 'Operations and Coordination Department', 'Central Office'),
(3, 'Legal Affairs Department', 'Central Office'),
(3, 'Corporate Planning and Management Services Deparment', 'Central Office'),
(3, 'Public Affairs Division', 'Central Office'),
(4, 'East District', 'District Office'),
(4, 'Central District', 'District Office'),
(4, 'NCR Regional Office', 'Regional Office'),
(5, 'Eastern Pangasinan', 'Branch Office'),
(5, 'Region I Office', 'Regional Office'),
(5, 'La Union', 'Branch Office'),
(5, 'Ilocos Norte', 'Branch Office'),
(6, 'Cagayan', 'Branch Office'),
(6, 'Nueva Vizcaya', 'Branch Office'),
(6, 'Region II Office', 'Regional Office'),
(6, 'Isabela', 'Branch Office'),
(7, 'Region III Office', 'Regional Office'),
(7, 'Bulacan', 'Branch Office'),
(7, 'Tarlac', 'Branch Office'),
(7, 'Pampanga', 'Branch Office'),
(7, 'Nueva Ecija', 'Branch Office'),
(8, 'Occidental Mindoro', 'Branch Office'),
(8, 'Palawan', 'Branch Office'),
(8, 'Oriental Mindoro', 'Branch Office'),
(8, 'Region IV Office', 'Regional Office'),
(8, 'Batangas', 'Branch Office'),
(8, 'Quezon', 'Branch Office'),
(8, 'Laguna', 'Branch Office'),
(9, 'Zamboanga Del Sur', 'Branch Office'),
(9, 'Region IX Office', 'Regional Office'),
(9, 'Zamboanga', 'Branch Office'),
(10, 'Region V Office', 'Regional Office'),
(10, 'Camarines Sur', 'Branch Office'),
(10, 'Sorsogon', 'Branch Office'),
(10, 'Albay', 'Branch Office'),
(11, 'Region VI Office', 'Regional Office'),
(11, 'Iloilo', 'Branch Office'),
(11, 'Capiz', 'Branch Office'),
(11, 'Negros Occidental', 'Branch Office'),
(12, 'Region VII Office', 'Regional Office'),
(12, 'Cebu', 'Branch Office'),
(12, 'Negros Oriental', 'Branch Office'),
(12, 'Bohol', 'Branch Office'),
(13, 'Leyte', 'Branch Office'),
(13, 'Region VIII Office', 'Regional Office'),
(13, 'Northern Samar', 'Branch Office'),
(14, 'Misamis Oriental', 'Branch Office'),
(14, 'Bukidnon', 'Branch Office'),
(14, 'Region X Office', 'Regional Office'),
(14, 'Lanao del Norte', 'Branch Office'),
(15, 'Davao Oriental', 'Branch Office'),
(15, 'Davao del Sur', 'Branch Office'),
(15, 'Region XI Office', 'Regional Office'),
(15, 'Davao del Norte', 'Branch Office'),
(16, 'Sultan Kudarat', 'Branch Office'),
(16, 'North Cotabato', 'Branch Office'),
(16, 'Region XII Office', 'Regional Office'),
(16, 'South Cotabato', 'Branch Office');

INSERT INTO settings (setting_key, setting_value) VALUES
('ict_notification_email', 'ict@nfa.gov.ph'),
('system_public_url', 'https://ebps.nfa.gov.ph/ICTTS/public');
