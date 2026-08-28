CREATE DATABASE IF NOT EXISTS ravine_academic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ravine_academic;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'lecturer', 'admin') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), UNIQUE KEY uq_academic_user_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS students (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id VARCHAR(30) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    course VARCHAR(100) NOT NULL,
    year_of_study TINYINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), UNIQUE KEY uq_academic_student_id (student_id), UNIQUE KEY uq_academic_student_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS courses (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(20) NOT NULL,
    title VARCHAR(150) NOT NULL,
    credits TINYINT UNSIGNED NOT NULL DEFAULT 3,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id), UNIQUE KEY uq_academic_course_code (code)
) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS academic_terms (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(30) NOT NULL,
        starts_on DATE NOT NULL,
        ends_on DATE NOT NULL,
        is_current TINYINT(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (id), UNIQUE KEY uq_term_name (name)
    ) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS course_registrations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    term VARCHAR(20) NOT NULL,
    status ENUM('Submitted', 'Approved', 'Rejected') NOT NULL DEFAULT 'Submitted',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), UNIQUE KEY uq_registration (student_id, course_id, term)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS student_results (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    grade VARCHAR(5) NOT NULL,
    status ENUM('Draft', 'Published') NOT NULL DEFAULT 'Draft',
    reviewed_by INT UNSIGNED NULL,
    reviewed_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB;
