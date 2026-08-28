# RAO Hostel Booking and Management Information System

## Final Project Report

## 1. Project Overview

RAO HBMIS is a standalone web-based hostel booking and management information system developed with PHP, MySQL/MariaDB, HTML, CSS, and JavaScript. The system helps hostel administrators and staff manage student records, rooms, bookings, room allocations, payments, users, and reports from one application. It is separate from the Ravine Academic System, although both systems use the same user credentials.

The system also includes OpenAI-powered tools to support hostel operations without automatically changing official records.

## 2. Project Objectives

The main objectives are to:

- Centralize hostel management records.
- Register and manage students.
- Manage hostel rooms and room availability.
- Record and track student bookings.
- Allocate rooms to students using hostel rules.
- Record student payments and payment status.
- Produce management summaries and reports.
- Control access according to user roles.
- Provide AI-assisted hostel questions and room suggestions.

## 3. Implemented Features

### Authentication and Authorization

- User login and logout.
- Password verification using PHP password hashing.
- Session-based authentication.
- Role-based access for administrators, wardens, and staff.
- Administrator-only user management.
- Separate lecturer and student roles are available for the academic system; RAO HBMIS remains focused on hostel operations.
- Failed-login lockout and password reset workflows.
- CSRF protection on state-changing forms.

### Student Management

- Add, view, edit, and delete students.
- Store student ID, name, gender, phone, email, course, and year of study.
- Prevent duplicate student IDs and email addresses.

### Room Management

- Add, view, edit, and delete rooms.
- Store room number, room type, capacity, floor, gender, and status.
- Support Available, Occupied, and Maintenance statuses.
- Prevent duplicate room numbers.

### Booking Management

- Create, view, edit, and delete bookings.
- Link each booking to a student and room.
- Support Pending, Confirmed, and Cancelled statuses.
- Validate room availability and student-room gender compatibility.

### Allocation Management

- Allocate rooms to students with confirmed bookings.
- Track allocation, check-in, and check-out dates.
- Support Active, Completed, and Cancelled statuses.
- Check room capacity and gender compatibility.
- Update room status when an active allocation is created or completed.

### Payment Management

- Record, view, edit, and delete payments.
- Support Cash, M-Pesa, Bank, and Card methods.
- Track Pending, Completed, Failed, and Cancelled statuses.
- Prevent duplicate payment reference numbers.

### Reports and Dashboard

- Show student, room, booking, allocation, and payment totals.
- Show available and occupied rooms.
- Show completed payment totals.
- Provide report pages for major management areas.
- Support CSV spreadsheet downloads.
- Record important administrative actions in audit logs.
- Queue booking notifications for email or SMS delivery.

### Artificial Intelligence Features

- **AI Hostel Assistant:** answers staff questions using current summary data from students, rooms, bookings, allocations, and payments.
- **AI Room Recommendations:** suggests suitable rooms for confirmed bookings that have no active allocation.
- AI recommendations are display-only and do not create or modify bookings or allocations.
- API requests are handled server-side so the OpenAI key is not exposed in browser JavaScript.

## 4. System Architecture

The application uses a simple PHP server-rendered architecture:

- `auth/` contains login, logout, and role checks.
- `config/` contains database and OpenAI configuration.
- `admin/` contains management and reporting pages.
- `api/` contains authenticated AI endpoints.
- `database/` contains the database schema.
- `assets/css/` contains shared styling.
- `dashboard.php` provides the authenticated hostel system dashboard.
- `student/` contains the hostel student's accommodation view.

The application uses PDO prepared statements for database operations and JSON responses for the AI endpoints.

## 5. Database Design

The database is named `rao_hbmis` and is created by `database/schema.sql`.

Main tables:

- `users` - system users and roles.
- `students` - student profiles.
- `rooms` - room details and availability.
- `bookings` - student room booking requests.
- `allocations` - confirmed room assignments.
- `payments` - student payment records.
- `audit_logs` - important administrative actions.
- `booking_notifications` - queued booking notifications.
- `ai_allocation_suggestions` - AI suggestions awaiting human review.

Foreign keys connect bookings, allocations, and payments to students and rooms. Restricting deletion protects records that are already referenced by other transactions.

## 6. Security Measures

- Passwords are stored using `password_hash()`.
- Passwords are checked with `password_verify()`.
- Protected pages require an authenticated session.
- Role checks limit access to sensitive operations.
- Database input uses prepared statements.
- Displayed database values use HTML escaping.
- AI endpoints validate request methods and input length.
- OpenAI API keys are read from the server environment rather than browser code.
- Room recommendations are not applied automatically.
- CSRF tokens protect state-changing requests.
- HTTPS is configured for local XAMPP access and session cookies are Secure, HttpOnly, and SameSite protected.
- Account lockout limits repeated failed login attempts.

## 7. Installation and Configuration

1. Install XAMPP.
2. Copy the project to `C:\xampp\htdocs\RAO_HBMIS`.
3. Start Apache and MySQL in XAMPP.
4. Open phpMyAdmin at `http://localhost/phpmyadmin`.
5. Import `database/schema.sql`.
6. Confirm the database name is `rao_hbmis`.
7. Open `https://localhost/RAO_HBMIS/auth/login.php`.

Initial administrator account:

- Email: `admin@rao-hbmis.local`
- Password: `Admin@123`

The password should be changed after the first login.

To enable AI features, configure the server environment:

```powershell
setx OPENAI_API_KEY "your-openai-api-key"
setx OPENAI_MODEL "gpt-4o-mini"
```

Restart the XAMPP Control Panel and Apache after configuring the environment. PHP cURL must also be enabled.

## 8. Testing and Validation

The following validation was completed:

- PHP syntax checks passed for the OpenAI configuration, AI endpoints, and dashboard.
- Editor error checks found no errors in the changed PHP files.
- Database schema was reviewed against the SQL queries and form fields used by the application.
- The GitHub repository was updated successfully on the `main` branch.

Manual testing should include:

- Login with valid and invalid credentials.
- Access control for each role.
- Creating and editing students, rooms, bookings, allocations, and payments.
- Room capacity and gender validation.
- Database import through phpMyAdmin.
- AI assistant and recommendation behavior with a valid API key.
- Behavior when MySQL or OpenAI is unavailable.

## 9. Limitations

- The system currently depends on a local XAMPP installation.
- OpenAI features require internet access and a valid API key.
- AI recommendations require staff review before any allocation is created.
- The interface is functional but can be expanded with richer validation and a more advanced design system.
- The local HTTPS certificate is self-signed and must be replaced with a trusted certificate for public deployment.
- Email and SMS notifications require a configured delivery provider and queue worker.
- PDF report generation requires a PDF library such as Dompdf; CSV spreadsheet export is available.

## 10. Future Enhancements

Recommended future improvements include:

- Connect the notification queue to production email and SMS providers.
- Add a background worker for reliable notification delivery and retries.
- Add PDF report generation and broader table filtering across all management pages.
- Expand automated integration and authorization tests.
- Replace the local self-signed certificate with a trusted production certificate.

## 11. Conclusion

RAO HBMIS provides a centralized solution for core hostel administration activities. It improves record organization, supports consistent room allocation rules, tracks payments, and gives authorized users a dashboard view of hostel operations. Its separate hostel login works with the same credentials as the Ravine Academic System, while keeping accommodation data and workflows independent. The OpenAI features extend the system with practical assistance while keeping final management decisions under staff control.
