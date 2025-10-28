<?php

namespace App\Policies;

use App\Models\DriveItem;
use App\Models\User;

class DriveItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DriveItem $driveItem): bool
    {
        return $driveItem->drive->hasMember($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Checked in controller via drive access
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DriveItem $driveItem): bool
    {
        $drive = $driveItem->drive;
        $role = $drive->getUserRole($user);
        
        // Viewers can't edit
        if ($role === 'viewer') {
            return false;
        }
        
        // Owners, admins, and members can edit
        return in_array($role, ['owner', 'admin', 'member']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DriveItem $driveItem): bool
    {
        $drive = $driveItem->drive;
        $role = $drive->getUserRole($user);
        
        // Viewers can't delete
        if ($role === 'viewer') {
            return false;
        }
        
        // Users can delete their own items, or admins/owners can delete any
        return $driveItem->created_by_id === $user->id || 
               in_array($role, ['owner', 'admin']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DriveItem $driveItem): bool
    {
        return $this->delete($user, $driveItem);
    }
}
