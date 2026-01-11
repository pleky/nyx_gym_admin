<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckIn extends Model
{
    /** @use HasFactory<\Database\Factories\CheckInFactory> */
    use HasFactory;

    protected $fillable = [
        'member_id',
        'checkin_at',
        'created_by',
        'notes',
        
    ];

    protected $casts = [
        'checkin_at' => 'datetime',
    ];

    public function member() { return $this->belongsTo(Member::class); }

    public function createdBy() {
        return $this->belongsTo(User::class, 'created_by');
    }
}
