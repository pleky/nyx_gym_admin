<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Membership extends Model
{
    /** @use HasFactory<\Database\Factories\MembershipFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'member_id',
        'membership_plan_id',
        'start_date',
        'end_date',
        'status',
        'auto_renew',
        'gym_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'auto_renew' => 'boolean',
        'status' => 'string',
    ];

    public function member() {
        return $this->belongsTo(Member::class);
    }

    public function gym() {
        return $this->belongsTo(Gym::class);
    }

    public function membershipPlan() {
        return $this->belongsTo(MembershipPlan::class, 'membership_plan_id');
    }

}
