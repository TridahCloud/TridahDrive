<?php

use App\Models\Project;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Authorize project channels - users must have access to the drive that contains the project
// Note: Laravel Echo strips 'private-' prefix automatically, so 'private-project.1' becomes 'project.1'
Broadcast::channel('project.{projectId}', function ($user, $projectId) {
    \Log::info('Broadcast channel authorization attempt', [
        'projectId' => $projectId,
        'userId' => $user->id ?? 'null',
        'channel' => 'project.' . $projectId,
        'raw_projectId' => $projectId
    ]);
    
    try {
        $project = Project::with('drive')->find($projectId);
        
        if (!$project) {
            \Log::warning('Broadcast channel authorization failed: Project not found', [
                'projectId' => $projectId,
                'userId' => $user->id
            ]);
            return false;
        }
        
        if (!$project->drive) {
            \Log::warning('Broadcast channel authorization failed: Project has no drive', [
                'projectId' => $projectId,
                'userId' => $user->id
            ]);
            return false;
        }
        
        // Check if user has access via drive membership
        $hasDriveAccess = $project->drive->hasMember($user);
        
        // Check if user has project-level access (shared to project but not drive member)
        $hasProjectAccess = $project->userCanView($user);
        
        $hasAccess = $hasDriveAccess || $hasProjectAccess;
        
        if (!$hasAccess) {
            \Log::warning('Broadcast channel authorization failed: User has no access', [
                'projectId' => $projectId,
                'driveId' => $project->drive->id,
                'userId' => $user->id,
                'hasDriveAccess' => $hasDriveAccess,
                'hasProjectAccess' => $hasProjectAccess
            ]);
        }
        
        return $hasAccess;
    } catch (\Exception $e) {
        \Log::error('Broadcast channel authorization error', [
            'projectId' => $projectId,
            'userId' => $user->id ?? null,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return false;
    }
});
