<?php

namespace App\Policies;

use App\Models\ToolProfile;
use App\Models\User;

class ToolProfilePolicy
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
    public function view(User $user, ToolProfile $toolProfile): bool
    {
        return $toolProfile->drive->hasMember($user);
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
    public function update(User $user, ToolProfile $toolProfile): bool
    {
        return $toolProfile->drive->isOwnerOrAdmin($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ToolProfile $toolProfile): bool
    {
        return $toolProfile->drive->isOwnerOrAdmin($user);
    }
}
