<?php

namespace App\Services;

use App\Models\Drive;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DriveService
{
    /**
     * Create a new shared drive
     */
    public function createSharedDrive(User $user, array $data): Drive
    {
        return DB::transaction(function () use ($user, $data) {
            $drive = Drive::create([
                'name' => $data['name'],
                'type' => 'shared',
                'owner_id' => $user->id,
                'color' => $data['color'] ?? null,
                'icon' => $data['icon'] ?? null,
                'description' => $data['description'] ?? null,
                'settings' => $data['settings'] ?? [],
            ]);

            // Add creator as owner
            $drive->users()->attach($user->id, [
                'role' => 'owner',
                'joined_at' => now(),
            ]);

            return $drive->fresh(['owner', 'users']);
        });
    }

    /**
     * Invite a user to a drive
     */
    public function inviteUser(Drive $drive, User $inviter, User $user, string $role = 'member'): bool
    {
        // Check permissions
        if (!in_array($drive->getUserRole($inviter), ['owner', 'admin'])) {
            throw new \Exception('You do not have permission to invite users to this drive.');
        }

        // Validate role
        if (!in_array($role, ['admin', 'member', 'viewer'])) {
            throw new \Exception('Invalid role specified.');
        }

        // Check if user is already a member
        if ($drive->hasMember($user)) {
            throw new \Exception('User is already a member of this drive.');
        }

        $drive->users()->attach($user->id, [
            'role' => $role,
            'invited_by_id' => $inviter->id,
            'joined_at' => now(),
        ]);

        // Create notification for the invited user
        Notification::create([
            'user_id' => $user->id,
            'type' => 'drive_invite',
            'title' => 'Drive Invitation',
            'message' => "You have been invited to join \"{$drive->name}\" as {$role} by {$inviter->name}.",
            'data' => [
                'drive_id' => $drive->id,
                'drive_name' => $drive->name,
                'inviter_id' => $inviter->id,
                'inviter_name' => $inviter->name,
                'role' => $role,
            ],
        ]);

        return true;
    }

    /**
     * Update a user's role in a drive
     */
    public function updateUserRole(Drive $drive, User $admin, User $user, string $newRole): bool
    {
        $adminRole = $drive->getUserRole($admin);
        
        // Only owner can change roles
        if ($adminRole !== 'owner') {
            throw new \Exception('Only the drive owner can change user roles.');
        }

        // Cannot change owner's role
        if ($drive->owner_id === $user->id) {
            throw new \Exception('Cannot change the owner\'s role.');
        }

        $drive->users()->updateExistingPivot($user->id, [
            'role' => $newRole,
        ]);

        // Create notification for the user whose role was changed
        Notification::create([
            'user_id' => $user->id,
            'type' => 'drive_role_changed',
            'title' => 'Role Updated',
            'message' => "Your role in \"{$drive->name}\" has been changed to {$newRole} by {$admin->name}.",
            'data' => [
                'drive_id' => $drive->id,
                'drive_name' => $drive->name,
                'admin_id' => $admin->id,
                'admin_name' => $admin->name,
                'new_role' => $newRole,
            ],
        ]);

        return true;
    }

    /**
     * Remove a user from a drive
     */
    public function removeUser(Drive $drive, User $admin, User $userToRemove): bool
    {
        $adminRole = $drive->getUserRole($admin);
        
        // Only owner or admin can remove users
        if (!in_array($adminRole, ['owner', 'admin'])) {
            throw new \Exception('You do not have permission to remove users from this drive.');
        }

        // Cannot remove the owner
        if ($drive->owner_id === $userToRemove->id) {
            throw new \Exception('Cannot remove the drive owner.');
        }

        // Cannot remove yourself if you're the only admin/owner
        if ($admin->id === $userToRemove->id && $adminRole === 'owner') {
            $ownerCount = $drive->memberships()->where('role', 'owner')->count();
            if ($ownerCount === 1) {
                throw new \Exception('Cannot remove yourself as the only owner.');
            }
        }

        $drive->users()->detach($userToRemove->id);

        return true;
    }

    /**
     * Update drive settings
     */
    public function updateDrive(Drive $drive, User $user, array $data): Drive
    {
        if (!$drive->isOwnerOrAdmin($user)) {
            throw new \Exception('You do not have permission to update this drive.');
        }

        $drive->update($data);

        return $drive->fresh();
    }

    /**
     * Delete a drive
     */
    public function deleteDrive(Drive $drive, User $user): bool
    {
        if ($drive->owner_id !== $user->id) {
            throw new \Exception('Only the drive owner can delete the drive.');
        }

        return $drive->delete();
    }

    /**
     * Allow a user to leave a shared drive
     */
    public function leaveDrive(Drive $drive, User $user): bool
    {
        // Cannot leave personal drives
        if ($drive->type === 'personal') {
            throw new \Exception('Cannot leave your personal drive.');
        }

        // Must be a member to leave
        if (!$drive->hasMember($user)) {
            throw new \Exception('You are not a member of this drive.');
        }

        // Cannot leave if you're the owner
        if ($drive->owner_id === $user->id) {
            throw new \Exception('Cannot leave your own drive. Transfer ownership or delete the drive instead.');
        }

        // Remove user from drive
        $drive->users()->detach($user->id);

        return true;
    }
}

