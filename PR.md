
We will implement these features step-by-step. Once a phase is complete, it will be marked as [x], and we will ask you to test it before moving on to the next.
Phase 1: Multi-Tenancy Foundation
[x] Create Church model and migration (id, name, type, etc.).
[x] Seed the initial branches: Adult Church (Main), Youth Church, Children Church.
[x] Add church_id foreign key to all core tables (Users, Members, Services, Attendances, Finances, etc.).
[x] Implement the Global ChurchScope to automatically filter data based on the logged-in user's church.
[x] Build the Super Admin UI dropdown to "switch" between the different church branches.
Phase 2: Roles & Access Control
[ ] Add new roles: super_admin, curate_pastor, attendance_manager, pa.
[ ] Configure specific permissions for these roles.
[ ] Restrict worker attendance visibility: hide it from the general view but make it visible to the curate_pastor.
Phase 3: Attendance Management System
[ ] Build reports/queries for "Most Punctual" and "Most Regular" members.
[ ] Implement a web-based Fingerprint attendance interface (inputting an ID manually or via a scanner that acts as a keyboard).
[ ] Build a fast, manual check-in interface specifically for members without phones (handled by attendance_manager).
[ ] Implement Sunday School attendance tracking (as a specific service type).
Phase 4: Counselling Booking System
[ ] Create CounsellingBooking model and database structure.
[ ] Build the member-facing UI to book a counselling session with the Head Pastor.
[ ] Build the PA dashboard to review, approve, or reject booking requests.
[ ] Add email/system notifications upon booking approval/rejection.
Phase 5: Notifications & Communication
[ ] Extend the Message model and tables to support an image_url field.
[ ] Implement the "Sermon Notification" feature (sending a visual banner + message to members).
[ ] Ensure all general notifications are isolated to the specific church branch.
