<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'status',
        'gym_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'string',
            'status' => 'string',
        ];
    }

    // check if user is owner
    public function isOwner(): bool
    {
        return $this->role === 'OWNER';
    }

    // check if user is staff
    public function isStaff(): bool
    {
        return $this->role === 'STAFF';
    }

    // check if user is active
    public function isActive(): bool
    {
        return $this->status === 'ACTIVE';
    }

    // scoope query to only include owners
    public function scopeOwners($query)
    {
        return $query->where('role', 'OWNER');
    }

    // scoope query to only include staffs
    public function scopeStaffs($query)
    {
        return $query->where('role', 'STAFF');
    }

    public function checkIns()
    {
        return $this->hasMany(CheckIn::class);
    }

    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    public function createdMembers()
    {
        return $this->hasMany(Member::class, 'created_by');
    }
}
