<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'user_id',
        'drive_id',
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'notes',
    ];

    /**
     * Get the user that owns this client
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the drive this client belongs to
     */
    public function drive(): BelongsTo
    {
        return $this->belongsTo(Drive::class);
    }

    /**
     * Get all invoices for this client
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get full address as formatted string
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->zip,
            $this->country,
        ]);
        
        return implode(', ', $parts);
    }
}
