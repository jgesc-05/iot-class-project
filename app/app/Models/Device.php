<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'device_id',
        'type',
        'measurement',
        'unit',
        'api_key_hash',
        'status',
        'sample_interval_s',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sample_interval_s' => 'integer',
    ];

    protected $attributes = [
        'status' => 'active',
        'sample_interval_s' => 15,
    ];

    protected $hidden = [
        'api_key_hash',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function alertRules(): HasMany
    {
        return $this->hasMany(AlertRule::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function commands(): HasMany
    {
        return $this->hasMany(Command::class);
    }
}
