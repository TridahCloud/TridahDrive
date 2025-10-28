<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceProfile extends Model
{
    protected $fillable = [
        'drive_id',
        'name',
        'is_default',
        'company_name',
        'company_address',
        'company_phone',
        'company_email',
        'company_website',
        'logo_url',
        'customizations',
        'invoice_prefix',
        'next_invoice_number',
        'bank_name',
        'bank_account_name',
        'bank_routing_label',
        'bank_routing_number',
        'bank_account_number',
        'accent_color',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'customizations' => 'array',
        'next_invoice_number' => 'integer',
    ];

    /**
     * Get the drive that owns this profile
     */
    public function drive(): BelongsTo
    {
        return $this->belongsTo(Drive::class);
    }

    /**
     * Get all invoices using this profile
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Generate the next invoice number
     */
    public function getNextInvoiceNumber(): string
    {
        $number = $this->next_invoice_number;
        $this->increment('next_invoice_number');
        return $this->invoice_prefix . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}
