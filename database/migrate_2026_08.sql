USE rao_hbmis;

ALTER TABLE users MODIFY role ENUM('admin', 'warden', 'staff', 'student', 'lecturer') NOT NULL DEFAULT 'staff';

CREATE TABLE IF NOT EXISTS courses (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(20) NOT NULL,
    title VARCHAR(150) NOT NULL,
    credits TINYINT UNSIGNED NOT NULL DEFAULT 3,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id), UNIQUE KEY uq_course_code (code)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS course_registrations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    term VARCHAR(20) NOT NULL,
    status ENUM('Submitted', 'Approved', 'Rejected') NOT NULL DEFAULT 'Submitted',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), UNIQUE KEY uq_student_course_term (student_id, course_id, term),
    CONSTRAINT fk_registration_student FOREIGN KEY (student_id) REFERENCES students (id) ON DELETE CASCADE,
    CONSTRAINT fk_registration_course FOREIGN KEY (course_id) REFERENCES courses (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS student_results (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    grade VARCHAR(5) NOT NULL,
    status ENUM('Draft', 'Published') NOT NULL DEFAULT 'Draft',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), KEY idx_results_student (student_id),
    CONSTRAINT fk_result_student FOREIGN KEY (student_id) REFERENCES students (id) ON DELETE CASCADE,
    CONSTRAINT fk_result_course FOREIGN KEY (course_id) REFERENCES courses (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

ALTER TABLE users
    MODIFY role ENUM('admin', 'warden', 'staff', 'student', 'lecturer') NOT NULL DEFAULT 'staff';

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NULL,
    action VARCHAR(80) NOT NULL,
    entity VARCHAR(80) NOT NULL,
    entity_id INT UNSIGNED NULL,
    details JSON NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), KEY idx_audit_user (user_id), KEY idx_audit_created (created_at),
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS booking_notifications (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    booking_id INT UNSIGNED NOT NULL,
    channel ENUM('email', 'sms') NOT NULL,
    recipient VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('queued', 'sent', 'failed') NOT NULL DEFAULT 'queued',
    sent_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), KEY idx_notifications_booking (booking_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ai_allocation_suggestions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id INT UNSIGNED NOT NULL,
    room_id INT UNSIGNED NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
    suggested_by INT UNSIGNED NULL,
    reviewed_by INT UNSIGNED NULL,
    reviewed_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), KEY idx_ai_status (status),
    CONSTRAINT fk_ai_student FOREIGN KEY (student_id) REFERENCES students (id) ON DELETE RESTRICT,
    CONSTRAINT fk_ai_room FOREIGN KEY (room_id) REFERENCES rooms (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE USER IF NOT EXISTS 'rao_app'@'localhost' IDENTIFIED BY 'rao_hbmis_change_this_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON rao_hbmis.* TO 'rao_app'@'localhost';
FLUSH PRIVILEGES;
