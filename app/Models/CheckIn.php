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
        'checked_in_at',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
    ];

    public function member() { return $this->belongsTo(Member::class); }
}
