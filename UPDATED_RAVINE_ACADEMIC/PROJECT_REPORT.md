# Ravine Academic System

## Final Project Report

## 1. Project Overview

Ravine Academic System is a standalone web application for academic student services. It provides students with course registration and published examination results, and gives lecturers an authorized workspace for entering results and maintaining student academic details.

The application is intentionally separate from RAO HBMIS, the hostel booking system. Students may use the same email address and password in both systems, but each system has its own URL, session, pages, and responsibilities.

## 2. Project Objectives

The objectives are to:

- Provide a dedicated academic student portal.
- Allow students to register courses for an academic term.
- Allow students to view published examination results.
- Allow lecturers to enter and publish student results.
- Allow authorized lecturers to edit student academic details.
- Keep academic and hostel workflows independent.
- Reuse the shared user credentials without sharing application sessions.

## 3. Implemented Features

### Separate Authentication

- Dedicated academic login page.
- Separate `ravine_academic_session` session cookie.
- Shared authentication records from the `rao_hbmis.users` table.
- Student accounts are routed to the academic student dashboard.
- Lecturer and administrator accounts are routed to the lecturer workspace.
- Link to the separate RAO Hostel System login.

### Student Academic Portal

- Student dashboard with academic service links.
- Course registration for the current academic term.
- Duplicate-safe course registration using a student, course, and term constraint.
- Published results view showing course code, title, academic year, score, and grade.
- Link to hostel accommodation that opens the separate hostel login.

### Lecturer Workspace

- Lecturer-only results entry page.
- Student and course selection from database records.
- Score validation from 0 to 100.
- Grade validation.
- Academic-year capture.
- Immediate publication of entered results.
- Results are submitted as drafts and require administrator approval before publication.
- Current academic term is stored and managed through the `academic_terms` table.
- Link to edit student details through the authorized hostel administration student record page where permitted.

### Security

- Passwords are verified with PHP `password_verify()`.
- State-changing academic forms use CSRF tokens.
- Database operations use PDO prepared statements.
- Output is HTML escaped.
- Student pages require the student role.
- Lecturer pages require the lecturer or administrator role.
- Academic and hostel sessions use different cookie names.
- Both systems are served through HTTPS in the local XAMPP deployment.

## 4. System Architecture

The system uses a small PHP server-rendered architecture:

- `auth/` contains academic login and logout pages.
- `config/` contains the database connection and academic session security helpers.
- `student/` contains student dashboard, course registration, and results pages.
- `lecturer/` contains lecturer result-entry pages.
- `assets/` contains academic-system styling.

The application connects to the shared `rao_hbmis` database for users, students, courses, registrations, and results. It does not include hostel booking pages or hostel administration navigation.

## 5. Database Design

Academic tables are stored in the shared `rao_hbmis` database:

- `users` - shared login credentials and account roles.
- `students` - student identity and academic profile details.
- `courses` - course code, title, credits, and active status.
- `course_registrations` - courses submitted by students for a term.
- `student_results` - scores, grades, academic years, and publication status.
- `academic_terms` - active academic terms and their start/end dates.

The `course_registrations` table prevents duplicate registration of the same course by the same student in the same term.

## 6. User Workflows

### Student

1. Open `https://localhost/RAVINE_ACADEMIC/auth/login.php`.
2. Sign in using the shared account credentials.
3. Open **Register courses** to submit courses.
4. Open **View results** to see published results.
5. Open **Hostel accommodation** to move to the separate RAO Hostel login.

### Lecturer

1. Open the academic login page.
2. Sign in with a lecturer account.
3. Select a student and course.
4. Enter the academic year, score, and grade.
5. Publish the result.
6. Update student details through the authorized student-management page when needed.

## 7. Installation and Configuration

1. Install XAMPP.
2. Place the application at `C:\xampp\htdocs\RAVINE_ACADEMIC`.
3. Start Apache and MySQL.
4. Ensure the shared `rao_hbmis` database exists.
5. Apply the academic tables from `RAO_HBMIS/database/migrate_2026_08.sql`.
6. Configure `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` in the server environment.
7. Open `https://localhost/RAVINE_ACADEMIC/auth/login.php`.

The academic system uses the same database account configuration as the hostel system. Production deployments should use the dedicated least-privilege database user rather than the local root account.

## 8. Testing and Validation

Validation completed:

- PHP syntax checks passed for academic login, student, and lecturer pages.
- Academic and hostel login endpoints returned HTTP 200 over HTTPS.
- HTTP requests redirect to HTTPS.
- The academic session cookie is separate from the hostel session cookie.
- The academic database migration completed successfully.
- CSRF tokens are present on academic POST forms.

Manual testing should include:

- Valid and invalid student login.
- Valid and invalid lecturer login.
- Course registration with valid and invalid courses.
- Duplicate course registration.
- Results visibility before and after publication.
- Score and grade validation.
- Unauthorized access to lecturer pages.
- Navigation from the academic system to the separate hostel login.

## 9. Limitations

- The application currently shares the hostel database rather than using a separate database server.
- Course and semester administration screens are still limited, although the active-term database model is now in place.
- Course administration pages for creating and editing courses are not yet included.
- Email notifications are not required for the academic workflow.
- The local HTTPS certificate is self-signed and should be replaced for public deployment.

## 10. Future Enhancements

- Add registrar approval for published results beyond the current administrator review page.
- Add lecturer course assignments so lecturers see only their assigned courses.
- Add academic transcript downloads in PDF format.
- Add semester and academic-year management.
- Add student profile editing with approval rules.
- Add automated integration tests for registration and lecturer authorization.
- Introduce a dedicated academic database while synchronizing only approved identity records.

## 11. Conclusion

Ravine Academic System provides a focused academic portal for students and lecturers. It keeps course registration, results, and academic data separate from hostel accommodation while allowing students to use the same credentials in both applications. A clear hostel-system link preserves access to accommodation services without combining the two systems into one interface.
