USE ictts;

ALTER TABLE tickets
    ADD COLUMN priority ENUM('Low','Medium','High','Critical') NOT NULL DEFAULT 'Medium' AFTER description,
    ADD COLUMN response_due_at DATETIME NULL AFTER assigned_at,
    ADD COLUMN resolution_due_at DATETIME NULL AFTER response_due_at,
    ADD COLUMN first_responded_at DATETIME NULL AFTER resolution_due_at,
    ADD COLUMN sla_status ENUM('Within SLA','Response Overdue','Resolution Overdue','Met','Breached') NOT NULL DEFAULT 'Within SLA' AFTER first_responded_at,
    ADD COLUMN sla_breached_at DATETIME NULL AFTER sla_status,
    ADD COLUMN responsible_group VARCHAR(160) NULL AFTER service_item_id,
    MODIFY status ENUM('Submitted','Assigned','In Progress','Pending','Completed','Confirmed Completed','Returned for Further Action','Cancelled') NOT NULL DEFAULT 'Submitted',
    ADD INDEX tickets_priority_idx (priority),
    ADD INDEX tickets_sla_status_idx (sla_status),
    ADD INDEX tickets_resolution_due_idx (resolution_due_at);

CREATE TABLE IF NOT EXISTS ticket_assignees (
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

ALTER TABLE ticket_assignments
    ADD COLUMN previous_assignee INT UNSIGNED NULL AFTER ticket_id,
    ADD COLUMN assignment_role ENUM('primary','supporting') NOT NULL DEFAULT 'primary' AFTER assigned_by,
    ADD COLUMN action ENUM('assign','reassign','add_support','remove_support') NOT NULL DEFAULT 'assign' AFTER assignment_role,
    ADD COLUMN reason VARCHAR(700) NULL AFTER notes,
    ADD CONSTRAINT ticket_assignments_previous_fk FOREIGN KEY (previous_assignee) REFERENCES users(id);

CREATE TABLE IF NOT EXISTS ticket_attachments (
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

CREATE TABLE IF NOT EXISTS ticket_feedback (
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

CREATE TABLE IF NOT EXISTS ticket_escalations (
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

CREATE TABLE IF NOT EXISTS ticket_reopen_logs (
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

CREATE TABLE IF NOT EXISTS ticket_endorsements (
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

UPDATE tickets t
JOIN service_categories sc ON sc.id = t.service_category_id
SET t.responsible_group = sc.name
WHERE t.responsible_group IS NULL;

INSERT INTO ticket_assignees (ticket_id, user_id, assignment_role, assigned_by, assigned_at, notes)
SELECT id, assigned_to, 'primary', COALESCE(assigned_by, assigned_to), COALESCE(assigned_at, NOW()), 'Migrated from tickets.assigned_to'
FROM tickets
WHERE assigned_to IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM ticket_assignees ta
      WHERE ta.ticket_id = tickets.id
        AND ta.user_id = tickets.assigned_to
        AND ta.removed_at IS NULL
  );
