<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipPlan extends Model
{
    /** @use HasFactory<\Database\Factories\MembershipPlanFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name', 'duration_days', 'price', 'is_active', 'gym_id', 'description'];
    
    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'duration_days' => 'integer',
    ];

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }
}
