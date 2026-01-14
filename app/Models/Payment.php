<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'gym_id',
        'member_id',
        'amount',
        'payment_for',
        'method',
        'status',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function member() { return $this->belongsTo(Member::class); }

    public function gym() { return $this->belongsTo(Gym::class); }

}
