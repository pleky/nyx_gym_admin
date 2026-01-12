```markdown
# Gym Management System MVP – Complete User Stories (Production Ready)

**Version**: 2.0 (Updated with Soft Delete & Data Retention)  
**Date**: January 12, 2026  
**Author**: Development Team  
**Status**: Ready for Implementation

---

## 📊 Executive Summary

- **Total User Stories**: 20 (15 original + 5 new)
- **Total Story Points**: 111 points
- **Timeline**: 8-10 weeks (4 sprints × 2 weeks)
- **Tech Stack**: Laravel 11 + Blade/Tailwind + MySQL/PostgreSQL
- **Key Features**: Soft Delete, Audit Trail, Financial Compliance, Data Retention

---

## 🗂️ Sprint Planning

| Sprint | Duration | Start Date | End Date | Stories | Total Points | Status |
|--------|----------|------------|----------|---------|--------------|--------|
| Sprint 1 | 2 weeks | 2026-01-13 | 2026-01-27 | US-001, US-002, US-003, US-016 | 26 | Not Started |
| Sprint 2 | 2 weeks | 2026-01-30 | 2026-02-13 | US-004, US-005*, US-006, US-013*, US-017 | 19 | Not Started |
| Sprint 3 | 2 weeks | 2026-02-16 | 2026-03-02 | US-007, US-008*, US-009, US-014 | 21 | Not Started |
| Sprint 4 | 2 weeks | 2026-03-05 | 2026-03-19 | US-010*, US-011, US-012*, US-015, US-018, US-019 | 32 | Not Started |
| Future | TBD | TBD | TBD | US-020 | 13 | Backlog |

**Legend:**  
- `*` = Updated story  
- **Bold** = New story

---

## 📖 SPRINT 1: Authentication, Dashboard & Data Policy

### US-001: Owner Login

**Role**: Owner  
**Story**: As an Owner, I want to login to the admin panel so that I can access the gym management features.  
**Priority**: High  
**Story Points**: 5  
**Status**: Todo

**Acceptance Criteria:**
1. Owner can input email and password
2. System validates email and password
3. Owner is redirected to dashboard on successful login
4. Error message shows on failed login

**Tasks:**
- T-001: Setup Laravel Breeze authentication
- T-002: Create login blade template  
- T-003: Setup role-based access control (Laravel Gates/Policies)

---

### US-002: View Dashboard

**Role**: Owner  
**Story**: As an Owner, I want to see a dashboard with key metrics (active members, today's check-ins, monthly revenue) so that I can quickly assess gym performance.  
**Priority**: High  
**Story Points**: 8  
**Status**: Todo

**Acceptance Criteria:**
1. Dashboard displays total count of active members
2. Dashboard displays count of today's check-ins
3. Dashboard displays total revenue for current month
4. All metrics update on page load or refresh

**Tasks:**
- T-004: Create dashboard controller
- T-005: Design dashboard layout with cards (Blade + Tailwind)
- T-006: Write SQL queries for metrics (Laravel Eloquent)

---

### US-003: Create Staff Account

**Role**: Owner  
**Story**: As an Owner, I want to create staff accounts so that front desk staff can use the system.  
**Priority**: High  
**Story Points**: 5  
**Status**: Todo

**Acceptance Criteria:**
1. Owner can input staff name, email, and password
2. System generates unique account for staff
3. Staff receives login credentials
4. Staff is assigned 'STAFF' role automatically

**Tasks:**
- T-007: Create staff creation form (Blade, Tailwind)
- T-008: Implement staff controller (create/store)

---

### 🆕 US-016: Data Retention Policy

**Role**: Owner  
**Story**: As an Owner, I want all critical data (members, payments, attendance) to be soft deleted instead of permanently deleted so that I can maintain audit trail and reporting history.  
**Priority**: High  
**Story Points**: 8  
**Status**: Todo

**Acceptance Criteria:**
1. Deleting a member only sets `deleted_at` timestamp (soft delete)
2. Deleted members still appear in historical reports with `withTrashed()` flag
3. System prevents permanent deletion of payment records
4. Restore function available for accidentally deleted members
5. Deleted members are hidden from active member lists but retained in database
6. All financial data preserved for minimum 7 years (configurable)

**Tasks:**
- T-031: Add `deleted_at` column to members, payments, checkins, memberships tables (Laravel Migration)
- T-032: Implement SoftDeletes trait in all critical models (Laravel Eloquent)
- T-033: Update all queries to exclude soft-deleted records by default
- T-034: Create restore member functionality (Laravel Controller)

**Technical Notes:**
```php
// Add to migrations
$table->softDeletes(); // adds deleted_at column

// In models
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model {
    use SoftDeletes;
}

// Query examples
Member::all(); // excludes soft-deleted
Member::withTrashed()->get(); // includes soft-deleted
Member::onlyTrashed()->get(); // only soft-deleted
```

---

## 📖 SPRINT 2: Member Management with Soft Delete

### US-004: Search Members

**Role**: Staff  
**Story**: As a Staff, I want to search for members by name/phone so that I can quickly find member details.  
**Priority**: High  
**Story Points**: 3  
**Status**: Todo

**Acceptance Criteria:**
1. Staff can search by member name (partial match)
2. Staff can search by member phone number
3. Search results display member ID, name, phone, status
4. Staff can click result to view full member profile
5. By default, search excludes soft-deleted members

**Tasks:**
- T-009: Create search form UI (Blade, Alpine.js)
- T-010: Implement member search controller (Laravel)

---

### ✏️ US-005: Create New Member (UPDATED)

**Role**: Staff  
**Story**: As a Staff, I want to create a new member with basic info (name, phone, gender, DOB) so that new customers can be registered, with duplicate detection for soft-deleted members.  
**Priority**: High  
**Story Points**: 5  
**Status**: Todo

**Acceptance Criteria (UPDATED):**
1. Form requires name, phone, gender, date of birth
2. Phone number is validated as unique (including soft-deleted members)
3. System auto-generates unique `member_id` (e.g., GYM001)
4. New member is created with status ACTIVE
5. **NEW:** System checks if phone exists in soft-deleted members and offers restore option
6. **NEW:** If duplicate found, show option: "Restore existing member?" or "Create new with different phone"

**Tasks (UPDATED):**
- T-011: Create member form template (Blade, Tailwind)
- T-012: Implement member creation controller + auto generate member_id
- T-013: Add validation for member data (Laravel Form Request)
- **T-035: Check for soft-deleted duplicates before creating new member (Laravel Service)**
- **T-036: Add restore existing member option if phone matches deleted record (Blade + Controller)**

**Code Example:**
```php
// Check soft-deleted duplicates
$existingMember = Member::withTrashed()
    ->where('phone', $request->phone)
    ->first();

if ($existingMember && $existingMember->trashed()) {
    return redirect()->route('members.restore-prompt', $existingMember->id)
        ->with('info', 'A deleted member with this phone exists. Restore instead?');
}
```

---

### US-006: View Member Profile

**Role**: Staff  
**Story**: As a Staff, I want to view a member's profile including membership status so that I know if they can access the gym.  
**Priority**: High  
**Story Points**: 3  
**Status**: Todo

**Acceptance Criteria:**
1. Member profile displays all personal information
2. Profile shows current membership (plan, start_date, end_date, status)
3. Profile shows button to extend/assign membership
4. Profile shows check-in history for last 30 days

**Tasks:**
- T-014: Create member profile page (Blade, Tailwind)
- T-015: Display membership status and history on profile (Eloquent relationships)

---

### ✏️ US-013: Edit Member Info (UPDATED)

**Role**: Staff  
**Story**: As a Staff, I want to edit or deactivate member information so that member data stays up to date without losing historical records.  
**Priority**: Medium  
**Story Points**: 5 (increased from 3)  
**Status**: Todo

**Acceptance Criteria (UPDATED):**
1. Edit form pre-populates with current member data
2. Staff can update name, phone, email, gender
3. Staff can mark member as INACTIVE instead of deleting
4. **NEW:** Staff can soft delete member (sets deleted_at) with confirmation dialog
5. **NEW:** System prevents deletion if member has pending payments or active membership
6. **NEW:** Deleted member retains all historical data (payments, check-ins, memberships)
7. Updated data is saved to database
8. Success message confirms update or shows error if deletion blocked

**Tasks (UPDATED):**
- T-027: Create member edit form (Blade, Tailwind)
- T-028: Implement member update controller (Laravel)
- **T-037: Add soft delete button with confirmation modal (Blade, Alpine.js)**
- **T-038: Validate no pending payments/active membership before allowing delete (Laravel Service)**

**Validation Logic:**
```php
// Before soft delete
public function destroy(Member $member)
{
    // Check pending payments
    if ($member->payments()->where('status', 'PENDING')->exists()) {
        return back()->withErrors(['error' => 'Cannot delete member with pending payments']);
    }

    // Check active memberships
    if ($member->memberships()->where('status', 'ACTIVE')->exists()) {
        return back()->withErrors(['error' => 'Please expire membership before deleting member']);
    }

    $member->delete(); // Soft delete
    return redirect()->route('members.index')
        ->with('success', 'Member deleted successfully. Can be restored within 90 days.');
}
```

---

### 🆕 US-017: Restore Deleted Member

**Role**: Staff  
**Story**: As a Staff, I want to restore accidentally deleted members so that I can recover from mistakes without losing data.  
**Priority**: Medium  
**Story Points**: 3  
**Status**: Todo

**Acceptance Criteria:**
1. System shows list of soft-deleted members (last 90 days by default)
2. Staff can search deleted members by name/phone
3. Clicking "Restore" button reactivates the member (sets deleted_at = NULL)
4. Restored member retains all historical data (memberships, payments, check-ins)
5. Confirmation message after successful restore
6. Warning shown if restored member has expired membership

**Tasks:**
- T-039: Create "Deleted Members" page with filters (Blade, Tailwind)
- T-040: Implement restore functionality (Laravel Controller)
- T-041: Show warning if restoring member with expired membership (Blade alert)

**Code Example:**
```php
// Restore member
public function restore($id)
{
    $member = Member::onlyTrashed()->findOrFail($id);
    $member->restore();

    // Check membership status
    if (!$member->activeMembership) {
        return redirect()->route('members.show', $member)
            ->with('warning', 'Member restored but has no active membership. Please assign one.');
    }

    return redirect()->route('members.show', $member)
        ->with('success', 'Member restored successfully');
}
```

---

## 📖 SPRINT 3: Membership Management

### US-007: Create Membership Plan

**Role**: Owner  
**Story**: As an Owner, I want to create membership plans (name, duration, price) so that I can define pricing tiers.  
**Priority**: High  
**Story Points**: 5  
**Status**: Todo

**Acceptance Criteria:**
1. Owner can input plan name, duration (days), and price
2. Owner can set plan as active/inactive
3. All plans are displayed in a list with edit/delete options
4. Inactive plans cannot be assigned to new members

**Tasks:**
- T-016: Create membership plan form (Blade, Tailwind)
- T-017: Implement membership plan CRUD (Laravel Resource Controller)

---

### ✏️ US-008: Assign Membership (UPDATED)

**Role**: Staff  
**Story**: As a Staff, I want to assign a membership plan to a member so that their gym access is activated.  
**Priority**: High  
**Story Points**: 5  
**Status**: Todo

**Acceptance Criteria (UPDATED):**
1. Staff selects member and membership plan from dropdowns
2. System auto-calculates end_date = start_date + plan duration_days
3. Membership status is set to ACTIVE
4. Member can now check-in to gym
5. **NEW:** System prevents assigning membership to soft-deleted members
6. **NEW:** If member is INACTIVE, show warning and option to reactivate first

**Tasks:**
- T-018: Create membership assignment form (Blade, Tailwind)
- T-019: Implement membership logic (start_date, end_date, status) (Laravel Controller)

**Code Example:**
```php
public function store(Request $request)
{
    $member = Member::findOrFail($request->member_id);
    
    // Check if member is deleted
    if ($member->trashed()) {
        return back()->withErrors(['Member is deleted. Please restore before assigning membership.']);
    }

    $plan = MembershipPlan::findOrFail($request->plan_id);
    
    $membership = Membership::create([
        'member_id' => $member->id,
        'membership_plan_id' => $plan->id,
        'start_date' => $request->start_date,
        'end_date' => Carbon::parse($request->start_date)->addDays($plan->duration_days),
        'status' => 'ACTIVE',
    ]);

    return redirect()->route('members.show', $member)
        ->with('success', 'Membership assigned successfully');
}
```

---

### US-009: Auto Update Membership Status

**Role**: System  
**Story**: As a System, I want membership status to automatically change to EXPIRED when end_date passes so that access is properly controlled.  
**Priority**: High  
**Story Points**: 8  
**Status**: Todo

**Acceptance Criteria:**
1. Daily job runs at 00:01 AM to check expired memberships
2. Memberships with end_date < today are marked EXPIRED
3. Check-in is blocked for EXPIRED memberships
4. Owner can see membership expiry notifications (future enhancement)

**Tasks:**
- T-020: Create database migration for memberships table
- T-021: Setup Laravel Scheduler job for daily membership status update

**Code Example:**
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        Membership::where('status', 'ACTIVE')
            ->where('end_date', '<', now()->toDateString())
            ->update(['status' => 'EXPIRED']);
    })->dailyAt('00:01');
}
```

---

### US-014: View Membership History

**Role**: Staff  
**Story**: As a Staff, I want to view a member's membership history so that I can see past memberships and renewals.  
**Priority**: Medium  
**Story Points**: 3  
**Status**: Todo

**Acceptance Criteria:**
1. Member profile tab shows 'Membership History'
2. History lists all past and current memberships with plan, dates, status
3. Latest membership appears at the top
4. History is sortable by date
5. **Includes soft-deleted memberships for complete audit trail**

**Tasks:**
- T-029: Create membership history view with `withTrashed()` query (Blade component)

**Code Example:**
```php
// In Member profile
$memberships = $member->memberships()
    ->withTrashed()
    ->with('membershipPlan')
    ->orderBy('start_date', 'desc')
    ->get();
```

---

## 📖 SPRINT 4: Check-in, Reporting & Analytics

### ✏️ US-010: Check-in Member (UPDATED)

**Role**: Staff  
**Story**: As a Staff, I want to perform check-in for a member so that attendance is tracked.  
**Priority**: High  
**Story Points**: 5  
**Status**: Todo

**Acceptance Criteria (UPDATED):**
1. Staff enters member ID or searches by name/phone
2. System displays member name and current membership status
3. Clicking "Check-in" logs timestamp and saves check-in record
4. Success message displays after check-in
5. **NEW:** System blocks check-in for soft-deleted members
6. **NEW:** Display message: "Member account is inactive. Please contact admin to restore."

**Tasks:**
- T-022: Create check-in form UI (Blade, Tailwind)
- T-023: Implement check-in controller with soft-delete validation (Laravel)

**Code Example:**
```php
public function store(Request $request)
{
    $member = Member::where('member_id', $request->member_code)->first();
    
    if (!$member) {
        return back()->withErrors(['Member not found']);
    }

    if ($member->trashed()) {
        return back()->withErrors(['Member account is inactive. Contact admin to restore.']);
    }

    $activeMembership = $member->activeMembership;
    if (!$activeMembership) {
        return back()->withErrors(['No active membership. Please renew.']);
    }

    Checkin::create([
        'member_id' => $member->id,
        'gym_id' => auth()->user()->gym_id,
        'checked_in_at' => now(),
        'checked_in_by' => auth()->user()->name,
    ]);

    return back()->with('success', "Check-in successful for {$member->full_name}");
}
```

---

### US-011: Validate Membership on Check-in

**Role**: Staff  
**Story**: As a Staff, I want to be prevented from checking in expired members so that only valid members access the gym.  
**Priority**: High  
**Story Points**: 3  
**Status**: Todo

**Acceptance Criteria:**
1. Check-in form checks if member membership status = ACTIVE
2. If expired, error message: "Membership expired – please renew"
3. Check-in button is disabled for expired members
4. Quick link to renew membership appears for expired members

**Tasks:**
- T-024: Add membership validation logic before check-in (Laravel Service)

---

### ✏️ US-012: View Today's Check-ins (UPDATED)

**Role**: Owner  
**Story**: As an Owner, I want to see today's check-ins list so that I can monitor attendance.  
**Priority**: Medium  
**Story Points**: 3  
**Status**: Todo

**Acceptance Criteria (UPDATED):**
1. Page displays all check-ins from today
2. List shows member name, time checked-in, checked-in by (staff name)
3. Owner can filter/search in today's list
4. List refreshes or can be manually refreshed
5. **NEW:** Only show check-ins from active (non-deleted) members by default
6. **NEW:** Toggle option "Include deleted members" for audit purposes

**Tasks:**
- T-025: Create today's check-ins list page (Blade, Tailwind)
- T-026: Implement check-in query with soft-delete filter toggle (Laravel)

**Code Example:**
```php
public function todayCheckins(Request $request)
{
    $query = Checkin::with('member')
        ->whereDate('checked_in_at', today())
        ->latest('checked_in_at');

    // Toggle to include deleted members
    if ($request->has('include_deleted')) {
        $query->whereHas('member', function($q) {
            $q->withTrashed();
        });
    }

    $checkins = $query->get();
    
    return view('checkins.today', compact('checkins'));
}
```

---

### US-015: View Check-in Reports

**Role**: Owner  
**Story**: As an Owner, I want to view check-in reports (daily, weekly, monthly) so that I can analyze attendance trends.  
**Priority**: Medium  
**Story Points**: 8  
**Status**: Todo

**Acceptance Criteria:**
1. Owner can select date range for report
2. Report shows daily check-in count
3. Report shows weekly/monthly summary
4. Report can be exported as PDF or CSV

**Tasks:**
- T-030: Create check-in reports page (Blade, Tailwind, Chart.js)

---

### 🆕 US-018: Financial Audit Report

**Role**: Owner  
**Story**: As an Owner, I want to view financial reports including deleted members and refunded payments so that I have complete audit trail for tax and compliance.  
**Priority**: High  
**Story Points**: 8  
**Status**: Todo

**Acceptance Criteria:**
1. Report includes ALL payments (including from deleted members) via `withTrashed()`
2. Report shows payment status (PAID, PENDING, REFUNDED, CANCELLED)
3. Filter by date range, payment method, status
4. Export to CSV/PDF with complete transaction history
5. Deleted member payments are marked with "🗑️ Deleted Member" indicator
6. Total revenue calculation includes only PAID status
7. Separate totals for each payment method
8. Refund amounts displayed separately

**Tasks:**
- T-042: Create financial audit report page (Blade, Tailwind)
- T-043: Implement query with `withTrashed()` for payments (Laravel Eloquent)
- T-044: Add filters for date range, status, method (Blade, Alpine.js)
- T-045: Export functionality CSV/PDF (Laravel Excel / DomPDF)

**Code Example:**
```php
public function financialAudit(Request $request)
{
    $payments = Payment::withTrashed()
        ->with(['member' => function($q) {
            $q->withTrashed();
        }])
        ->whereBetween('created_at', [$request->start_date, $request->end_date])
        ->when($request->status, function($q) use ($request) {
            $q->where('status', $request->status);
        })
        ->get();

    $totalRevenue = $payments->where('status', 'PAID')->sum('amount');
    $totalRefunds = $payments->where('status', 'REFUNDED')->sum('amount');
    $netRevenue = $totalRevenue - $totalRefunds;

    return view('reports.financial-audit', compact('payments', 'totalRevenue', 'totalRefunds', 'netRevenue'));
}
```

---

### 🆕 US-019: Member Churn Report

**Role**: Owner  
**Story**: As an Owner, I want to see report of members who stopped (deleted/inactive) this month so that I can analyze retention and churn rate.  
**Priority**: Medium  
**Story Points**: 5  
**Status**: Todo

**Acceptance Criteria:**
1. Report shows members marked INACTIVE or soft-deleted in selected period
2. Display churn rate percentage: `(churned members / total members at start) * 100`
3. Show reason if captured during deletion
4. Filter by date range (default: current month)
5. Export to CSV
6. Compare with previous period (e.g., last month)
7. Breakdown by: voluntary leave vs. expired membership

**Tasks:**
- T-046: Create churn report page (Blade, Tailwind)
- T-047: Calculate churn metrics (Laravel Service)
- T-048: Add optional "reason for leaving" field on member deletion (Migration + Form)

**Code Example:**
```php
public function churnReport(Request $request)
{
    $startDate = $request->start_date ?? now()->startOfMonth();
    $endDate = $request->end_date ?? now();

    // Total members at start of period
    $totalMembersStart = Member::where('created_at', '<', $startDate)->count();

    // Churned members (deleted or inactive)
    $churnedMembers = Member::onlyTrashed()
        ->whereBetween('deleted_at', [$startDate, $endDate])
        ->count();

    $inactiveMembers = Member::where('status', 'INACTIVE')
        ->whereBetween('updated_at', [$startDate, $endDate])
        ->count();

    $totalChurned = $churnedMembers + $inactiveMembers;
    $churnRate = $totalMembersStart > 0 ? ($totalChurned / $totalMembersStart) * 100 : 0;

    return view('reports.churn', compact('totalChurned', 'churnRate', 'churnedMembers', 'inactiveMembers'));
}
```

---

## 📖 FUTURE: Compliance & Long-term

### 🆕 US-020: Data Retention Compliance

**Role**: System  
**Story**: As a System, I want to permanently delete member data older than 7 years (configurable) to comply with data retention laws while preserving payment records indefinitely.  
**Priority**: Low (Future)  
**Story Points**: 13  
**Status**: Backlog

**Acceptance Criteria:**
1. Daily cron job checks soft-deleted members older than retention period (7 years default)
2. System permanently deletes member personal data (GDPR right to be forgotten)
3. Payment records are anonymized but retained (amount, date, method kept; member name replaced with "Deleted User #XXX")
4. Checkin records are anonymized similarly
5. Owner receives email notification 30 days before permanent deletion
6. Retention period is configurable in admin settings panel
7. Anonymization is irreversible and logged for audit

**Tasks:**
- T-049: Create permanent deletion cron job (Laravel Scheduler)
- T-050: Implement data anonymization for payments/checkins (Laravel Service)
- T-051: Add retention period setting in admin panel (Blade, Settings table)
- T-052: Create audit log for permanent deletions (Migration + Model)
- T-053: Email notification system for upcoming deletions (Laravel Mail)

**Code Example:**
```php
// Daily cron job
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        $retentionYears = Setting::get('data_retention_years', 7);
        
        $oldMembers = Member::onlyTrashed()
            ->where('deleted_at', '<', now()->subYears($retentionYears))
            ->get();

        foreach ($oldMembers as $member) {
            // Anonymize payments
            Payment::where('member_id', $member->id)->update([
                'member_name_anonymous' => "Deleted User #{$member->id}",
                // Keep: amount, date, method, status for tax/audit
            ]);

            // Anonymize checkins
            Checkin::where('member_id', $member->id)->update([
                'member_name_anonymous' => "Deleted User #{$member->id}",
            ]);

            // Log permanent deletion
            AuditLog::create([
                'action' => 'permanent_delete_member',
                'member_id' => $member->id,
                'performed_at' => now(),
            ]);

            // Permanently delete member
            $member->forceDelete();
        }
    })->dailyAt('02:00');
}
```

---

## 📊 Complete User Stories Table

| Story ID | Sprint | Role | Story Title | Priority | Points | Type |
|----------|--------|------|-------------|----------|--------|------|
| US-001 | 1 | Owner | Owner Login | High | 5 | Original |
| US-002 | 1 | Owner | View Dashboard | High | 8 | Original |
| US-003 | 1 | Owner | Create Staff Account | High | 5 | Original |
| **US-016** | **1** | **Owner** | **Data Retention Policy** | **High** | **8** | **NEW** |
| US-004 | 2 | Staff | Search Members | High | 3 | Original |
| US-005 | 2 | Staff | Create New Member | High | 5 | Updated |
| US-006 | 2 | Staff | View Member Profile | High | 3 | Original |
| US-013 | 2 | Staff | Edit Member Info | Medium | 5 | Updated |
| **US-017** | **2** | **Staff** | **Restore Deleted Member** | **Medium** | **3** | **NEW** |
| US-007 | 3 | Owner | Create Membership Plan | High | 5 | Original |
| US-008 | 3 | Staff | Assign Membership | High | 5 | Updated |
| US-009 | 3 | System | Auto Update Membership Status | High | 8 | Original |
| US-014 | 3 | Staff | View Membership History | Medium | 3 | Original |
| US-010 | 4 | Staff | Check-in Member | High | 5 | Updated |
| US-011 | 4 | Staff | Validate Membership on Check-in | High | 3 | Original |
| US-012 | 4 | Owner | View Today's Check-ins | Medium | 3 | Updated |
| US-015 | 4 | Owner | View Check-in Reports | Medium | 8 | Original |
| **US-018** | **4** | **Owner** | **Financial Audit Report** | **High** | **8** | **NEW** |
| **US-019** | **4** | **Owner** | **Member Churn Report** | **Medium** | **5** | **NEW** |
| **US-020** | **Future** | **System** | **Data Retention Compliance** | **Low** | **13** | **NEW** |

**Summary:**
- **Original Stories**: 15
- **New Stories**: 5  
- **Updated Stories**: 5
- **Total Stories**: 20
- **Total Points**: 111

---

## 🎯 Implementation Priority

### Phase 1 (MVP Core - Sprint 1-2)
1. US-016: Data Retention Policy (foundation for all soft deletes)
2. US-001, US-002, US-003: Authentication & Dashboard
3. US-004, US-005, US-006: Member management
4. US-017: Restore deleted members

### Phase 2 (Business Logic - Sprint 3)
5. US-007, US-008, US-009: Membership management
6. US-014: Membership history

### Phase 3 (Operations - Sprint 4)
7. US-010, US-011, US-012: Check-in system
8. US-015: Check-in reports

### Phase 4 (Analytics - Sprint 4)
9. US-018: Financial audit trail
10. US-019: Churn analysis

### Phase 5 (Compliance - Future)
11. US-020: Long-term data retention & GDPR compliance

---

## 🔐 Key Business Rules

### Data Retention
- **Members**: Soft delete only, retained indefinitely (or until GDPR compliance cleanup)
- **Payments**: NEVER permanently delete (tax & audit compliance)
- **Check-ins**: Soft delete, retained for attendance history
- **Memberships**: Soft delete, retained for revenue reporting

### Soft Delete Behavior
- Deleted records have `deleted_at` timestamp set
- Default queries exclude soft-deleted records
- Use `withTrashed()` for historical reports
- Use `onlyTrashed()` to view deleted records only
- `restore()` method available for recovery

### Validation Rules
- Cannot delete member with:
  - Pending payments
  - Active membership
- Cannot assign membership to deleted member
- Cannot check-in deleted member
- Phone/email uniqueness checks include soft-deleted records

---

## 📋 Tasks Summary

### New Tasks (Sprint 1-2)
- T-031: Add soft delete columns (migrations)
- T-032: Implement SoftDeletes trait (models)
- T-033: Update queries (controllers)
- T-034: Restore functionality (controller + view)
- T-035: Check soft-deleted duplicates (service)
- T-036: Restore prompt UI (blade)
- T-037: Soft delete button (blade)
- T-038: Delete validation (service)
- T-039: Deleted members page (blade)
- T-040: Restore controller (controller)
- T-041: Expired membership warning (blade)

### New Tasks (Sprint 4)
- T-042: Financial audit report page (blade)
- T-043: withTrashed() queries (eloquent)
- T-044: Report filters (blade + alpine)
- T-045: Export CSV/PDF (laravel excel)
- T-046: Churn report page (blade)
- T-047: Churn metrics calculation (service)
- T-048: Add "reason" field (migration)

### Future Tasks
- T-049: Permanent deletion cron (scheduler)
- T-050: Data anonymization (service)
- T-051: Retention settings (blade + migration)
- T-052: Audit log (migration + model)
- T-053: Email notifications (mail)

---

## 🚀 Getting Started

### Step 1: Database Setup
```bash
# Run migrations with soft delete support
php artisan migrate

# Verify deleted_at columns exist
php artisan tinker
>>> Schema::hasColumn('members', 'deleted_at'); // should return true
```

### Step 2: Model Setup
```php
// Ensure all critical models use SoftDeletes trait
// app/Models/Member.php
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use SoftDeletes;
    
    protected $dates = ['deleted_at'];
}
```

### Step 3: Update Controllers
```php
// Update existing queries to handle soft deletes
Member::all(); // excludes deleted
Member::withTrashed()->get(); // includes deleted
```

### Step 4: Testing
```bash
# Test soft delete functionality
php artisan test --filter MemberDeletionTest
```

---

## 📚 References

- Laravel Soft Deletes: https://laravel.com/docs/11.x/eloquent#soft-deleting
- Data Retention Best Practices: GDPR Article 5(1)(e)
- Financial Audit Requirements: Tax regulations (country-specific)

---

**END OF DOCUMENT**

*Last Updated: January 12, 2026*  
*Version: 2.0 (Production Ready with Soft Delete)*
```