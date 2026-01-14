<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CheckIn extends Model
{
    /** @use HasFactory<\Database\Factories\CheckInFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $table = 'checkins';

    protected $fillable = [
        'member_id',
        'checked_in_at',
        'checked_in_by',
        'gym_id',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
    ];

    public function gym() { return $this->belongsTo(Gym::class); }

    public function member() { return $this->belongsTo(Member::class); }

}
