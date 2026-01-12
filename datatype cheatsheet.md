# 🐘 Laravel + PostgreSQL Data Types Cheatsheet (Best Practice)

Gunakan file ini sebagai referensi cepat saat membuat migration Laravel dengan database PostgreSQL.

---

## 🔑 Primary Key

| Laravel        | PostgreSQL |
| -------------- | ---------- |
| `$table->id()` | `BIGINT`   |

---

## 🔗 Foreign Key

```php
$table->foreignId('user_id')->constrained();
```

---

## 🔢 Numbers

| Kebutuhan      | Laravel                          | PostgreSQL         |
| -------------- | -------------------------------- | ------------------ |
| Integer biasa  | `$table->integer()`              | `INTEGER`          |
| Non-negative   | `$table->integer()` + CHECK >= 0 | `INTEGER`          |
| Big number     | `$table->bigInteger()`           | `BIGINT`           |
| Decimal (uang) | `$table->decimal(10,2)`          | `NUMERIC(10,2)`    |
| Float          | `$table->float()`                | `REAL`             |
| Double         | `$table->double()`               | `DOUBLE PRECISION` |

---

## 🔤 String & Text

| Kebutuhan  | Laravel               | PostgreSQL     |
| ---------- | --------------------- | -------------- |
| Short text | `$table->string(100)` | `VARCHAR(100)` |
| Fixed code | `$table->char(3)`     | `CHAR(3)`      |
| Long text  | `$table->text()`      | `TEXT`         |

---

## 🧭 Enum / Status (Recommended Pattern)

❌ Jangan gunakan:

```php
$table->enum('status', ['ACTIVE','INACTIVE']);
```

✅ Gunakan:

```php
$table->string('status', 10);
```

Tambahkan constraint:

```sql
CHECK (status IN ('ACTIVE','INACTIVE'))
```

---

## 🕐 Date & Time

| Laravel                  | PostgreSQL    |
| ------------------------ | ------------- |
| `$table->date()`         | `DATE`        |
| `$table->time()`         | `TIME`        |
| `$table->timestampTz()`  | `TIMESTAMPTZ` |
| `$table->timestampsTz()` | `TIMESTAMPTZ` |

---

## 🔘 Boolean

```php
$table->boolean('is_active')->default(true);
```

---

## 📦 JSON

```php
$table->jsonb('meta');
```

> Gunakan `jsonb` (lebih cepat daripada `json`).

---

## 📍 UUID

```php
$table->uuid('uuid');
```

---

## 🧮 PostgreSQL Array (Advanced)

```sql
ALTER TABLE users ADD COLUMN tags TEXT[];
```

---

## 🗂 Indexing

```php
$table->index('email');
$table->unique('email');
```

---

## 🔐 Database Constraints Examples

```sql
CHECK (gender IN ('M','F','O'))
CHECK (duration_days >= 0)
```

---

## ❌ Hindari di PostgreSQL

| Laravel Type        | Alasan                              |
| ------------------- | ----------------------------------- |
| `enum()`            | Tidak native PostgreSQL             |
| `unsignedInteger()` | PostgreSQL tidak mendukung unsigned |
| `tinyInteger()`     | Tidak ada                           |
| `mediumInteger()`   | Tidak ada                           |

---

## ✅ Template Migration Modern

```php
Schema::create('members', function (Blueprint $table) {
    $table->id();
    $table->string('member_id', 10)->unique();
    $table->string('gender', 1);
    $table->integer('duration_days');
    $table->jsonb('meta')->nullable();
    $table->timestampsTz();
});
```

---

## 📌 Quick Rules

* Gunakan `string` bukan `enum`
* Gunakan `integer + CHECK` bukan `unsigned`
* Gunakan `jsonb`
* Gunakan `timestampsTz`
* Selalu buat constraint penting

---

## 🏁 Recommended Extensions

```sql
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";
```

---

**Author:** Laravel + PostgreSQL Best Practice Guide
**Usage:** Production-ready reference
