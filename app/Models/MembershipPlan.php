<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipPlan extends Model
{
    /** @use HasFactory<\Database\Factories\MembershipPlanFactory> */
    use HasFactory;

    protected $fillable = ['name', 'duration_days', 'price', 'is_active'];
    
    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }
}
