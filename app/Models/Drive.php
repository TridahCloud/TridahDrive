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
        'parent_drive_id',
        'color',
        'icon',
        'settings',
        'description',
        'currency',
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
     * Get the parent drive (if this is a sub-drive)
     */
    public function parentDrive(): BelongsTo
    {
        return $this->belongsTo(Drive::class, 'parent_drive_id');
    }

    /**
     * Get all sub-drives of this drive
     */
    public function subDrives(): HasMany
    {
        return $this->hasMany(Drive::class, 'parent_drive_id');
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
     * Get recurring transactions for this drive (BookKeeper)
     */
    public function recurringTransactions(): HasMany
    {
        return $this->hasMany(RecurringTransaction::class);
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

    /**
     * Get all drive IDs including this drive and sub-drives
     */
    public function getDriveIdsIncludingSubDrives(bool $includeHidden = false): array
    {
        $driveIds = [$this->id];

        $subDriveIds = $this->subDrives()
            ->when(!$includeHidden, function($query) {
                $settings = $this->settings ?? [];
                $hiddenSubDrives = $settings['hidden_sub_drives'] ?? [];
                if (!empty($hiddenSubDrives)) {
                    $query->whereNotIn('id', $hiddenSubDrives);
                }
            })
            ->pluck('id')
            ->toArray();

        return array_merge($driveIds, $subDriveIds);
    }

    /**
     * Get all invoices including from sub-drives
     */
    public function getInvoicesIncludingSubDrives(bool $includeHidden = false)
    {
        $driveIds = $this->getDriveIdsIncludingSubDrives($includeHidden);
        return Invoice::whereIn('drive_id', $driveIds);
    }

    /**
     * Get all transactions including from sub-drives
     */
    public function getTransactionsIncludingSubDrives(bool $includeHidden = false)
    {
        $driveIds = $this->getDriveIdsIncludingSubDrives($includeHidden);
        return BookTransaction::whereIn('drive_id', $driveIds);
    }

    /**
     * Get all projects including from sub-drives
     */
    public function getProjectsIncludingSubDrives(bool $includeHidden = false)
    {
        $driveIds = $this->getDriveIdsIncludingSubDrives($includeHidden);
        return Project::whereIn('drive_id', $driveIds);
    }

    /**
     * Check if this is a sub-drive
     */
    public function isSubDrive(): bool
    {
        return !is_null($this->parent_drive_id);
    }

    /**
     * Get a short identifier for sub-drive transaction numbers
     */
    public function getSubDrivePrefix(): ?string
    {
        if (!$this->isSubDrive()) {
            return null;
        }
        
        // Use first 3 uppercase letters of drive name or drive ID as fallback
        $name = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $this->name));
        if (strlen($name) >= 3) {
            return substr($name, 0, 3);
        }
        
        return str_pad($this->id, 3, '0', STR_PAD_LEFT);
    }
}
