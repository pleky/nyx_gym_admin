<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gym extends Model
{
    /** @use HasFactory<\Database\Factories\GymFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'phone'
    ];

    // Define relationships to users
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Define relationships to members
    public function members()
    {
        return $this->hasMany(Member::class);
    }

    // Define relationships to membership plans
    public function membershipPlans()
    {
        return $this->hasMany(MembershipPlan::class);
    }

    // Define relationships to check-ins
    public function checkIns()
    {
        return $this->hasMany(CheckIn::class);
    }

    // Define relationships to payments
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
