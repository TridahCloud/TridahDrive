<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Drive extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'owner_id',
        'color',
        'icon',
        'settings',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    /**
     * Get the owner of the drive
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get all users who have access to this drive
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'drive_users')
            ->withPivot('role', 'invited_by_id', 'joined_at')
            ->withTimestamps()
            ->using(DriveUser::class);
    }

    /**
     * Get all memberships (drive_users pivot records)
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(DriveUser::class);
    }

    /**
     * Get all items in this drive
     */
    public function items(): HasMany
    {
        return $this->hasMany(DriveItem::class);
    }

    /**
     * Get all tool profiles for this drive
     */
    public function toolProfiles(): HasMany
    {
        return $this->hasMany(ToolProfile::class);
    }

    /**
     * Get invoice profiles for this drive
     */
    public function invoiceProfiles(): HasMany
    {
        return $this->hasMany(InvoiceProfile::class);
    }

    /**
     * Get invoices for this drive
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get clients for this drive
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Get user items for this drive
     */
    public function userItems(): HasMany
    {
        return $this->hasMany(UserItem::class);
    }

    /**
     * Get accounts for this drive (BookKeeper)
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Get categories for this drive (BookKeeper)
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Get transactions for this drive (BookKeeper)
     */
    public function bookTransactions(): HasMany
    {
        return $this->hasMany(BookTransaction::class);
    }

    /**
     * Get departments for this drive (BookKeeper)
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /**
     * Get projects for this drive (Project Board)
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get task labels for this drive (Project Board)
     */
    public function taskLabels(): HasMany
    {
        return $this->hasMany(TaskLabel::class);
    }

    /**
     * Get the default invoice profile
     */
    public function getDefaultInvoiceProfileAttribute(): ?InvoiceProfile
    {
        return $this->invoiceProfiles()->where('is_default', true)->first() 
            ?? $this->invoiceProfiles()->first();
    }

    /**
     * Check if a user is a member of this drive
     */
    public function hasMember(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Get the user's role in this drive
     */
    public function getUserRole(User $user): ?string
    {
        $membership = $this->memberships()->where('user_id', $user->id)->first();
        return $membership?->role;
    }

    /**
     * Check if user has permission (owner or admin)
     */
    public function isOwnerOrAdmin(User $user): bool
    {
        $role = $this->getUserRole($user);
        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Check if user can edit
     */
    public function canEdit(User $user): bool
    {
        $role = $this->getUserRole($user);
        return in_array($role, ['owner', 'admin', 'member']);
    }
}
