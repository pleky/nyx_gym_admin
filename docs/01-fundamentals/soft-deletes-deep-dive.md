# Soft Deletes Deep Dive - Complete Guide

> Why soft deletes are critical for the Gym Management System and how to implement them correctly in Laravel.

---

## 📖 Table of Contents

1. [What is Soft Delete?](#what-is-soft-delete)
2. [Hard Delete vs Soft Delete](#hard-delete-vs-soft-delete)
3. [Why Gym Management Needs Soft Deletes](#why-gym-management-needs-soft-deletes)
4. [How Laravel Implements Soft Deletes](#how-laravel-implements-soft-deletes)
5. [Performance Considerations](#performance-considerations)
6. [Best Practices](#best-practices)
7. [Common Pitfalls](#common-pitfalls)

---

## 🎯 What is Soft Delete?

**Soft Delete** = Mark record as "deleted" without actually removing it from database.

### **Mechanism:**
- Add `deleted_at` timestamp column to table
- When "deleting": Set `deleted_at = NOW()`
- When querying: Automatically filter `WHERE deleted_at IS NULL`
- Record stays in database, just hidden from normal queries

### **Comparison:**

```sql
-- HARD DELETE (permanent)
DELETE FROM members WHERE id = 1;
-- Record GONE forever ❌

-- SOFT DELETE (reversible)
UPDATE members SET deleted_at = '2026-01-12 10:30:00' WHERE id = 1;
-- Record still exists, just marked as deleted ✅
```

---

## ⚖️ Hard Delete vs Soft Delete

### **Hard Delete (Traditional)**

**How it works:**
```php
Member::find(1)->forceDelete(); // Permanent deletion
```

**Advantages:**
- ✅ Smaller database size (no "dead" records)
- ✅ Simpler queries (no deleted_at filtering)
- ✅ Faster writes (no index maintenance)

**Disadvantages:**
- ❌ **Data loss** - Cannot recover accidentally deleted records
- ❌ **Broken audit trail** - No history of what happened
- ❌ **Foreign key nightmares** - Related data becomes orphaned
- ❌ **Reporting gaps** - Historical reports incomplete
- ❌ **Compliance issues** - Violates data retention laws

---

### **Soft Delete (Modern Approach)**

**How it works:**
```php
Member::find(1)->delete(); // Soft delete (reversible)
```

**Advantages:**
- ✅ **Data preservation** - Nothing truly lost
- ✅ **Undo capability** - Restore with one click
- ✅ **Complete audit trail** - Track deletions
- ✅ **Accurate reporting** - Include deleted data in analytics
- ✅ **Legal compliance** - Meet retention requirements
- ✅ **Foreign key safety** - Relationships intact

**Disadvantages:**
- ❌ Slightly larger database (deleted records retained)
- ❌ Extra WHERE clause in every query (performance cost)
- ❌ Must maintain index on `deleted_at`

**Verdict for Gym System:** Soft delete advantages FAR outweigh disadvantages! ✅

---

## 🏋️ Why Gym Management Needs Soft Deletes

### **Real-World Scenario 1: Member Churn & Return**

```
Timeline:
Jan 2025: John joins gym → Member ID: MBR-0001
Jun 2025: John cancels (moving to another city)
Dec 2025: John returns (moved back)
```

**With Hard Delete:**
```php
// June: John cancels
Member::find(1)->forceDelete(); 
// ❌ All history GONE: past payments, check-ins, membership records

// December: John returns
Member::create(['name' => 'John Doe', 'phone' => '...']);
// ❌ New Member ID: MBR-5432
// ❌ Staff doesn't know John was previous member
// ❌ Lost opportunity for "Welcome back!" experience
```

**With Soft Delete:**
```php
// June: John cancels
Member::find(1)->delete(); 
// ✅ History preserved with deleted_at timestamp

// December: Check if phone exists (including deleted)
$existing = Member::withTrashed()->where('phone', $phone)->first();

if ($existing && $existing->trashed()) {
    $existing->restore(); // ✅ Restore MBR-0001
    // ✅ All history intact: payments, check-ins
    // ✅ Staff sees: "Welcome back, John! You were last active in June"
}
```

**Business Impact:**
- Better customer experience (personalization)
- Resume old membership benefits/discounts
- Analyze churn & return patterns for retention strategies

---

### **Real-World Scenario 2: Financial Audit Trail**

**Business Question:** "What was total revenue in Q1 2025?"

**Database Schema:**
```
members (1) ──< payments (many)
```

**With Hard Delete + CASCADE:**
```php
// Member deleted in April
Member::find(123)->forceDelete();
// Foreign key CASCADE: payments also deleted! ❌

// Revenue query
Payment::whereMonth('created_at', 1)
       ->sum('amount');
// Result: INCOMPLETE (deleted members' payments missing)
// ❌ Audit FAIL
// ❌ Tax compliance FAIL
```

**With Soft Delete:**
```php
// Member deleted in April
Member::find(123)->delete(); // Soft delete
// Payments remain intact ✅

// Revenue query
Payment::whereMonth('created_at', 1)
       ->sum('amount');
// Result: COMPLETE (all payments preserved)
// ✅ Accurate financial reporting
// ✅ Tax audit ready (7-10 year retention)
```

**Legal Requirement (Indonesia):**
> "Wajib menyimpan bukti transaksi minimal 10 tahun" - Undang-Undang Perpajakan

Soft delete ensures compliance! ✅

---

### **Real-World Scenario 3: Staff Mistakes**

**Situation:** Staff accidentally deletes wrong member

**With Hard Delete:**
```
Staff: "I accidentally deleted Member #123 instead of #124!"
Manager: "Sorry, data is gone forever. Re-enter everything manually."
Result: Lost 2 hours + angry customer + data gaps
```

**With Soft Delete:**
```
Staff: "I accidentally deleted Member #123!"
Manager: "No problem, just restore it."
Result: 1-click restore, 10 seconds, crisis averted ✅
```

---

## 🔧 How Laravel Implements Soft Deletes

### **Step 1: Migration - Add `deleted_at` Column**

```php
// Migration
public function up()
{
    Schema::table('members', function (Blueprint $table) {
        $table->softDeletes(); // Adds: deleted_at TIMESTAMP NULL
    });
}

public function down()
{
    Schema::table('members', function (Blueprint $table) {
        $table->dropSoftDeletes(); // Removes deleted_at column
    });
}
```

**What `softDeletes()` creates:**
```sql
ALTER TABLE members ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL;
```

---

### **Step 2: Model - Add `SoftDeletes` Trait**

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use SoftDeletes; // Enable soft delete behavior
    
    protected $fillable = ['full_name', 'phone', 'email'];
}
```

**What the trait does:**
- Adds `deleted_at` to model's `$dates` array (auto-cast to Carbon)
- Adds global scope: `WHERE deleted_at IS NULL` to ALL queries
- Provides methods: `restore()`, `forceDelete()`, `trashed()`

---

### **Step 3: Usage - CRUD Operations**

#### **Create (No change)**
```php
$member = Member::create([
    'full_name' => 'John Doe',
    'phone' => '+6281234567890',
]);
// deleted_at = NULL (active)
```

#### **Read (Auto-filtered)**
```php
// Default: Only NON-deleted records
Member::all(); 
// SQL: SELECT * FROM members WHERE deleted_at IS NULL

// Include deleted records
Member::withTrashed()->get();
// SQL: SELECT * FROM members (no WHERE clause)

// ONLY deleted records
Member::onlyTrashed()->get();
// SQL: SELECT * FROM members WHERE deleted_at IS NOT NULL
```

#### **Update (No change)**
```php
$member->update(['phone' => '+6289999999999']);
// Works only if NOT deleted (WHERE deleted_at IS NULL)
```

#### **Delete (Soft)**
```php
$member->delete(); // Soft delete (default)
// SQL: UPDATE members SET deleted_at = NOW() WHERE id = 1

// Check if deleted
$member->trashed(); // true

// Restore
$member->restore();
// SQL: UPDATE members SET deleted_at = NULL WHERE id = 1

// Permanent delete (use with caution!)
$member->forceDelete();
// SQL: DELETE FROM members WHERE id = 1
```

---

### **Step 4: Query Scopes**

```php
// Active members only (default behavior)
$active = Member::where('status', 'ACTIVE')->get();
// WHERE status = 'ACTIVE' AND deleted_at IS NULL

// Search including deleted
$all = Member::withTrashed()
    ->where('phone', 'LIKE', '%1234%')
    ->get();

// Recently deleted (last 30 days)
$recent = Member::onlyTrashed()
    ->where('deleted_at', '>=', now()->subDays(30))
    ->get();

// Count deleted members
$deletedCount = Member::onlyTrashed()->count();
```

---

## ⚡ Performance Considerations

### **Problem: Every Query Filters `deleted_at`**

```php
Member::all();
// SQL: SELECT * FROM members WHERE deleted_at IS NULL
```

For 50,000 members (30% deleted over 5 years):
- Without index: Scan ALL 50,000 rows
- With index: Scan only 35,000 active rows (using B-Tree)

### **Solution: Index `deleted_at`**

```php
// Migration
$table->index('deleted_at');
```

```sql
CREATE INDEX members_deleted_at_index ON members (deleted_at);
```

**Performance Impact:**

| Members | Deleted | Without Index | With Index | Improvement |
|---------|---------|---------------|-----------|-------------|
| 10,000 | 3,000 | 85ms | 6ms | 14x faster |
| 50,000 | 15,000 | 420ms | 28ms | 15x faster |
| 100,000 | 30,000 | 1,200ms | 65ms | 18x faster |

**Index size:** ~500 KB for 50,000 rows (tiny!)

**Detailed Explanation:** [database-indexes.md](database-indexes.md)

---

### **Composite Index for Common Queries**

If you frequently query:
```php
Member::where('status', 'ACTIVE')->get();
```

Create composite index:
```php
$table->index(['status', 'deleted_at']);
```

Benefits:
- Single index covers both filters
- Faster than 2 separate indexes
- Enables index-only scans (no table access)

---

## ✅ Best Practices

### **1. Consistent Soft Delete Policy**

```php
// ✅ GOOD: All interconnected tables use soft deletes
class Member extends Model { use SoftDeletes; }
class Membership extends Model { use SoftDeletes; }
class Payment extends Model { use SoftDeletes; }
class CheckIn extends Model { use SoftDeletes; }

// ❌ BAD: Mixed strategy
class Member extends Model { use SoftDeletes; }
class Payment extends Model { } // No soft delete!
// Problem: When member deleted, payment relationship breaks!
```

---

### **2. Cascade Soft Delete**

```php
class Member extends Model
{
    use SoftDeletes;
    
    protected static function booted()
    {
        static::deleting(function ($member) {
            // When member deleted, also delete related records
            $member->memberships()->delete(); // Soft delete
            $member->payments()->delete();
            $member->checkIns()->delete();
        });
        
        static::restoring(function ($member) {
            // When member restored, restore related records
            $member->memberships()->restore();
            $member->payments()->restore();
            $member->checkIns()->restore();
        });
    }
}
```

---

### **3. Prevent Accidental Force Delete**

```php
class Member extends Model
{
    use SoftDeletes;
    
    public function forceDelete()
    {
        // Disable force delete in production
        if (app()->environment('production')) {
            throw new \Exception(
                'Permanent deletion not allowed in production! Use soft delete instead.'
            );
        }
        
        return parent::forceDelete();
    }
}
```

---

### **4. Cleanup Old Soft Deletes (GDPR Compliance)**

```php
// Scheduled command (app/Console/Kernel.php)
$schedule->call(function () {
    // Permanently delete members soft-deleted > 10 years ago
    Member::onlyTrashed()
        ->where('deleted_at', '<', now()->subYears(10))
        ->forceDelete();
})->monthly();
```

**GDPR "Right to be Forgotten":**
```php
// Step 1: Soft delete (immediate removal from UI)
$member->delete();

// Step 2: Anonymize after retention period (2 years)
Member::onlyTrashed()
    ->where('deleted_at', '<', now()->subYears(2))
    ->each(function ($member) {
        $member->update([
            'full_name' => 'ANONYMIZED',
            'email' => null,
            'phone' => 'REDACTED',
        ]);
    });

// Step 3: Hard delete after 10 years (tax compliance met)
Member::onlyTrashed()
    ->where('deleted_at', '<', now()->subYears(10))
    ->forceDelete();
```

---

## ⚠️ Common Pitfalls

### **Pitfall 1: Unique Constraints with Soft Deletes**

**Problem:**
```php
Schema::table('members', function (Blueprint $table) {
    $table->string('phone')->unique(); // ❌ Problem!
});

// Create member
Member::create(['phone' => '123']);

// Delete member
Member::first()->delete();

// Try to create new member with same phone
Member::create(['phone' => '123']); // ❌ Unique constraint violation!
```

**Solution:** Remove unique constraint OR use composite unique:
```php
// Option 1: Remove unique at DB level, validate in application
$table->string('phone'); // No unique constraint

// Validation
'phone' => Rule::unique('members')->whereNull('deleted_at');

// Option 2: Composite unique (phone + deleted_at)
$table->unique(['phone', 'deleted_at']);
// Allows same phone IF deleted_at different
```

---

### **Pitfall 2: Forgetting to Index `deleted_at`**

```php
// Migration
$table->softDeletes(); // ❌ No index!

// Result: SLOW queries on large tables

// Fix:
$table->softDeletes();
$table->index('deleted_at'); // ✅ Add index
```

---

### **Pitfall 3: Using `find()` on Deleted Records**

```php
$member = Member::find(1); // Returns NULL if soft-deleted!

// Fix:
$member = Member::withTrashed()->find(1); // Finds even if deleted
```

---

### **Pitfall 4: Foreign Key CASCADE with Soft Deletes**

**Bad Schema:**
```sql
ALTER TABLE payments 
ADD FOREIGN KEY (member_id) 
REFERENCES members(id) 
ON DELETE CASCADE; -- ❌ Dangerous with soft deletes!
```

If you accidentally `forceDelete()` a member, CASCADE will hard delete all payments!

**Good Schema:**
```sql
ALTER TABLE payments 
ADD FOREIGN KEY (member_id) 
REFERENCES members(id) 
ON DELETE RESTRICT; -- ✅ Prevent accidental hard deletes
```

Then handle deletion in application layer with soft deletes.

---

## 🧪 Testing Soft Deletes

```php
// Feature test
public function test_member_can_be_soft_deleted()
{
    $member = Member::factory()->create();
    
    $member->delete();
    
    // Assert soft deleted
    $this->assertSoftDeleted('members', ['id' => $member->id]);
    
    // Assert not in default query
    $this->assertDatabaseMissing('members', [
        'id' => $member->id,
        'deleted_at' => null,
    ]);
    
    // Assert can be restored
    $member->restore();
    $this->assertDatabaseHas('members', [
        'id' => $member->id,
        'deleted_at' => null,
    ]);
}
```

---

## 📚 Further Reading

- [Laravel Soft Deleting Docs](https://laravel.com/docs/12.x/eloquent#soft-deleting)
- [Database Indexes Explained](database-indexes.md)
- [Multi-Tenancy Concept](../02-phase-1-foundation/multi-tenancy-concept.md)
- [Phase 1: Gym Infrastructure](../02-phase-1-foundation/gym-infrastructure.md)

---

**Navigation:**
- ← [Fundamentals Index](README.md)
- → [Database Indexes](database-indexes.md)
- ↑ [Main Documentation](../README.md)
