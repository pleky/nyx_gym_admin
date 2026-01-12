# User Stories to Laravel Concepts Mapping

> See how each user story teaches specific Laravel & database concepts

---

## 📖 Purpose

This document maps all 20 user stories to the Laravel and database concepts you'll learn. Use this as a reference to understand **what you're learning** when implementing each feature.

---

## **SPRINT 1: Foundation & Data Policy**

### **US-001: Owner Login**

**Priority**: High | **Points**: 5

**Laravel Concepts:**
- ✅ Laravel Breeze (authentication scaffolding)
- ✅ Middleware (`auth`, `verified`)
- ✅ Session management
- ✅ Password hashing (bcrypt)
- ✅ CSRF protection
- ✅ Route protection (`middleware('auth')`)

**Database Concepts:**
- ✅ `users` table schema
- ✅ `remember_token` for "Remember Me" functionality
- ✅ `email_verified_at` for email verification
- ✅ Unique constraints on email

**Files Created/Modified:**
- `routes/auth.php` - Authentication routes
- `app/Http/Controllers/Auth/*` - Login, register, password reset
- `resources/views/auth/login.blade.php` - Login form
- `app/Http/Middleware/Authenticate.php` - Auth middleware

**Code Example:**
```php
// Route protection
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Check authentication in blade
@auth
    <p>Welcome, {{ Auth::user()->name }}!</p>
@endauth
```

---

### **US-002: View Dashboard with Metrics**

**Priority**: High | **Points**: 8

**Laravel Concepts:**
- ✅ Eloquent aggregates (`count()`, `sum()`)
- ✅ Query scopes (local scopes for reusable queries)
- ✅ Blade components (`<x-stat-card />`)
- ✅ View composers (share data to views)
- ✅ Carbon for date filtering

**Database Concepts:**
- ✅ Aggregate functions (COUNT, SUM)
- ✅ WHERE clauses with date functions
- ✅ JOIN operations (implicit via Eloquent relationships)
- ✅ Index usage for performance

**Eloquent Examples:**
```php
// Total active members (exclude soft-deleted)
$activeMembers = Member::where('status', 'ACTIVE')->count();

// Today's check-ins
$todayCheckins = CheckIn::whereDate('checked_in_at', today())->count();

// Monthly revenue
$monthlyRevenue = Payment::whereMonth('created_at', now()->month)
    ->where('status', 'PAID')
    ->sum('amount');

// With relationships (avoid N+1)
$gym = Gym::with(['members', 'payments'])->find(1);
```

**Index Impact:**
```sql
-- Without index on checked_in_at: Full table scan
-- With index: Fast range lookup
CREATE INDEX checkins_checked_in_at_index ON checkins (checked_in_at);
```

---

### **US-003: Create Staff Account**

**Priority**: High | **Points**: 5

**Laravel Concepts:**
- ✅ Form validation (`FormRequest` classes)
- ✅ Resource controllers (RESTful methods)
- ✅ Eloquent create/insert operations
- ✅ Authorization (Gates: only owners can create staff)
- ✅ Policy classes (`UserPolicy`)
- ✅ Password hashing (`Hash::make()`)

**Database Concepts:**
- ✅ UNIQUE constraint enforcement (email)
- ✅ ENUM validation (role: OWNER, STAFF)
- ✅ Foreign key (gym_id)
- ✅ Default values (role defaults to STAFF)

**Code Example:**
```php
// Form Request validation
class StoreUserRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'gym_id' => 'required|exists:gyms,id',
        ];
    }
}

// Controller
public function store(StoreUserRequest $request)
{
    $this->authorize('create', User::class); // Policy check
    
    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => 'STAFF', // Default
        'gym_id' => auth()->user()->gym_id,
    ]);
    
    return redirect()->route('users.index')
        ->with('success', 'Staff created successfully');
}

// Policy
class UserPolicy
{
    public function create(User $user)
    {
        return $user->role === 'OWNER';
    }
}
```

---

### **US-016: Data Retention Policy (Soft Deletes)**

**Priority**: High | **Points**: 8

**Laravel Concepts:**
- ✅ `SoftDeletes` trait
- ✅ Global scopes (auto-filter deleted records)
- ✅ `withTrashed()`, `onlyTrashed()`, `restore()` methods
- ✅ Model events (`deleting`, `deleted`, `restoring`)
- ✅ Cascade soft delete pattern

**Database Concepts:**
- ✅ `deleted_at` timestamp column
- ✅ Index on `deleted_at` for performance
- ✅ NULL vs NOT NULL filtering
- ✅ Data retention compliance (7-10 years)

**Implementation:**
```php
// Migration
$table->softDeletes(); // Adds deleted_at column
$table->index('deleted_at'); // Performance

// Model
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use SoftDeletes;
}

// Usage
Member::all(); // Excludes soft-deleted (WHERE deleted_at IS NULL)
Member::withTrashed()->get(); // Includes soft-deleted
Member::onlyTrashed()->get(); // Only soft-deleted

// Restore
$member = Member::withTrashed()->find(1);
$member->restore(); // Sets deleted_at = NULL

// Force delete (permanent - use carefully!)
$member->forceDelete();
```

**Cascade Soft Delete:**
```php
class Member extends Model
{
    protected static function booted()
    {
        static::deleting(function ($member) {
            // Cascade soft delete to related records
            $member->memberships()->delete();
            $member->payments()->delete();
            $member->checkIns()->delete();
        });
    }
}
```

**Deep Dive**: [soft-deletes-deep-dive.md](../01-fundamentals/soft-deletes-deep-dive.md)

---

## **SPRINT 2: Member Management**

### **US-004: Search Members**

**Priority**: High | **Points**: 3

**Laravel Concepts:**
- ✅ Query Builder (`where`, `orWhere`, `like`)
- ✅ Pagination (`paginate()`, `links()`)
- ✅ Blade loops (`@foreach`, `@forelse`)
- ✅ Query scopes (reusable search logic)
- ✅ Alpine.js for live search (optional)

**Database Concepts:**
- ✅ LIKE operator with wildcards (`%search%`)
- ✅ OR conditions in WHERE clause
- ✅ Index on searchable columns (full_name, phone)
- ✅ Case-insensitive search (`ILIKE` in PostgreSQL)

**Code Example:**
```php
// Controller
public function index(Request $request)
{
    $search = $request->input('search');
    
    $members = Member::query()
        ->when($search, function ($query, $search) {
            $query->where('full_name', 'ILIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('member_id', 'LIKE', "%{$search}%");
        })
        ->paginate(20);
    
    return view('members.index', compact('members', 'search'));
}

// Using Query Scope (better)
class Member extends Model
{
    public function scopeSearch($query, $term)
    {
        return $query->where('full_name', 'ILIKE', "%{$term}%")
                     ->orWhere('phone', 'LIKE', "%{$term}%");
    }
}

// Controller with scope
$members = Member::search($search)->paginate(20);
```

**Blade Template:**
```blade
<form method="GET" action="{{ route('members.index') }}">
    <input type="text" name="search" value="{{ $search }}" 
           placeholder="Search by name or phone...">
    <button type="submit">Search</button>
</form>

@forelse($members as $member)
    <tr>
        <td>{{ $member->member_id }}</td>
        <td>{{ $member->full_name }}</td>
        <td>{{ $member->phone }}</td>
    </tr>
@empty
    <tr><td colspan="3">No members found</td></tr>
@endforelse

{{ $members->links() }} {{-- Pagination --}}
```

---

### **US-005: Create Member with Duplicate Detection**

**Priority**: High | **Points**: 5

**Laravel Concepts:**
- ✅ Form validation (unique with soft deletes)
- ✅ Conditional logic in controllers
- ✅ Flash messages (`session()->flash()`, `with()`)
- ✅ Service layer pattern (business logic separation)
- ✅ Model events for auto-generation (member_id)

**Database Concepts:**
- ✅ UNIQUE constraint challenges with soft deletes
- ✅ Composite queries (`withTrashed()->where()`)
- ✅ Transaction handling for data integrity

**Advanced Pattern:**
```php
// Check INCLUDING soft-deleted members
$existing = Member::withTrashed()
    ->where('phone', $request->phone)
    ->first();

if ($existing) {
    if ($existing->trashed()) {
        // Offer restore option
        return redirect()
            ->route('members.restore-prompt', $existing->id)
            ->with('info', 'Member with this phone was previously deleted. Restore instead?');
    } else {
        // Active duplicate
        return back()
            ->withErrors(['phone' => 'Phone number already exists'])
            ->withInput();
    }
}

// Create new member
$member = Member::create($validated);
```

**Auto-Generate Member ID (Model Event):**
```php
class Member extends Model
{
    protected static function booted()
    {
        static::creating(function ($member) {
            if (empty($member->member_id)) {
                // Generate: MBR-0001, MBR-0002, etc.
                $lastId = Member::withTrashed()->max('id') ?? 0;
                $member->member_id = 'MBR-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
```

**Service Layer (Optional but Professional):**
```php
class MemberService
{
    public function createOrRestore(array $data)
    {
        $existing = Member::withTrashed()
            ->where('phone', $data['phone'])
            ->first();
        
        if ($existing && $existing->trashed()) {
            $existing->restore();
            $existing->update($data);
            return ['action' => 'restored', 'member' => $existing];
        }
        
        return ['action' => 'created', 'member' => Member::create($data)];
    }
}
```

---

### **US-006: View Member Profile**

**Priority**: High | **Points**: 3

**Laravel Concepts:**
- ✅ Route model binding (`Route::get('/members/{member}')`)
- ✅ Eager loading (`with()` to prevent N+1)
- ✅ Relationship methods (`$member->memberships`)
- ✅ Collection methods (`filter()`, `first()`, `sortBy()`)
- ✅ Blade conditionals (`@if`, `@isset`)

**Database Concepts:**
- ✅ JOIN operations via relationships
- ✅ LEFT JOIN for optional relationships
- ✅ Foreign key navigation

**Code Example:**
```php
// Controller (with route model binding)
public function show(Member $member)
{
    // Eager load relationships (prevent N+1 problem)
    $member->load([
        'memberships.plan', // Nested relationship
        'payments' => function ($query) {
            $query->latest()->limit(10); // Last 10 payments
        },
        'checkIns' => function ($query) {
            $query->where('checked_in_at', '>=', now()->subDays(30));
        }
    ]);
    
    return view('members.show', compact('member'));
}

// In Member model
public function activeMembership()
{
    return $this->hasOne(Membership::class)
        ->where('status', 'ACTIVE')
        ->where('end_date', '>=', today());
}

// Blade
@if($member->activeMembership)
    <span class="badge badge-success">Active</span>
    <p>Expires: {{ $member->activeMembership->end_date->format('M d, Y') }}</p>
@else
    <span class="badge badge-danger">No Active Membership</span>
@endif
```

**N+1 Problem Prevention:**
```php
// ❌ BAD: N+1 queries
$members = Member::all();
foreach ($members as $member) {
    echo $member->memberships->count(); // Query per member!
}

// ✅ GOOD: Eager loading
$members = Member::with('memberships')->get();
foreach ($members as $member) {
    echo $member->memberships->count(); // No extra queries
}
```

---

### **US-013: Edit/Delete Member**

**Priority**: Medium | **Points**: 5

**Laravel Concepts:**
- ✅ Route model binding (auto-find member)
- ✅ Policy authorization (`@can('delete', $member)`)
- ✅ Validation before delete (business rules)
- ✅ Error handling (`try-catch`, validation errors)
- ✅ Flash messages (success/error feedback)

**Database Concepts:**
- ✅ Foreign key constraint checking
- ✅ Soft delete vs status change
- ✅ Transaction rollback on error

**Implementation:**
```php
// Policy
class MemberPolicy
{
    public function delete(User $user, Member $member)
    {
        // Only owners can delete, or staff of same gym
        return $user->role === 'OWNER' || 
               ($user->role === 'STAFF' && $user->gym_id === $member->gym_id);
    }
}

// Controller
public function destroy(Member $member)
{
    $this->authorize('delete', $member);
    
    // Business rule: Check active memberships
    if ($member->memberships()->where('status', 'ACTIVE')->exists()) {
        return back()->withErrors([
            'error' => 'Cannot delete member with active membership. Please expire it first.'
        ]);
    }
    
    // Business rule: Check pending payments
    if ($member->payments()->where('status', 'PENDING')->exists()) {
        return back()->withErrors([
            'error' => 'Cannot delete member with pending payments.'
        ]);
    }
    
    // Soft delete
    $member->delete();
    
    return redirect()->route('members.index')
        ->with('success', 'Member deleted successfully. Can be restored within 90 days.');
}
```

**Blade Authorization:**
```blade
@can('delete', $member)
    <form method="POST" action="{{ route('members.destroy', $member) }}"
          onsubmit="return confirm('Are you sure?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Delete</button>
    </form>
@endcan
```

---

### **US-017: Restore Deleted Member**

**Priority**: Medium | **Points**: 3

**Laravel Concepts:**
- ✅ `onlyTrashed()` scope
- ✅ `restore()` method
- ✅ Conditional views (show warnings)
- ✅ Flash messages with types (success, warning, error)
- ✅ Route grouping for trashed resources

**Code Example:**
```php
// Route
Route::get('/members/trashed', [MemberController::class, 'trashed'])
    ->name('members.trashed');
Route::post('/members/{id}/restore', [MemberController::class, 'restore'])
    ->name('members.restore');

// Controller
public function trashed()
{
    $deletedMembers = Member::onlyTrashed()
        ->where('deleted_at', '>', now()->subDays(90)) // Last 90 days
        ->orderBy('deleted_at', 'desc')
        ->paginate(20);
    
    return view('members.trashed', compact('deletedMembers'));
}

public function restore($id)
{
    $member = Member::onlyTrashed()->findOrFail($id);
    $member->restore();
    
    // Check if needs membership
    if (!$member->activeMembership) {
        return redirect()->route('members.show', $member)
            ->with('warning', 'Member restored but has no active membership. Please assign one.');
    }
    
    return redirect()->route('members.show', $member)
        ->with('success', 'Member restored successfully!');
}
```

---

## **SPRINT 3: Membership Management**

### **US-007: Create Membership Plans**

**Priority**: High | **Points**: 5

**Laravel Concepts:**
- ✅ Resource controllers (7 RESTful methods)
- ✅ Form validation (duration > 0, price > 0)
- ✅ Active/inactive toggles (boolean columns)
- ✅ Soft delete on plans
- ✅ Scopes for active plans

**Database Concepts:**
- ✅ CHECK constraints (`duration_days > 0`)
- ✅ Boolean columns (`is_active`)
- ✅ Decimal precision for currency (`decimal(10,2)`)

**Code Example:**
```php
// Migration
$table->decimal('price', 10, 2); // Max: 99,999,999.99
$table->boolean('is_active')->default(true);
DB::statement('ALTER TABLE membership_plans ADD CONSTRAINT check_duration_positive CHECK (duration_days > 0)');

// Model scope
class MembershipPlan extends Model
{
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

// Usage
$plans = MembershipPlan::active()->get();
```

---

### **US-008: Assign Membership**

**Priority**: High | **Points**: 5

**Laravel Concepts:**
- ✅ Carbon date manipulation (`addDays()`, `parse()`)
- ✅ Eloquent relationships (member, plan)
- ✅ Database transactions (`DB::transaction()`)
- ✅ Model events (creating, created)

**Code Example:**
```php
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

public function store(Request $request)
{
    $validated = $request->validate([
        'member_id' => 'required|exists:members,id',
        'plan_id' => 'required|exists:membership_plans,id',
        'start_date' => 'required|date',
    ]);
    
    $plan = MembershipPlan::findOrFail($validated['plan_id']);
    
    // Calculate end date
    $startDate = Carbon::parse($validated['start_date']);
    $endDate = $startDate->copy()->addDays($plan->duration_days);
    
    // Transaction: Create membership + payment
    DB::transaction(function () use ($validated, $plan, $startDate, $endDate) {
        $membership = Membership::create([
            'member_id' => $validated['member_id'],
            'membership_plan_id' => $validated['plan_id'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'ACTIVE',
        ]);
        
        // Record payment
        Payment::create([
            'gym_id' => auth()->user()->gym_id,
            'member_id' => $validated['member_id'],
            'amount' => $plan->price,
            'payment_for' => 'membership',
            'method' => 'CASH', // From form
            'status' => 'PAID',
        ]);
    });
    
    return redirect()->route('members.show', $validated['member_id'])
        ->with('success', 'Membership assigned successfully!');
}
```

**Why Transaction?**
If payment creation fails, membership shouldn't be created (data consistency).

---

## **SPRINT 4: Operations & Reporting**

### **US-010: Record Check-in**

**Laravel Concepts:**
- ✅ Real-time operations (timestamp recording)
- ✅ Validation (member has active membership)
- ✅ Flash feedback (success/error)

**Code Example:**
```php
public function store(Request $request)
{
    $validated = $request->validate([
        'member_id' => 'required|exists:members,id',
    ]);
    
    $member = Member::findOrFail($validated['member_id']);
    
    // Validate active membership
    if (!$member->activeMembership) {
        return back()->withErrors([
            'error' => 'Member has no active membership. Cannot check in.'
        ]);
    }
    
    CheckIn::create([
        'member_id' => $member->id,
        'gym_id' => auth()->user()->gym_id,
        'checked_in_at' => now(),
        'checked_in_by' => auth()->user()->name,
    ]);
    
    return back()->with('success', "Check-in recorded for {$member->full_name}");
}
```

---

### **US-011: Record Payment**

**Laravel Concepts:**
- ✅ Enum handling (method, status)
- ✅ Decimal casting
- ✅ Transaction handling

**Code Example:**
```php
$validated = $request->validate([
    'member_id' => 'required|exists:members,id',
    'amount' => 'required|numeric|min:0.01',
    'payment_for' => 'required|in:membership,registration,personal_training',
    'method' => 'required|in:CASH,TRANSFER,CARD,E_WALLET',
    'notes' => 'nullable|string',
]);

Payment::create([
    ...$validated,
    'gym_id' => auth()->user()->gym_id,
    'status' => 'PAID',
]);
```

---

### **US-012: Monthly Revenue Report**

**Laravel Concepts:**
- ✅ Eloquent aggregates with grouping
- ✅ Date filtering (`whereMonth`, `whereYear`)
- ✅ Collection methods (`groupBy`, `sum`)

**Code Example:**
```php
// Monthly revenue breakdown
$monthlyRevenue = Payment::selectRaw('
        DATE_TRUNC(\'month\', created_at) as month,
        SUM(amount) as total,
        COUNT(*) as transaction_count
    ')
    ->where('status', 'PAID')
    ->whereYear('created_at', now()->year)
    ->groupBy('month')
    ->orderBy('month')
    ->get();

// By payment method
$byMethod = Payment::selectRaw('method, SUM(amount) as total')
    ->whereMonth('created_at', now()->month)
    ->where('status', 'PAID')
    ->groupBy('method')
    ->get();
```

---

## 📊 Concept Frequency Map

| Laravel Concept | Frequency | User Stories |
|----------------|-----------|--------------|
| Eloquent Relationships | 18x | US-002, 005, 006, 008, 009, 010, 011, 012 |
| Soft Deletes | 12x | US-016, 005, 013, 017 |
| Form Validation | 15x | US-003, 005, 007, 008, 010, 011 |
| Authorization (Gates/Policies) | 10x | US-001, 003, 013, 014 |
| Blade Components | 20x | All UI stories |
| Query Optimization | 8x | US-002, 004, 012, 015 |
| Database Transactions | 4x | US-008, 011 |
| Carbon Date Manipulation | 6x | US-008, 009, 012 |

---

## 🎓 Learning Progression

### **Beginner Concepts (Sprint 1)**
- Authentication basics
- Simple queries (count, sum)
- Soft deletes introduction
- Route protection

### **Intermediate Concepts (Sprint 2-3)**
- Complex queries (LIKE, OR, JOIN)
- Policy authorization
- Eager loading (N+1 prevention)
- Date calculations
- Transactions

### **Advanced Concepts (Sprint 4)**
- Aggregate grouping
- Performance optimization
- Report generation
- Data export
- Background jobs (future)

---

**Navigation:**
- ← [Executive Summary](executive-summary.md)
- → [Learning Objectives](learning-objectives.md)
- ↑ [Back to Main README](../README.md)
