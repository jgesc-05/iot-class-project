<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Command extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'type',
        'payload',
        'status',
        'acked_at',
        'result',
    ];

    protected $casts = [
        'payload' => 'array',
        'result' => 'array',
        'acked_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function markExecuted(?array $result = null): void
    {
        $this->update([
            'status' => 'executed',
            'acked_at' => now(),
            'result' => $result,
        ]);
    }

    public function markFailed(string $reason): void
    {
        $this->update([
            'status' => 'failed',
            'acked_at' => now(),
            'result' => ['error' => $reason],
        ]);
    }
}
