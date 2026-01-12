<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    /** @use HasFactory<\Database\Factories\MemberFactory> */
    use HasFactory;

    protected $fillable = [
        'member_id',
        'full_name',
        'phone',
        'gender',
        'date_of_birth',
        'status',
        'gym_id',
        'created_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    public function checkIns()
    {
        return $this->hasMany(CheckIn::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted()
    {
        static::created(function (Member $member) {
            // Automatically create an initial membership plan or other setup if needed
            if (empty($member->member_id)) {
                $member->member_id = 'MBR-' . str_pad($member->id, 4, '0', STR_PAD_LEFT);
                $member->saveQuietly();
            }
        });
    }
}
