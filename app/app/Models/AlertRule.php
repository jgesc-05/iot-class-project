<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AlertRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'measurement',
        'min_threshold',
        'max_threshold',
        'enabled',
    ];

    protected $casts = [
        'min_threshold' => 'float',
        'max_threshold' => 'float',
        'enabled' => 'boolean',
    ];

    protected $attributes = [
        'enabled' => true,
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function isOutOfRange(float $value): bool
    {
        if ($this->min_threshold !== null && $value < $this->min_threshold) {
            return true;
        }
        if ($this->max_threshold !== null && $value > $this->max_threshold) {
            return true;
        }
        return false;
    }
}
