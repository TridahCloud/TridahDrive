<?php

namespace App\Http\Controllers;

use App\Models\Drive;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskCommentController extends Controller
{
    /**
     * Store a new comment
     */
    public function store(Request $request, Drive $drive, Project $project, Task $task): RedirectResponse
    {
        $this->authorize('view', $drive);
        
        if ($project->drive_id !== $drive->id || $task->project_id !== $project->id) {
            abort(404);
        }

        $validated = $request->validate([
            'comment' => 'required|string|max:5000',
            'parent_id' => 'nullable|exists:task_comments,id',
        ]);

        // Parse mentions
        $mentions = TaskComment::parseMentions($validated['comment'], $task->id, $drive->id);
        
        // Log for debugging
        \Log::info('Parsed mentions', [
            'comment' => $validated['comment'],
            'mentioned_user_ids' => $mentions['user_ids'],
            'mentioned_users_count' => count($mentions['users']),
            'task_id' => $task->id,
            'drive_id' => $drive->id,
        ]);
        
        // Create HTML version
        $commentHtml = TaskComment::toHtml($validated['comment'], $mentions['users']);

        // Create comment
        $comment = $task->allComments()->create([
            'user_id' => Auth::id(),
            'comment' => $validated['comment'],
            'comment_html' => $commentHtml,
            'mentioned_users' => $mentions['user_ids'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        // Create notifications for mentioned users
        $this->createMentionNotifications($comment, $mentions['users'], $drive, $project, $task);

        $taskUrl = route('drives.projects.projects.tasks.show', [$drive, $project, $task]);

        // Create notification for task owner (if not the commenter)
        if ($task->owner_id && $task->owner_id !== Auth::id() && !in_array($task->owner_id, $mentions['user_ids'])) {
            $this->createNotification(
                $task->owner_id,
                'task_comment',
                'New comment on your task',
                Auth::user()->name . ' commented on task: ' . $task->title,
                [
                    'drive_id' => $drive->id,
                    'project_id' => $project->id,
                    'task_id' => $task->id,
                    'comment_id' => $comment->id,
                    'commenter_id' => Auth::id(),
                    'url' => $taskUrl . '#comment-' . $comment->id,
                ]
            );
        }

        // Create notifications for task members (if not the commenter and not already notified)
        foreach ($task->members as $member) {
            if ($member->id !== Auth::id() && 
                $member->id !== $task->owner_id && 
                !in_array($member->id, $mentions['user_ids'])) {
                $this->createNotification(
                    $member->id,
                    'task_comment',
                    'New comment on assigned task',
                    Auth::user()->name . ' commented on task: ' . $task->title,
                    [
                        'drive_id' => $drive->id,
                        'project_id' => $project->id,
                        'task_id' => $task->id,
                        'comment_id' => $comment->id,
                        'commenter_id' => Auth::id(),
                        'url' => $taskUrl . '#comment-' . $comment->id,
                    ]
                );
            }
        }

        return redirect()->route('drives.projects.projects.tasks.show', [$drive, $project, $task])
            ->with('success', 'Comment added successfully!');
    }

    /**
     * Update a comment
     */
    public function update(Request $request, Drive $drive, Project $project, Task $task, TaskComment $comment): RedirectResponse
    {
        $this->authorize('view', $drive);
        
        if ($project->drive_id !== $drive->id || 
            $task->project_id !== $project->id || 
            $comment->task_id !== $task->id) {
            abort(404);
        }

        // Only the comment author can edit
        if ($comment->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'comment' => 'required|string|max:5000',
        ]);

        // Re-parse mentions (in case mentions changed)
        $mentions = TaskComment::parseMentions($validated['comment'], $task->id, $drive->id);
        $commentHtml = TaskComment::toHtml($validated['comment'], $mentions['users']);

        $comment->update([
            'comment' => $validated['comment'],
            'comment_html' => $commentHtml,
            'mentioned_users' => $mentions['user_ids'],
        ]);

        // Create notifications for newly mentioned users
        $oldMentions = $comment->mentioned_users ?? [];
        $newMentions = array_diff($mentions['user_ids'], $oldMentions);
        
        if (!empty($newMentions)) {
            $newMentionUsers = array_filter($mentions['users'], function($user) use ($newMentions) {
                return in_array($user->id, $newMentions);
            });
            $this->createMentionNotifications($comment, $newMentionUsers, $drive, $project, $task);
        }

        return redirect()->route('drives.projects.projects.tasks.show', [$drive, $project, $task])
            ->with('success', 'Comment updated successfully!');
    }

    /**
     * Delete a comment
     */
    public function destroy(Drive $drive, Project $project, Task $task, TaskComment $comment): RedirectResponse
    {
        $this->authorize('view', $drive);
        
        if ($project->drive_id !== $drive->id || 
            $task->project_id !== $project->id || 
            $comment->task_id !== $task->id) {
            abort(404);
        }

        // Only the comment author or task owner can delete
        if ($comment->user_id !== Auth::id() && $task->owner_id !== Auth::id()) {
            abort(403);
        }

        $comment->delete();

        return redirect()->route('drives.projects.projects.tasks.show', [$drive, $project, $task])
            ->with('success', 'Comment deleted successfully!');
    }

    /**
     * Create notifications for mentioned users
     */
    private function createMentionNotifications(
        TaskComment $comment, 
        array $mentionedUsers, 
        Drive $drive, 
        Project $project, 
        Task $task
    ): void {
        $taskUrl = route('drives.projects.projects.tasks.show', [$drive, $project, $task]);
        
        \Log::info('Creating mention notifications', [
            'mentioned_users_count' => count($mentionedUsers),
            'commenter_id' => Auth::id(),
        ]);
        
        foreach ($mentionedUsers as $user) {
            // Don't notify the commenter themselves
            if ($user->id === Auth::id()) {
                \Log::info('Skipping notification for commenter themselves', ['user_id' => $user->id]);
                continue;
            }

            \Log::info('Creating notification for mentioned user', [
                'user_id' => $user->id,
                'user_name' => $user->name,
            ]);

            $this->createNotification(
                $user->id,
                'task_mention',
                'You were mentioned in a task comment',
                Auth::user()->name . ' mentioned you in a comment on task: ' . $task->title,
                [
                    'drive_id' => $drive->id,
                    'project_id' => $project->id,
                    'task_id' => $task->id,
                    'comment_id' => $comment->id,
                    'commenter_id' => Auth::id(),
                    'url' => $taskUrl . '#comment-' . $comment->id,
                ]
            );
        }
    }

    /**
     * Create a notification
     */
    private function createNotification(
        int $userId,
        string $type,
        string $title,
        string $message,
        array $data = []
    ): void {
        // Check if notification already exists (prevent duplicates)
        $existing = Notification::where('user_id', $userId)
            ->where('type', $type)
            ->where('read', false)
            ->whereRaw('JSON_EXTRACT(data, "$.task_id") = ?', [$data['task_id'] ?? null])
            ->whereRaw('JSON_EXTRACT(data, "$.comment_id") = ?', [$data['comment_id'] ?? null])
            ->where('created_at', '>', now()->subMinutes(5))
            ->first();
        
        if ($existing) {
            // Update existing notification instead of creating duplicate
            $existing->update([
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'created_at' => now(),
            ]);
            return;
        }
        
        try {
            $notification = Notification::create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'read' => false,
            ]);
            
            \Log::info('Notification created', [
                'notification_id' => $notification->id,
                'user_id' => $userId,
                'type' => $type,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to create notification', [
                'user_id' => $userId,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
            // Don't throw - we don't want to break comment creation
        }
    }
}
