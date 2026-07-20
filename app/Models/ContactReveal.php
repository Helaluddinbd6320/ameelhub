<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactReveal extends Model
{
    public $timestamps = false; // শুধু revealed_at আছে, updated_at নেই

    protected $fillable = [
        'user_id',
        'worker_id',
        'amount_charged',
        'phone_type',
        'ip_address',
        'revealed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_charged' => 'decimal:2',
            'revealed_at'    => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }
}