<?php

namespace App\Models;

use App\Models\Drive;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskComment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'task_id',
        'user_id',
        'comment',
        'comment_html',
        'mentioned_users',
        'parent_id',
    ];

    protected $casts = [
        'mentioned_users' => 'array',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(TaskComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TaskComment::class, 'parent_id')->orderBy('created_at');
    }

    /**
     * Parse @username mentions and create notifications
     */
    public static function parseMentions(string $comment, int $taskId, int $driveId): array
    {
        $mentionedUsers = [];
        $mentionedUserIds = [];
        
        // Pattern to match @username (now supports spaces and more characters)
        // Matches @ followed by word characters or spaces (until space or end of string)
        preg_match_all('/@([^\s@]+)/', $comment, $matches);
        
        if (!empty($matches[1])) {
            // Get drive members (including owner)
            $drive = Drive::find($driveId);
            if ($drive) {
                $members = $drive->users()->get();
                
                // Also include the drive owner if they're not already in members
                $owner = $drive->owner;
                if ($owner && !$members->contains('id', $owner->id)) {
                    $members->push($owner);
                }
                
                foreach ($matches[1] as $username) {
                    $username = trim($username);
                    if (empty($username)) {
                        continue;
                    }
                    
                    // Try to find user by name (case-insensitive, partial match for names with spaces)
                    $user = $members->first(function($member) use ($username) {
                        $memberName = strtolower($member->name);
                        $usernameLower = strtolower($username);
                        $memberEmail = strtolower($member->email ?? '');
                        
                        // Exact match or name starts with username
                        return $memberName === $usernameLower || 
                               str_starts_with($memberName, $usernameLower) ||
                               $memberEmail === $usernameLower ||
                               str_starts_with($memberEmail, $usernameLower);
                    });
                    
                    if ($user) {
                        $mentionedUsers[] = $user;
                        $mentionedUserIds[] = $user->id;
                    }
                }
            }
        }
        
        return [
            'users' => $mentionedUsers,
            'user_ids' => array_unique($mentionedUserIds),
        ];
    }

    /**
     * Convert comment to HTML with @username links
     */
    public static function toHtml(string $comment, array $mentionedUsers): string
    {
        $html = htmlspecialchars($comment);
        
        foreach ($mentionedUsers as $user) {
            // Replace @username with linked version
            $pattern = '/@' . preg_quote($user->name, '/') . '/i';
            $replacement = '<a href="#" class="user-mention" data-user-id="' . $user->id . '">@' . htmlspecialchars($user->name) . '</a>';
            $html = preg_replace($pattern, $replacement, $html);
        }
        
        // Convert newlines to <br>
        $html = nl2br($html);
        
        return $html;
    }
}
