
# FASE 1: Gym Infrastructure Setup
----------------------------------------------------

## Step 1: Create Gym Model
File: [Gym.php](/app/Models/Gym.php)

### What I Built:

* Model Dengan *SoftDelete* trait untuk soft delete functionallity
* Fillable Attributes: name, address, phone
* 5 Relatioships: *hasMany* ke User, Member, MembershipPlan, Checkin, Payment

### What I Learned:

* *SoftDelete* trait menambahkan column `delete_at` behaviour
* `HasFactory` trait untuk integration dengan factory pattern
* Model Relatioships menggunakan `hasMany()` method
* Convetion: relationship method name camelCase, tapi table names snake_case

```php
<?php
class Gym extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = ['name', 'address', 'phone'];
    
    public function members()
    {
        return $this->hasMany(Member::class);
    }
}
```

## Step 2: Create GymFactory 
File: [GymFactory.php](/database/factories/GymFactory.php)

### What I Build:

* Factory Untuk generate fake gym data menggunakan *Faker* Library
* `$this->Faker` access ke Faker Method
* Factory dapat di-chain: Gym::Factory()->create()
* Separation: factory untuk structure, seeder untuk spesific data

```php
<?php
public function definition(): array
{
    return [
        'name' => $this->faker->company(),
        'address' => $this->faker->address(),
        'phone' => $this->faker->phoneNumber(),
    ];
}
```

## Step 3: Create Gyms Table Migration
File: [](/database/migrations/2026_01_12_031310_create_gyms_table.php)

### What I Built:

* Migration untuk membuat table gym
* Columns: `id`, `name`, `address`, `delete_at`, `timestamps`
* Index pada `deleted_at` untuk soft delete query optimization

### Key Concept - Why Index on `deleted_at`

1. Bagaimana Soft Delete Bekerja di Laravel
Tanpa soft Deletes (Hard Delete);