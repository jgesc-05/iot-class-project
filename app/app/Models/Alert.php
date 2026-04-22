<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'alert_rule_id',
        'device_id',
        'triggered_at',
        'value',
        'resolved_at',
    ];

    protected $casts = [
        'triggered_at' => 'datetime',
        'resolved_at' => 'datetime',
        'value' => 'float',
    ];

    public function alertRule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * True si la alerta sigue activa (no resuelta).
     */
    public function isActive(): bool
    {
        return $this->resolved_at === null;
    }

    /**
     * Marca la alerta como resuelta.
     */
    public function resolve(\DateTimeInterface $at = null): void
    {
        $this->update(['resolved_at' => $at ?? now()]);
    }
}
