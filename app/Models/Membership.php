<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    /** @use HasFactory<\Database\Factories\MembershipFactory> */
    use HasFactory;

    protected $fillable = [
        'member_id',
        'membership_plan_id',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function member() {
        return $this->belongsTo(Member::class);
    }

    public function plan() {
        return $this->belongsTo(MembershipPlan::class);
    }
}
