# WorkPulse — Employee Daily Work Update & Performance Evaluation System

A PHP + MySQL assignment project built with native PHP, PDO, HTML, CSS, and JavaScript. It does not require Composer, Laravel, npm, or a build step.

## Features implemented

### Core requirements
- Employee task allocation by Admin/Manager.
- Task tracking with To-do, In progress, Blocked, and Completed statuses.
- Due dates, Low/Medium/High priority, and overdue-task highlighting.
- Employee daily work updates: completed work, next steps/to-do, pending work, and blockers.
- Manager dashboard with team task metrics and recent daily updates.
- Team reports with completion rate, overdue tasks, update counts, blocker-update counts, and CSV export.
- Basic performance evaluations with 1–5 ratings for quality, communication, ownership, and timeliness, plus reviewer comments.

### Additional improvements
- Role-based access: Admin, Manager, Employee.
- Prepared SQL statements via PDO.
- Password hashing with `password_hash()` and verification with `password_verify()`.
- CSRF tokens for state-changing forms.
- HTML output escaping to reduce XSS risk.
- Session ID regeneration after login.
- Activity log for login/logout, task changes, daily updates, account creation, and evaluations.
- Responsive dashboard and forms.
- Seeded demo records for quick review.

## Requirements
- PHP 8.0+ recommended (PDO MySQL extension enabled).
- MySQL 8.0+ or compatible MariaDB.
- Apache (XAMPP or WampServer).
- `mbstring` is recommended for dashboard text previews.

## Setup on Windows (XAMPP / WampServer)

1. Extract the ZIP.
2. Copy the `employee_work_performance_system` folder into:
   - XAMPP: `C:\xampp\htdocs\`
   - WampServer: `C:\wamp64\www\`
3. Start Apache and MySQL.
4. Open phpMyAdmin, choose **Import**, and import `database.sql`.
   - The script creates the `employee_work_performance` database and inserts demo users/tasks/updates/evaluation data.
   - **Warning:** `database.sql` drops and recreates this project's tables. Do not import it over an existing database containing data you need to keep.
5. Open `config.php` and update `$dbHost`, `$dbName`, `$dbUser`, and `$dbPass` if your local MySQL settings differ.
6. Visit `http://localhost/employee_work_performance_system/login.php`.

## Demo login credentials

All demo accounts use this password:

`password`

- Admin: `admin@workpulse.local`
- Manager: `manager@workpulse.local`
- Employee: `employee@workpulse.local`
- Employee: `asha@workpulse.local`

Change demo credentials before any deployment. The demo accounts and sample data are only for local evaluation.

## Role guide

- **Admin:** all manager capabilities, user creation, and task deletion.
- **Manager:** assign tasks, view team updates, view reports/export CSV, and record evaluations.
- **Employee:** view assigned tasks, update task status, and submit/view their own daily updates.

## Database tables

- `users` — user identities, role, password hash, active state.
- `tasks` — assignments, due dates, priorities, statuses.
- `daily_updates` — completed work, to-do, pending work, blockers, and work date.
- `evaluations` — manager ratings, review period, overall score, comments.
- `activity_logs` — basic audit trail.

## Design and evaluation notes

- The overall performance score is the arithmetic mean of the four 1–5 ratings.
- Task completion rate is completed tasks divided by assigned tasks.
- Overdue means due date is before today's date and the task is not completed.
- Reports intentionally distinguish current task status from daily-update activity in the selected date range.
- Performance scores should support a human manager conversation; they should not be treated as a fully automated or definitive measure of employee value.

## Before production deployment

This is an assignment/demo baseline, not a production-hardened HR platform. Before public deployment, add password reset/change workflows, stronger session/cookie settings, rate limiting, manager-to-team scoping, database backups, automated tests, audit-log access controls, and an appropriate privacy/retention policy. Configure HTTPS and use a least-privilege MySQL account rather than the local root account.
