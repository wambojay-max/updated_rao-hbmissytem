CREATE DATABASE IF NOT EXISTS rao_hbmis
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE rao_hbmis;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS ai_allocation_suggestions;
DROP TABLE IF EXISTS booking_notifications;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS allocations;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'warden', 'staff', 'student', 'lecturer') NOT NULL DEFAULT 'staff',
    failed_login_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    locked_until DATETIME NULL,
    reset_token_hash CHAR(64) NULL,
    reset_expires_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NULL,
    action VARCHAR(80) NOT NULL,
    entity VARCHAR(80) NOT NULL,
    entity_id INT UNSIGNED NULL,
    details JSON NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_audit_user (user_id),
    KEY idx_audit_created (created_at),
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE booking_notifications (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    booking_id INT UNSIGNED NOT NULL,
    channel ENUM('email', 'sms') NOT NULL,
    recipient VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('queued', 'sent', 'failed') NOT NULL DEFAULT 'queued',
    sent_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_notifications_booking (booking_id)
) ENGINE=InnoDB;

CREATE TABLE students (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id VARCHAR(30) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    gender ENUM('Male', 'Female') NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    course VARCHAR(100) NOT NULL,
    year_of_study TINYINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_students_student_id (student_id),
    UNIQUE KEY uq_students_email (email)
) ENGINE=InnoDB;

CREATE TABLE courses (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(20) NOT NULL,
    title VARCHAR(150) NOT NULL,
    credits TINYINT UNSIGNED NOT NULL DEFAULT 3,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id), UNIQUE KEY uq_course_code (code)
) ENGINE=InnoDB;

CREATE TABLE course_registrations (
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

CREATE TABLE student_results (
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

CREATE TABLE ai_allocation_suggestions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id INT UNSIGNED NOT NULL,
    room_id INT UNSIGNED NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
    suggested_by INT UNSIGNED NULL,
    reviewed_by INT UNSIGNED NULL,
    reviewed_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ai_status (status),
    CONSTRAINT fk_ai_student FOREIGN KEY (student_id) REFERENCES students (id) ON DELETE RESTRICT,
    CONSTRAINT fk_ai_room FOREIGN KEY (room_id) REFERENCES rooms (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE rooms (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    room_number VARCHAR(20) NOT NULL,
    room_type ENUM('Single', 'Double', 'Triple', 'Shared') NOT NULL,
    capacity TINYINT UNSIGNED NOT NULL,
    floor SMALLINT UNSIGNED NOT NULL,
    gender ENUM('Male', 'Female') NOT NULL,
    status ENUM('Available', 'Occupied', 'Maintenance') NOT NULL DEFAULT 'Available',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_rooms_room_number (room_number)
) ENGINE=InnoDB;

CREATE TABLE bookings (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id INT UNSIGNED NOT NULL,
    room_id INT UNSIGNED NOT NULL,
    booking_date DATE NOT NULL,
    check_in_date DATE NOT NULL,
    status ENUM('Pending', 'Confirmed', 'Cancelled') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_bookings_student (student_id),
    KEY idx_bookings_room (room_id),
    KEY idx_bookings_status (status),
    CONSTRAINT fk_bookings_student FOREIGN KEY (student_id) REFERENCES students (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_bookings_room FOREIGN KEY (room_id) REFERENCES rooms (id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE allocations (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id INT UNSIGNED NOT NULL,
    room_id INT UNSIGNED NOT NULL,
    allocation_date DATE NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    status ENUM('Active', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_allocations_student (student_id),
    KEY idx_allocations_room_status (room_id, status),
    CONSTRAINT fk_allocations_student FOREIGN KEY (student_id) REFERENCES students (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_allocations_room FOREIGN KEY (room_id) REFERENCES rooms (id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE payments (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id INT UNSIGNED NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method ENUM('Cash', 'M-Pesa', 'Bank', 'Card') NOT NULL,
    reference_number VARCHAR(50) NOT NULL,
    status ENUM('Pending', 'Completed', 'Failed', 'Cancelled') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_payments_reference (reference_number),
    KEY idx_payments_student (student_id),
    KEY idx_payments_status (status),
    CONSTRAINT fk_payments_student FOREIGN KEY (student_id) REFERENCES students (id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO users (full_name, email, password, role)
VALUES (
    'System Administrator',
    'admin@rao-hbmis.local',
    '$2y$10$Q0Q8u7CkUacDXkw45V9cjO3AGkNfyohMhkMCYI0r.q.OOmTjDUQh2',
    'admin'
);
