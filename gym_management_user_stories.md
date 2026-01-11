# Gym Management System MVP – User Stories & System Design (Markdown Version)

## 1. Overview

Produk: **Gym Management System – Admin/Owner Web App (Laravel)**  
Fokus: sisi sistem (backend + web admin) untuk gym owner & staff.

Fitur inti MVP:
- Authentication & Role (Owner, Staff)
- Dashboard
- Member Management
- Membership Plans & Memberships
- Check-in & Attendance
- Basic Reporting (check-in & membership info)

---

## 2. Sprint Planning

| Sprint   | Start Date | End Date   | Stories                               | Total Story Points | Team Capacity | Status       |
|----------|------------|-----------|----------------------------------------|--------------------|--------------|-------------|
| Sprint 1 | 2026-01-13 | 2026-01-27| US-001, US-002, US-003                 | 18                 | 20           | Not Started |
| Sprint 2 | 2026-01-30 | 2026-02-13| US-004, US-005, US-006, US-013         | 14                 | 20           | Not Started |
| Sprint 3 | 2026-02-16 | 2026-03-02| US-007, US-008, US-009, US-014         | 21                 | 20           | Not Started |
| Sprint 4 | 2026-03-05 | 2026-03-19| US-010, US-011, US-012, US-015         | 19                 | 20           | Not Started |

Total: **15 user stories**, **72 story points**, target **8 minggu** (4 sprint × 2 minggu).

---

## 3. User Stories (Detail)

### Sprint 1 – Authentication & Dashboard

#### US-001 – Owner Login

- **Role**: Owner  
- **Story**:  
  As an Owner, I want to login to the admin panel so that I can access the gym management features.  
- **Priority**: High  
- **Story Points**: 5  
- **Status**: Todo  

**Acceptance Criteria:**
1. Owner can input email and password.
2. System validates email and password.
3. Owner is redirected to dashboard on successful login.
4. Error message shows on failed login.

**Tasks:**
- T-001: Setup Laravel Breeze authentication.
- T-002: Create login blade template.
- T-003: Setup role-based access control (OWNER/STAFF).

---

#### US-002 – View Dashboard

- **Role**: Owner  
- **Story**:  
  As an Owner, I want to see a dashboard with key metrics (active members, today's check-ins, monthly revenue) so that I can quickly assess gym performance.  
- **Priority**: High  
- **Story Points**: 8  
- **Status**: Todo  

**Acceptance Criteria:**
1. Dashboard displays total count of active members.
2. Dashboard displays count of today's check-ins.
3. Dashboard displays total revenue for current month.
4. All metrics update on page load or refresh.

**Tasks:**
- T-004: Create dashboard controller.
- T-005: Design dashboard layout with cards (Blade + Tailwind).
- T-006: Write Eloquent queries for metrics.

---

#### US-003 – Create Staff Account

- **Role**: Owner  
- **Story**:  
  As an Owner, I want to create staff accounts so that front desk staff can use the system.  
- **Priority**: High  
- **Story Points**: 5  
- **Status**: Todo  

**Acceptance Criteria:**
1. Owner can input staff name, email, and password.
2. System generates unique account for staff.
3. Staff receives login credentials (untuk MVP bisa manual share).
4. Staff is assigned `STAFF` role automatically.

**Tasks:**
- T-007: Create staff creation form.
- T-008: Implement staff controller (create/store).

---

### Sprint 2 – Member Management

#### US-004 – Search Members

- **Role**: Staff  
- **Story**:  
  As a Staff, I want to search for members by name/phone so that I can quickly find member details.  
- **Priority**: High  
- **Story Points**: 3  
- **Status**: Todo  

**Acceptance Criteria:**
1. Staff can search by member name (partial match).
2. Staff can search by member phone number.
3. Search results display member ID, name, phone, status.
4. Staff can click result to view full member profile.

**Tasks:**
- T-009: Create search form UI.
- T-010: Implement member search controller.

---

#### US-005 – Create New Member

- **Role**: Staff  
- **Story**:  
  As a Staff, I want to create a new member with basic info (name, phone, gender, DOB) so that new customers can be registered.  
- **Priority**: High  
- **Story Points**: 5  
- **Status**: Todo  

**Acceptance Criteria:**
1. Form requires name, phone, gender, date of birth.
2. Phone number is validated as unique.
3. System auto-generates unique `member_id` (misal GYM001).
4. New member is created with status `ACTIVE`.

**Tasks:**
- T-011: Create member form template.
- T-012: Implement member creation controller + auto generate `member_id`.
- T-013: Add validation for member data (Form Request).

---

#### US-006 – View Member Profile

- **Role**: Staff  
- **Story**:  
  As a Staff, I want to view a member's profile including membership status so that I know if they can access the gym.  
- **Priority**: High  
- **Story Points**: 3  
- **Status**: Todo  

**Acceptance Criteria:**
1. Member profile displays all personal information.
2. Profile shows current membership (plan, start_date, end_date, status).
3. Profile shows button to extend/assign membership.
4. Profile shows check-in history for last 30 days.

**Tasks:**
- T-014: Create member profile page.
- T-015: Display membership status and history on profile.

---

#### US-013 – Edit Member Info

- **Role**: Staff  
- **Story**:  
  As a Staff, I want to edit member information (name, phone, email, gender) so that member data stays up to date.  
- **Priority**: Medium  
- **Story Points**: 3  
- **Status**: Todo  

**Acceptance Criteria:**
1. Edit form pre-populates with current member data.
2. Staff can update name, phone, email, gender.
3. Updated data is saved to database.
4. Success message confirms update.

**Tasks:**
- T-027: Create member edit form.
- T-028: Implement member update controller.

---

### Sprint 3 – Membership Management

#### US-007 – Create Membership Plan

- **Role**: Owner  
- **Story**:  
  As an Owner, I want to create membership plans (name, duration, price) so that I can define pricing tiers.  
- **Priority**: High  
- **Story Points**: 5  
- **Status**: Todo  

**Acceptance Criteria:**
1. Owner can input plan name, duration (days), and price.
2. Owner can set plan as active/inactive.
3. All plans are displayed in a list with edit/delete options.
4. Inactive plans cannot be assigned to new members.

**Tasks:**
- T-016: Create membership plan form.
- T-017: Implement membership plan CRUD.

---

#### US-008 – Assign Membership

- **Role**: Staff  
- **Story**:  
  As a Staff, I want to assign a membership plan to a member so that their gym access is activated.  
- **Priority**: High  
- **Story Points**: 5  
- **Status**: Todo  

**Acceptance Criteria:**
1. Staff selects member and membership plan from dropdowns.
2. System auto-calculates `end_date = start_date + duration_days`.
3. Membership status is set to `ACTIVE`.
4. Member can now check-in to gym.

**Tasks:**
- T-018: Create membership assignment form.
- T-019: Implement membership logic (start_date, end_date, status).

---

#### US-009 – Auto Update Membership Status

- **Role**: System  
- **Story**:  
  As a System, I want membership status to automatically change to EXPIRED when end_date passes so that access is properly controlled.  
- **Priority**: High  
- **Story Points**: 8  
- **Status**: Todo  

**Acceptance Criteria:**
1. Daily job runs to check expired memberships.
2. Memberships with `end_date < today` are marked `EXPIRED`.
3. Check-in is blocked for `EXPIRED` memberships.
4. Owner can see membership expiry notifications (future).

**Tasks:**
- T-020: Create database migration for `memberships`.
- T-021: Setup Laravel Scheduler job for daily membership status update.

---

#### US-014 – View Member
