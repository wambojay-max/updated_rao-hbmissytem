USE ravine_academic;

CREATE TABLE IF NOT EXISTS academic_terms (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(30) NOT NULL,
    starts_on DATE NOT NULL,
    ends_on DATE NOT NULL,
    is_current TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id), UNIQUE KEY uq_term_name (name)
) ENGINE=InnoDB;

INSERT IGNORE INTO academic_terms (name, starts_on, ends_on, is_current)
VALUES ('2026/2027', '2026-09-01', '2027-08-31', 1);

ALTER TABLE student_results
    ADD COLUMN reviewed_by INT UNSIGNED NULL,
    ADD COLUMN reviewed_at DATETIME NULL;
