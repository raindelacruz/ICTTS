ALTER TABLE users
    ADD COLUMN service_category_id INT UNSIGNED NULL AFTER role,
    ADD INDEX users_service_category_idx (service_category_id);

ALTER TABLE users
    ADD CONSTRAINT users_service_category_fk FOREIGN KEY (service_category_id) REFERENCES service_categories(id);
