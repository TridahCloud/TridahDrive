<?php

namespace App\Policies;

use App\Models\Drive;
use App\Models\User;

class DrivePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view their own drives
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Drive $drive): bool
    {
        // Drive members can always view
        if ($drive->hasMember($user)) {
            return true;
        }
        
        // Check if user has project-level access (shared to projects but not drive member)
        $hasProjectAccess = $drive->projects()
            ->whereHas('users', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->exists();
        
        return $hasProjectAccess;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can create a shared drive
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Drive $drive): bool
    {
        return $drive->isOwnerOrAdmin($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Drive $drive): bool
    {
        return $drive->owner_id === $user->id;
    }

    /**
     * Determine whether the user can invite users.
     */
    public function invite(User $user, Drive $drive): bool
    {
        return $drive->isOwnerOrAdmin($user);
    }

    /**
     * Determine whether the user can manage members.
     */
    public function manageMembers(User $user, Drive $drive): bool
    {
        return $drive->isOwnerOrAdmin($user);
    }
}
