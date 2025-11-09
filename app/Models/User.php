<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'currency',
        'timezone',
        'theme',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Get all drives owned by this user
     */
    public function ownedDrives(): HasMany
    {
        return $this->hasMany(Drive::class, 'owner_id');
    }

    /**
     * Get all drives this user has access to (including owned)
     */
    public function drives(): BelongsToMany
    {
        return $this->belongsToMany(Drive::class, 'drive_users')
            ->withPivot('role', 'invited_by_id', 'joined_at')
            ->withTimestamps()
            ->using(DriveUser::class);
    }

    /**
     * Get only shared drives (not personal)
     */
    public function sharedDrives(): BelongsToMany
    {
        return $this->drives()->where('type', 'shared');
    }

    /**
     * Get only personal drives
     */
    public function personalDrives(): BelongsToMany
    {
        return $this->drives()->where('type', 'personal');
    }

    /**
     * Get all drive memberships
     */
    public function driveMemberships(): HasMany
    {
        return $this->hasMany(DriveUser::class);
    }

    /**
     * Get all items created by this user
     */
    public function createdItems(): HasMany
    {
        return $this->hasMany(DriveItem::class, 'created_by_id');
    }

    /**
     * Get all notifications for this user
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadNotificationsCountAttribute(): int
    {
        return $this->notifications()->where('read', false)->count();
    }

    /**
     * Get or create the user's personal drive
     */
    public function getPersonalDrive(): Drive
    {
        // Check if personal drive exists
        $drive = $this->ownedDrives()
            ->where('type', 'personal')
            ->first();

        if (!$drive) {
            // Create personal drive if it doesn't exist
            $drive = Drive::create([
                'name' => $this->name . "'s Drive",
                'type' => 'personal',
                'owner_id' => $this->id,
            ]);

            // Add user as owner
            $drive->users()->attach($this->id, [
                'role' => 'owner',
                'joined_at' => now(),
            ]);
        }

        return $drive;
    }
}
