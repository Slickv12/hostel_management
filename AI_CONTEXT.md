HOSTEL MANAGEMENT SYSTEM
ARCHITECTURAL MEMORY FILE

This file defines permanent architectural rules.
All future changes must respect these rules.

--------------------------------------------------
1. PROJECT SCOPE
--------------------------------------------------

This is an academic Hostel Management System built using:

- PHP (procedural style, no frameworks)
- MySQL
- HTML
- CSS
- XAMPP environment

The system must remain simple.
No frameworks, no MVC conversion, no OOP layers.

This project is intended for Software Modeling & Design diagrams.
Architecture clarity is more important than complexity.

--------------------------------------------------
2. ROLE MODEL (STRICT)
--------------------------------------------------

Only two system roles exist:

- student
- rector

Admin must NEVER exist as a login role.

Database administrator exists only at database level,
NOT inside the application.

Rector accounts are created manually in database.
There must NOT be a rector registration page.

--------------------------------------------------
3. ACCOUNT STATUS RULE
--------------------------------------------------

Users table must contain:

status ENUM('pending','active')

Rules:

- Student registers → status = 'pending'
- Rector approves student → status = 'active'
- Only 'active' users can login
- Once active, user remains active until permanently deleted
- No reverting back to 'pending'

Student deletion must be permanent (hard delete).

--------------------------------------------------
4. ROOM SYSTEM (SINGLE SOURCE OF TRUTH)
--------------------------------------------------

Room allocation must use ONLY:

room_allocation table

The following columns must NOT exist:

- users.room_id
- rooms.current_occupants

Room capacity must be calculated dynamically using COUNT().

Rules:

- One student can have only one room.
- A room cannot exceed its capacity.
- Room changes overwrite allocation (no room history).
- No room assignment history is stored.

--------------------------------------------------
5. FEES SYSTEM
--------------------------------------------------

Fee rules:

- One fee record per student.
- Fee is yearly.
- No payment gateway.
- Student only sees current due.
- Rector can update fee amount, due date, and status.
- No historical fee records required.

--------------------------------------------------
6. LEAVE SYSTEM
--------------------------------------------------

Rules:

- Student submits leave request.
- Rector approves or rejects.
- Rector cannot modify leave dates.
- Student can cancel leave only if status = 'pending'.
- No automatic expiration logic required.
- approved_by and approved_at must be recorded.

--------------------------------------------------
7. NOTICE SYSTEM
--------------------------------------------------

Rules:

- Notices are created by rector.
- Notices are visible only to logged-in users.
- Rector can edit notices.
- Rector can delete notices.
- No notice expiry logic required.

--------------------------------------------------
8. DASHBOARD REQUIREMENTS
--------------------------------------------------

Rector dashboard must show:

- total students
- students without room
- available rooms
- pending approvals
- pending leave requests
- unpaid fees

Student dashboard must show:

- room number
- roommate list (name, phone, id)

Student dashboard must NOT show:
- system statistics
- fee analytics
- leave analytics

--------------------------------------------------
9. STUDENT PERMISSIONS
--------------------------------------------------

Students can edit only:

- phone
- address
- course details

Students cannot:

- change role
- change approval status
- assign room
- modify fees
- modify leave status

--------------------------------------------------
10. ACTIVITY LOGGING
--------------------------------------------------

The system must log the following actions:

- student approval
- room assignment
- fee update
- leave approval/rejection
- student deletion

Room history and fee history are NOT required.
Only action logging is required.

--------------------------------------------------
11. STRUCTURE RULES
--------------------------------------------------

System must remain procedural PHP.

Reusable files allowed:

- config/database.php
- includes/auth_check.php
- includes/sidebar.php

Do NOT introduce:

- MVC architecture
- Controllers folder
- Models folder
- Routing systems
- Frameworks
- Third-party packages
- APIs

CSS should be organized professionally for future UI updates.
Prefer a centralized CSS structure over page-specific CSS.

--------------------------------------------------
12. FUTURE FEATURE LOCK
--------------------------------------------------

Do NOT implement:

- attendance system
- complaint system
- mess management
- hostel blocks
- multi-hostel support

Complaint system may be considered in future
but must NOT be implemented unless explicitly requested.

--------------------------------------------------
END OF ARCHITECTURAL MEMORY
--------------------------------------------------
