# Step 9: Memberships Table Refactor - Complete Guide

> Remove price_paid, add auto_renew, expand status ENUM for subscription management

---

## 🎯 **Learning Objectives**

By completing this step, you will master:
- ✅ Dropping columns safely (data loss awareness)
- ✅ Adding boolean columns with defaults
- ✅ Modifying CHECK constraints in PostgreSQL via raw SQL
- ✅ Creating composite indexes for query optimization
- ✅ Understanding when to separate concerns (payments → new table)
- ✅ Database constraint management

---

## 📖 **What We're Changing**

### **Before (Current Schema):**
```sql
CREATE TABLE memberships (
    id INT PRIMARY KEY,
    member_id INT NOT NULL,
    membership_plan_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    price_paid DECIMAL(10,2) NOT NULL, -- ← Will be removed
    status VARCHAR CHECK (status IN ('ACTIVE', 'EXPIRED', 'CANCELLED')), -- ← Will be updated
    deleted_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### **After (New Schema):**
```sql
CREATE TABLE memberships (
    id INT PRIMARY KEY,
    member_id INT NOT NULL,
    membership_plan_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    auto_renew BOOLEAN DEFAULT FALSE, -- ← NEW
    status VARCHAR CHECK (status IN ('ACTIVE', 'EXPIRED', 'CANCELLED', 'PENDING_RENEWAL')), -- ← UPDATED
    deleted_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- NEW: Composite index
CREATE INDEX memberships_status_deleted_at_idx ON memberships (status, deleted_at);
```

---

## 💡 **Why These Changes?**

### **1. Why Remove price_paid?**

**The Problem:**
Storing `price_paid` in `memberships` table mixes **subscription data** with **payment data**. This creates limitations:

```
Scenario: Member buys 1-year membership (Rp 1,200,000)
Payment options:
1. Pay full upfront → How to record 1 payment?
2. Pay 3 installments → How to record 3 payments?
3. Pay full, get refund after 6 months → How to record refund?
4. Promotional discount applied → Where to track original vs paid price?

With price_paid in memberships table:
❌ Can only store ONE price value
❌ Cannot track payment method (cash/transfer/card)
❌ Cannot track payment status (pending/paid/refunded)
❌ Cannot support installments
❌ Cannot track partial payments
```

**The Solution:**
Create dedicated `payments` table (Step 12) that supports:
- Multiple payments per membership
- Different payment methods
- Payment status tracking
- Refunds and adjustments
- Full audit trail

**Real-World Example:**
```
Member A: 1-year membership
- Membership record: start_date, end_date, plan_id
- Payment records:
  1. Jan 12: Rp 400,000 (TRANSFER, PAID)
  2. Feb 12: Rp 400,000 (CASH, PAID)
  3. Mar 12: Rp 400,000 (CARD, PENDING)
  
Total flexibility! ✅
```

---

### **2. Why Add auto_renew?**

**Business Need:**
Gyms want automatic subscription renewals to reduce churn and improve cash flow.

**User Flow:**
```
Member signs up for monthly membership (Rp 200,000/month):

auto_renew = FALSE (default):
Day 1-30: Membership ACTIVE
Day 31: Membership → EXPIRED
→ Member must manually renew (visit gym, make new payment)
→ Churn risk: 40% don't renew

auto_renew = TRUE (opt-in):
Day 1-30: Membership ACTIVE
Day 24-30: Status → PENDING_RENEWAL (send reminders)
Day 31: Auto-charge payment method
       → Success: Renew membership (new end_date = +30 days)
       → Failed: Status → EXPIRED, send notification
→ Churn risk: 15% (much lower!)
```

**Technical Implementation:**
```php
// In migration:
$table->boolean('auto_renew')->default(false)->after('end_date');
```

**Why default(false)?**
- **Opt-in is safer**: Members explicitly choose auto-renewal
- **Legal compliance**: Some countries require explicit consent
- **Better UX**: Avoids surprise charges
- **Trust building**: Member controls their subscription

---

### **3. Why Add PENDING_RENEWAL Status?**

**Problem Without It:**
```
Membership lifecycle: ACTIVE → ??? → EXPIRED

What happens in the 7 days before expiration?
- Should we send renewal reminders?
- Should we lock premium features?
- How do we track "about to expire" memberships?
```

**Solution with PENDING_RENEWAL:**
```
Improved lifecycle:
Day 1-23: status = 'ACTIVE' (full access)
Day 24-30 (7 days before end): status = 'PENDING_RENEWAL'
  → Send daily renewal reminder emails/SMS
  → Show renewal banner in app
  → Lock premium features (optional)
  → Highlight in staff dashboard
Day 31+: status = 'EXPIRED' (no access)
```

**Use Cases:**
1. **Marketing Automation**: Trigger email sequence for PENDING_RENEWAL members
2. **Staff Dashboard**: "5 memberships expiring this week" widget
3. **App UX**: Show renewal prompt only to PENDING_RENEWAL members
4. **Feature Gating**: Lock advanced features for PENDING_RENEWAL (soft reminder)

**Business Impact:**
```sql
-- Find members likely to churn this week:
SELECT m.full_name, ms.end_date
FROM members m
JOIN memberships ms ON ms.member_id = m.id
WHERE ms.status = 'PENDING_RENEWAL'
ORDER BY ms.end_date;

-- Marketing can call them proactively!
```

---

## 🔧 **Implementation**

### **Migration File:** `2026_01_12_085349_restructure_memberships_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
           // 1. Drop price_paid column (data loss OK - moving to payments table)
           $table->dropColumn('price_paid');
           
           // 2. Add auto_renew boolean with default false
           $table->boolean('auto_renew')->default(false)->after('end_date');
           
           // 3. Add composite index for performance
           $table->index(['status', 'deleted_at']);
        });

        // 4. Drop old CHECK constraint (if exists)
        DB::statement('ALTER TABLE memberships DROP CONSTRAINT IF EXISTS status_check');

        // 5. Add new CHECK constraint with PENDING_RENEWAL
        DB::statement("ALTER TABLE memberships ADD CONSTRAINT status_check CHECK (status IN ('ACTIVE', 'EXPIRED', 'CANCELLED', 'PENDING_RENEWAL'))");
    }

    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
           // 1. Restore price_paid column (data will be NULL)
           $table->decimal('price_paid', 10, 2)->after('end_date');
           
           // 2. Remove auto_renew
           $table->dropColumn('auto_renew');
           
           // 3. Drop composite index
           $table->dropIndex(['status', 'deleted_at']);
        });

        // 4. Restore old CHECK constraint
        DB::statement('ALTER TABLE memberships DROP CONSTRAINT IF EXISTS status_check');
        DB::statement("ALTER TABLE memberships ADD CONSTRAINT status_check CHECK (status IN ('ACTIVE', 'EXPIRED', 'CANCELLED'))");
    }
};
```

---

## 📚 **Deep Dive: Each Operation**

### **Operation 1: Drop price_paid Column**

```php
$table->dropColumn('price_paid');
```

**Generated SQL:**
```sql
ALTER TABLE memberships DROP COLUMN price_paid;
```

**⚠️ Data Loss Awareness:**
- **Is this reversible?** NO! Data is permanently deleted
- **Can you restore in down()?** You can add column back, but data is GONE
- **Production safety**: In production, you would:
  1. Create `payments` table FIRST (Step 12)
  2. Migrate existing `price_paid` data → `payments` table
  3. Verify all data migrated
  4. THEN drop `price_paid` column

**For this learning project:**
- Fresh database, no production data
- Safe to drop immediately
- Document the learning: "Always migrate data before dropping columns"

---

### **Operation 2: Add auto_renew Boolean**

```php
$table->boolean('auto_renew')->default(false)->after('end_date');
```

**Let's break it down:**

#### `->boolean('auto_renew')`
Creates `BOOLEAN` column in PostgreSQL.

**PostgreSQL Type:**
```sql
ALTER TABLE memberships ADD COLUMN auto_renew BOOLEAN;
```

PostgreSQL stores boolean as:
- `TRUE` / `FALSE` (SQL standard)
- `1` / `0` (numeric)
- `'t'` / `'f'` (character)

All equivalent, PostgreSQL normalizes to TRUE/FALSE.

#### `->default(false)`
Sets default value for new rows.

**What This Means:**
```php
// Existing rows: auto_renew = NULL → becomes FALSE (PostgreSQL converts)
// New rows without auto_renew specified: auto_renew = FALSE

// Test this:
Membership::create([
    'member_id' => 1,
    'membership_plan_id' => 1,
    'start_date' => now(),
    'end_date' => now()->addMonth(),
    // Notice: auto_renew NOT specified
]);

$membership = Membership::first();
echo $membership->auto_renew; // Output: 0 (false)
```

**Why NOT `->default(true)`?**
- Default TRUE = All existing memberships become auto-renew
- Dangerous! Members didn't consent
- Legal issue: Charging without permission
- Better UX: Let members opt-in explicitly

#### `->after('end_date')`
Column placement (cosmetic).

**Visual Organization:**
```
memberships table columns:
... start_date, end_date, auto_renew, status ...
   (logical grouping: dates together, then renewal logic, then status)
```

---

### **Operation 3: Add Composite Index**

```php
$table->index(['status', 'deleted_at']);
```

**Generated SQL:**
```sql
CREATE INDEX memberships_status_deleted_at_index 
ON memberships (status, deleted_at);
```

**Why Composite Index?**

**Query Pattern:**
```php
// Most common query:
Membership::where('status', 'ACTIVE')->get();

// With SoftDeletes, Laravel actually runs:
SELECT * FROM memberships 
WHERE status = 'ACTIVE' 
  AND deleted_at IS NULL;
```

**With Composite Index:**
- Single index covers BOTH filters
- PostgreSQL can use index for status lookup, then filter deleted_at from index
- Faster than using 2 separate indexes

**Performance Comparison:**
```sql
-- Test query:
EXPLAIN ANALYZE 
SELECT * FROM memberships 
WHERE status = 'PENDING_RENEWAL' 
  AND deleted_at IS NULL;

With composite index:
  Index Scan using memberships_status_deleted_at_index
  Execution Time: 0.45 ms

With separate indexes (hypothetical):
  Bitmap Index Scan on status_index
  Bitmap Index Scan on deleted_at_index
  Execution Time: 1.2 ms
```

Composite is ~2.5x faster! ✅

---

### **Operation 4-5: Update CHECK Constraint**

```php
// 4. Drop old constraint
DB::statement('ALTER TABLE memberships DROP CONSTRAINT IF EXISTS status_check');

// 5. Add new constraint
DB::statement("ALTER TABLE memberships ADD CONSTRAINT status_check CHECK (status IN ('ACTIVE', 'EXPIRED', 'CANCELLED', 'PENDING_RENEWAL'))");
```

**Why Use DB::statement() Instead of Schema Builder?**

Laravel's Schema Builder doesn't have native CHECK constraint support:
```php
// ❌ This doesn't exist in Laravel:
$table->check('status IN (...)', 'status_check');

// ✅ Must use raw SQL:
DB::statement("ALTER TABLE ... ADD CONSTRAINT ...");
```

**PostgreSQL vs MySQL:**
| Feature | PostgreSQL | MySQL 5.7 | MySQL 8.0+ |
|---------|------------|-----------|------------|
| CHECK constraints | ✅ Fully supported | ❌ Ignored (no error!) | ✅ Supported |
| ENUM type | ⚠️ Exists but inflexible | ✅ Native ENUM | ✅ Native ENUM |

We use CHECK in PostgreSQL because:
- More flexible than ENUM (can modify without ALTER TYPE)
- Database-level validation (can't insert invalid status)
- Self-documenting (constraint name shows allowed values)

**Why `DROP CONSTRAINT IF EXISTS`?**
- Safe if constraint doesn't exist yet
- Prevents error on fresh migrations
- Idempotent (can run multiple times)

**Testing the Constraint:**
```php
// Try invalid status:
Membership::create([
    'member_id' => 1,
    'membership_plan_id' => 1,
    'start_date' => now(),
    'end_date' => now()->addMonth(),
    'status' => 'INVALID_STATUS', // ❌ Will fail
]);

// Error:
// SQLSTATE[23514]: Check violation
// new row for relation "memberships" violates check constraint "status_check"
```

Database protects data integrity! ✅

---

## 🎓 **Model Updates**

### **Before:**
```php
protected $fillable = [
    'member_id',
    'membership_plan_id',
    'start_date',
    'end_date',
    'status',
    'price_paid', // ← Removed
];

protected $casts = [
    'start_date' => 'date',
    'end_date' => 'date',
    'price_paid' => 'decimal:2', // ← Removed
];
```

### **After:**
```php
protected $fillable = [
    'member_id',
    'membership_plan_id',
    'start_date',
    'end_date',
    'auto_renew', // ← ADDED
    'status',
];

protected $casts = [
    'start_date' => 'date',
    'end_date' => 'date',
    'auto_renew' => 'boolean', // ← ADDED
];
```

**Why Add `auto_renew` to $casts?**

Without casting:
```php
$membership = Membership::create(['auto_renew' => true]);
echo $membership->auto_renew; // Output: "1" (string)
if ($membership->auto_renew) { } // Works, but type inconsistent
```

With casting:
```php
$membership = Membership::create(['auto_renew' => true]);
echo $membership->auto_renew; // Output: 1 (boolean true displayed as 1)
var_dump($membership->auto_renew); // Output: bool(true) ✅
if ($membership->auto_renew === true) { } // Strict comparison works
```

**Benefits:**
- Type safety in PHP
- Consistent boolean behavior
- Better IDE autocomplete
- Clearer intent

---

## 🔄 **Migration Rollback Strategy**

```php
public function down(): void
{
    Schema::table('memberships', function (Blueprint $table) {
        $table->decimal('price_paid', 10, 2)->after('end_date');
        $table->dropColumn('auto_renew');
        $table->dropIndex(['status', 'deleted_at']);
    });

    DB::statement('ALTER TABLE memberships DROP CONSTRAINT IF EXISTS status_check');
    DB::statement("ALTER TABLE memberships ADD CONSTRAINT status_check CHECK (status IN ('ACTIVE', 'EXPIRED', 'CANCELLED'))");
}
```

**Can You Restore price_paid DATA?**
❌ NO! Data is permanently lost after `dropColumn('price_paid')`.

**What `down()` Does:**
- ✅ Restores column structure (decimal(10,2))
- ❌ Cannot restore data values
- ✅ Restores old CHECK constraint
- ✅ Removes auto_renew column
- ✅ Drops composite index

**Production Rollback Strategy:**
If you need reversible migration:
```php
// Option 1: Rename instead of drop
$table->renameColumn('price_paid', 'price_paid_deprecated');

// Option 2: Add new column, keep old
$table->decimal('payment_amount_new')->nullable();
// Migrate data over time, then drop old column later

// Option 3: Backup before migration
DB::statement('CREATE TABLE memberships_backup AS SELECT * FROM memberships');
```

For learning: Accept data loss, focus on concepts ✅

---

## 🧪 **Testing & Verification**

### **1. Verify Columns Changed:**
```bash
php artisan tinker
```

```php
DB::getSchemaBuilder()->getColumnListing('memberships');
// Expected output:
// ['id', 'member_id', 'membership_plan_id', 'start_date', 'end_date', 
//  'auto_renew', 'status', 'deleted_at', 'created_at', 'updated_at']

// Should NOT include 'price_paid'
```

### **2. Verify CHECK Constraint:**
```php
// Test valid status:
Membership::create([
    'member_id' => 1,
    'membership_plan_id' => 1,
    'start_date' => now(),
    'end_date' => now()->addMonth(),
    'status' => 'PENDING_RENEWAL', // ✅ Should work
]);

// Test invalid status:
Membership::create([
    'member_id' => 1,
    'membership_plan_id' => 1,
    'start_date' => now(),
    'end_date' => now()->addMonth(),
    'status' => 'FROZEN', // ❌ Should fail
]);
// Expected: Check constraint violation error
```

### **3. Verify Composite Index:**
```php
DB::select("SELECT indexname, indexdef FROM pg_indexes 
            WHERE tablename = 'memberships'");
            
// Expected output includes:
// memberships_status_deleted_at_index
// CREATE INDEX ... ON memberships USING btree (status, deleted_at)
```

### **4. Test auto_renew Default:**
```php
$membership = Membership::create([
    'member_id' => 1,
    'membership_plan_id' => 1,
    'start_date' => now(),
    'end_date' => now()->addMonth(),
    'status' => 'ACTIVE',
    // Notice: auto_renew NOT specified
]);

echo $membership->auto_renew; // Output: 0 (false) ✅
```

---

## ✅ **What I Learned**

### **Laravel Concepts:**
- ✅ `dropColumn()` is permanent - data loss is irreversible
- ✅ `->default(value)` sets value for existing + new rows
- ✅ `->after('column')` for visual organization (cosmetic)
- ✅ `DB::statement()` required for CHECK constraints
- ✅ Composite indexes via array: `->index(['col1', 'col2'])`

### **Database Concepts:**
- ✅ Separating concerns: Payments ≠ Memberships
- ✅ CHECK constraints enforce data integrity at database level
- ✅ Composite indexes > separate indexes for multi-column queries
- ✅ Index column order matters (leading column must be in WHERE)
- ✅ PostgreSQL boolean type normalization

### **Business Logic:**
- ✅ auto_renew reduces churn (15% vs 40%)
- ✅ PENDING_RENEWAL status enables proactive marketing
- ✅ Default `false` for opt-in compliance
- ✅ Payment flexibility requires separate payments table

### **Mistakes Made & Fixed:**
- ⚠️ **Initially forgot to update Model casts** → auto_renew returned string "1" instead of boolean
  - ✅ Fixed by adding `'auto_renew' => 'boolean'` to $casts
- ⚠️ **Tried to use Schema::check()** → Doesn't exist in Laravel
  - ✅ Fixed by using `DB::statement()` with raw SQL
- ⚠️ **Forgot IF EXISTS in DROP CONSTRAINT** → Migration failed on fresh DB
  - ✅ Fixed by adding `IF EXISTS` clause

---

## 🚀 **Next Steps**

**Steps 10-11:** [Restructure CheckIns Table](checkins-transformation.md)
- Rename table: check_ins → checkins
- Rename column: checkin_at → checked_in_at
- Drop created_by column (self-service check-in concept)
- Drop notes column (simplification)

**Why This Restructuring Matters:**
Aligns with self-service kiosk model where members check themselves in, no staff involvement needed.

---

## 📎 **Related Documentation**

- [Members Table Evolution](members-table-evolution.md) - Step 8 & 8b
- [Soft Deletes Deep Dive](../01-fundamentals/soft-deletes-deep-dive.md)
- [Composite Indexes Guide](../01-fundamentals/composite-indexes.md) ← Create this later
- [CHECK Constraints in PostgreSQL](../01-fundamentals/check-constraints.md) ← Create this later

---
