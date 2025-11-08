@extends('layouts.dashboard')

@section('title', $task->title . ' - ' . $drive->name)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('drives.index') }}">Drives</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('drives.show', $drive) }}">{{ $drive->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('drives.projects.projects.index', $drive) }}">Projects</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('drives.projects.projects.show', [$drive, $project]) }}">{{ $project->name }}</a></li>
                    <li class="breadcrumb-item active">{{ $task->title }}</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="display-6 mb-0 brand-teal">{{ $task->title }}</h1>
                    <p class="text-muted">{{ $project->name }}</p>
                </div>
                <div class="d-flex gap-2">
                    @if($drive->canEdit(auth()->user()))
                        <a href="{{ route('drives.projects.projects.tasks.edit', [$drive, $project, $task]) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Edit
                        </a>
                    @endif
                    <a href="{{ route('drives.projects.projects.show', [$drive, $project]) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <!-- Task Header Image -->
            @if($task->attachments->where('type', 'header')->count() > 0)
                <div class="dashboard-card mb-4">
                    @foreach($task->attachments->where('type', 'header') as $headerImage)
                        <img src="{{ route('drives.projects.projects.tasks.attachments.show', [$drive, $project, $task, $headerImage]) }}" 
                             alt="Task header" 
                             class="img-fluid rounded"
                             style="max-height: 300px; width: 100%; object-fit: cover;">
                    @endforeach
                </div>
            @endif

            <!-- Task Details -->
            <div class="dashboard-card mb-4">
                <h5 class="mb-3" style="color: var(--text-color);">Description</h5>
                @if($task->description)
                    <p class="mb-0" style="color: var(--text-color);">{{ nl2br(e($task->description)) }}</p>
                @else
                    <p class="text-muted mb-0">No description provided.</p>
                @endif
            </div>

            <!-- Subtasks -->
            @if($task->subtasks->count() > 0)
                <div class="dashboard-card mb-4">
                    <h5 class="mb-3" style="color: var(--text-color);">Subtasks</h5>
                    <div class="list-group">
                        @foreach($task->subtasks as $subtask)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <a href="{{ route('drives.projects.projects.tasks.show', [$drive, $project, $subtask]) }}" class="text-decoration-none" style="color: var(--text-color);">
                                        {{ $subtask->title }}
                                    </a>
                                    @if($subtask->status)
                                        <span class="badge ms-2" style="background-color: {{ $subtask->status->color }}; color: #fff;">
                                            {{ $subtask->status->name }}
                                        </span>
                                    @endif
                                </div>
                                <span class="badge bg-{{ $subtask->priority === 'urgent' ? 'danger' : ($subtask->priority === 'high' ? 'warning' : ($subtask->priority === 'medium' ? 'info' : 'secondary')) }}">
                                    {{ ucfirst($subtask->priority) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Attachments -->
            @if($task->attachments->where('type', 'attachment')->count() > 0)
                <div class="dashboard-card mb-4">
                    <h5 class="mb-3" style="color: var(--text-color);">Attachments</h5>
                    <div class="row">
                        @foreach($task->attachments->where('type', 'attachment') as $attachment)
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    @if(str_starts_with($attachment->mime_type, 'image/'))
                                        <a href="{{ route('drives.projects.projects.tasks.attachments.show', [$drive, $project, $task, $attachment]) }}" target="_blank">
                                            <img src="{{ route('drives.projects.projects.tasks.attachments.show', [$drive, $project, $task, $attachment]) }}" 
                                                 alt="{{ $attachment->original_filename }}" 
                                                 class="card-img-top" 
                                                 style="max-height: 200px; object-fit: cover;">
                                        </a>
                                    @endif
                                    <div class="card-body">
                                        @if(!str_starts_with($attachment->mime_type, 'image/'))
                                            <div class="text-center mb-2">
                                                <i class="fas fa-file fa-3x text-muted"></i>
                                            </div>
                                        @endif
                                        <h6 class="card-title text-truncate" title="{{ $attachment->original_filename }}">
                                            {{ $attachment->original_filename }}
                                        </h6>
                                        <p class="text-muted small mb-2">{{ $attachment->human_readable_size }}</p>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('drives.projects.projects.tasks.attachments.show', [$drive, $project, $task, $attachment]) }}" 
                                               target="_blank" 
                                               class="btn btn-sm btn-outline-primary flex-fill">
                                                <i class="fas fa-download me-1"></i>View
                                            </a>
                                            <form action="{{ route('drives.projects.projects.tasks.attachments.destroy', [$drive, $project, $task, $attachment]) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this attachment?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Task Info Sidebar -->
            <div class="dashboard-card mb-4">
                <h5 class="mb-3" style="color: var(--text-color);">Task Information</h5>
                
                <div class="mb-3">
                    <label class="text-muted small">Status</label>
                    <p class="mb-0">
                        @if($task->status)
                            <span class="badge fs-6" style="background-color: {{ $task->status->color }}; color: #fff;">
                                {{ $task->status->name }}
                            </span>
                        @else
                            <span class="badge bg-secondary fs-6">Unassigned</span>
                        @endif
                    </p>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Priority</label>
                    <p class="mb-0">
                        <span class="badge bg-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning' : ($task->priority === 'medium' ? 'info' : 'secondary')) }} fs-6">
                            {{ ucfirst($task->priority) }}
                        </span>
                    </p>
                </div>

                @if($task->owner)
                    <div class="mb-3">
                        <label class="text-muted small">Owner</label>
                        <p class="mb-0">
                            <span class="badge bg-info">{{ $task->owner->name }}</span>
                        </p>
                    </div>
                @endif

                @if($task->start_date)
                    <div class="mb-3">
                        <label class="text-muted small">Start Date</label>
                        <p class="mb-0" style="color: var(--text-color);">{{ $task->start_date->format('F d, Y') }}</p>
                    </div>
                @endif

                @if($task->due_date)
                    <div class="mb-3">
                        <label class="text-muted small">Due Date</label>
                        <p class="mb-0" style="color: var(--text-color);">
                            @if($task->isOverdue())
                                <span class="text-danger">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    {{ $task->due_date->format('F d, Y') }}
                                </span>
                                <br><small class="text-danger">Overdue</small>
                            @else
                                {{ $task->due_date->format('F d, Y') }}
                            @endif
                        </p>
                    </div>
                @endif

                @if($task->estimated_hours || $task->actual_hours)
                    <div class="mb-3">
                        <label class="text-muted small">Time Tracking</label>
                        <p class="mb-0" style="color: var(--text-color);">
                            @if($task->estimated_hours)
                                <strong>Estimated:</strong> {{ $task->estimated_hours }}h<br>
                            @endif
                            @if($task->actual_hours)
                                <strong>Actual:</strong> {{ $task->actual_hours }}h
                            @endif
                        </p>
                    </div>
                @endif

                @if($task->members->count() > 0)
                    <div class="mb-3">
                        <label class="text-muted small">Assigned Members</label>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($task->members as $member)
                                <span class="badge bg-secondary">{{ $member->name }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($task->labels->count() > 0)
                    <div class="mb-3">
                        <label class="text-muted small">Labels</label>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($task->labels as $label)
                                <span class="badge" style="background-color: {{ $label->color }};">{{ $label->name }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($task->parent)
                    <div class="mb-3">
                        <label class="text-muted small">Parent Task</label>
                        <p class="mb-0">
                            <a href="{{ route('drives.projects.projects.tasks.show', [$drive, $project, $task->parent]) }}" class="text-decoration-none">
                                {{ $task->parent->title }}
                            </a>
                        </p>
                    </div>
                @endif

                <div class="mb-3">
                    <label class="text-muted small">Created By</label>
                    <p class="mb-0" style="color: var(--text-color);">{{ $task->creator->name }}</p>
                    <small class="text-muted">{{ $task->created_at->format('M d, Y g:i A') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Comments Section -->
    <div class="row">
        <div class="col-12">
            <div class="dashboard-card">
                <h5 class="mb-3" style="color: var(--text-color);">Comments</h5>

                <!-- Add Comment Form -->
                @if($drive->canEdit(auth()->user()))
                    <form action="{{ route('drives.projects.projects.tasks.comments.store', [$drive, $project, $task]) }}" method="POST" class="mb-4">
                        @csrf
                        <div class="mb-3">
                            <label for="comment" class="form-label">Add a comment</label>
                            <textarea class="form-control" id="comment" name="comment" rows="3" 
                                      placeholder="Type @username to mention someone..." required></textarea>
                            <small class="text-muted">Type @username to mention drive members</small>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-comment me-2"></i>Post Comment
                        </button>
                    </form>
                @endif

                <!-- Comments List -->
                @if($task->comments->count() > 0)
                    <div class="comments-list">
                        @foreach($task->comments as $comment)
                            <div class="comment mb-4" id="comment-{{ $comment->id }}">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px;">
                                            <span style="color: white;">{{ substr($comment->user->name, 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <strong style="color: var(--text-color);">{{ $comment->user->name }}</strong>
                                                <small class="text-muted ms-2">{{ $comment->created_at->diffForHumans() }}</small>
                                            </div>
                                            @if($drive->canEdit(auth()->user()) && ($comment->user_id === Auth::id() || $task->owner_id === Auth::id()))
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        @if($comment->user_id === Auth::id())
                                                            <li>
                                                                <button class="dropdown-item" onclick="editComment({{ $comment->id }})">
                                                                    <i class="fas fa-edit me-2"></i>Edit
                                                                </button>
                                                            </li>
                                                        @endif
                                                        <li>
                                                            <form action="{{ route('drives.projects.projects.tasks.comments.destroy', [$drive, $project, $task, $comment]) }}" 
                                                                  method="POST" 
                                                                  onsubmit="return confirm('Are you sure you want to delete this comment?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    <i class="fas fa-trash me-2"></i>Delete
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="comment-content" id="comment-content-{{ $comment->id }}" style="color: var(--text-color);">
                                            {!! $comment->comment_html ?? nl2br(e($comment->comment)) !!}
                                        </div>
                                        <div class="comment-edit-form d-none" id="comment-edit-form-{{ $comment->id }}">
                                            <form action="{{ route('drives.projects.projects.tasks.comments.update', [$drive, $project, $task, $comment]) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <textarea class="form-control mb-2" name="comment" rows="3" required>{{ $comment->comment }}</textarea>
                                                <div class="d-flex gap-2">
                                                    <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                                    <button type="button" class="btn btn-sm btn-secondary" onclick="cancelEdit({{ $comment->id }})">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                        @if($drive->canEdit(auth()->user()))
                                            <div class="mt-2">
                                                <button class="btn btn-sm btn-link text-muted p-0" onclick="replyToComment({{ $comment->id }})">
                                                    <i class="fas fa-reply me-1"></i>Reply
                                                </button>
                                            </div>
                                        @endif

                                        <!-- Replies -->
                                        @if($comment->replies->count() > 0)
                                            <div class="replies mt-3 ms-4">
                                                @foreach($comment->replies as $reply)
                                                    <div class="comment mb-3" id="comment-{{ $reply->id }}">
                                                        <div class="d-flex">
                                                            <div class="flex-shrink-0">
                                                                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                                                                     style="width: 30px; height: 30px;">
                                                                    <span style="color: white;" class="small">{{ substr($reply->user->name, 0, 1) }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1 ms-2">
                                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                                    <div>
                                                                        <strong class="small" style="color: var(--text-color);">{{ $reply->user->name }}</strong>
                                                                        <small class="text-muted ms-2">{{ $reply->created_at->diffForHumans() }}</small>
                                                                    </div>
                                                                    @if($drive->canEdit(auth()->user()) && ($reply->user_id === Auth::id() || $task->owner_id === Auth::id()))
                                                                        <form action="{{ route('drives.projects.projects.tasks.comments.destroy', [$drive, $project, $task, $reply]) }}" 
                                                                              method="POST" 
                                                                              class="d-inline"
                                                                              onsubmit="return confirm('Are you sure you want to delete this reply?');">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit" class="btn btn-sm btn-link text-danger p-0">
                                                                                <i class="fas fa-trash"></i>
                                                                            </button>
                                                                        </form>
                                                                    @endif
                                                                </div>
                                                                <div class="small" style="color: var(--text-color);">
                                                                    {!! $reply->comment_html ?? nl2br(e($reply->comment)) !!}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <!-- Reply Form -->
                                        <div class="reply-form d-none mt-2 ms-4" id="reply-form-{{ $comment->id }}">
                                            <form action="{{ route('drives.projects.projects.tasks.comments.store', [$drive, $project, $task]) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                                <textarea class="form-control mb-2" name="comment" rows="2" 
                                                          placeholder="Reply to {{ $comment->user->name }}..." required></textarea>
                                                <div class="d-flex gap-2">
                                                    <button type="submit" class="btn btn-sm btn-primary">Reply</button>
                                                    <button type="button" class="btn btn-sm btn-secondary" onclick="cancelReply({{ $comment->id }})">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No comments yet. Be the first to comment!</p>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .comment {
        padding: 1rem;
        background-color: var(--bg-secondary);
        border-radius: 8px;
    }
    .replies .comment {
        background-color: var(--bg-tertiary);
    }
    .user-mention {
        color: var(--brand-teal);
        font-weight: 600;
        text-decoration: none;
    }
    .user-mention:hover {
        text-decoration: underline;
    }
    
    /* Autocomplete styling for dark mode */
    .autocomplete-list {
        background-color: var(--bg-secondary) !important;
        border: 1px solid var(--border-color) !important;
        color: var(--text-color) !important;
        opacity: 1 !important;
        backdrop-filter: blur(10px);
    }
    
    .autocomplete-item {
        background-color: var(--bg-secondary) !important;
        color: var(--text-color) !important;
        opacity: 1 !important;
    }
    
    .autocomplete-item:hover {
        background-color: var(--bg-tertiary) !important;
        color: var(--text-color) !important;
    }
    
    .autocomplete-item.active,
    .autocomplete-item.active:hover {
        background-color: var(--brand-teal) !important;
        color: white !important;
        font-weight: 600;
    }
</style>
@endpush

@push('scripts')
<script>
    function editComment(commentId) {
        document.getElementById('comment-content-' + commentId).classList.add('d-none');
        document.getElementById('comment-edit-form-' + commentId).classList.remove('d-none');
    }

    function cancelEdit(commentId) {
        document.getElementById('comment-content-' + commentId).classList.remove('d-none');
        document.getElementById('comment-edit-form-' + commentId).classList.add('d-none');
    }

    function replyToComment(commentId) {
        document.getElementById('reply-form-' + commentId).classList.remove('d-none');
    }

    function cancelReply(commentId) {
        document.getElementById('reply-form-' + commentId).classList.add('d-none');
    }

    // @username autocomplete with keyboard navigation
    document.addEventListener('DOMContentLoaded', function() {
        const driveMembers = @json($driveMembers->map(function($member) { return ['id' => $member->id, 'name' => $member->name]; }));
        
        // Initialize autocomplete for main comment textarea
        const commentTextarea = document.getElementById('comment');
        if (commentTextarea) {
            initAutocomplete(commentTextarea);
        }
        
        // Initialize autocomplete for all reply textareas (including dynamically added ones)
        function initAllReplyTextareas() {
            document.querySelectorAll('textarea[name="comment"][form]').forEach(textarea => {
                if (!textarea.dataset.autocompleteInitialized) {
                    initAutocomplete(textarea);
                    textarea.dataset.autocompleteInitialized = 'true';
                }
            });
            
            // Also check for reply forms that might be hidden initially
            document.querySelectorAll('.reply-form textarea[name="comment"]').forEach(textarea => {
                if (!textarea.dataset.autocompleteInitialized) {
                    initAutocomplete(textarea);
                    textarea.dataset.autocompleteInitialized = 'true';
                }
            });
        }
        
        // Initialize existing reply textareas
        initAllReplyTextareas();
        
        // Watch for new reply forms being shown
        const observer = new MutationObserver(function(mutations) {
            initAllReplyTextareas();
        });
        
        // Observe the comments list for changes
        const commentsList = document.querySelector('.comments-list');
        if (commentsList) {
            observer.observe(commentsList, { childList: true, subtree: true });
        }
        
        function initAutocomplete(textarea) {
            let autocompleteList = null;
            let selectedIndex = -1;
            let currentMembers = [];
            let currentCursorPos = 0;
            let isAutocompleteActive = false;
            
            textarea.addEventListener('input', function(e) {
                const cursorPos = e.target.selectionStart;
                const text = e.target.value.substring(0, cursorPos);
                // Updated regex to match @ followed by non-space characters
                const match = text.match(/@([^\s@]*)$/);
                
                if (match && match[1].length >= 0) {
                    const query = match[1].toLowerCase().trim();
                    
                    // Filter members based on what's being typed
                    if (query.length > 0) {
                        currentMembers = driveMembers.filter(m => {
                            const nameLower = m.name.toLowerCase();
                            return nameLower.includes(query);
                        });
                    } else {
                        // Show all members if just @ is typed
                        currentMembers = driveMembers.slice();
                    }
                    
                    // Sort: exact matches first, then starts with, then contains
                    currentMembers.sort((a, b) => {
                        const aName = a.name.toLowerCase();
                        const bName = b.name.toLowerCase();
                        const aStarts = aName.startsWith(query);
                        const bStarts = bName.startsWith(query);
                        const aExact = aName === query;
                        const bExact = bName === query;
                        
                        if (aExact && !bExact) return -1;
                        if (!aExact && bExact) return 1;
                        if (aStarts && !bStarts) return -1;
                        if (!aStarts && bStarts) return 1;
                        return aName.localeCompare(bName);
                    });
                    
                    if (currentMembers.length > 0) {
                        currentCursorPos = cursorPos;
                        showAutocomplete(e.target, currentMembers, cursorPos);
                        selectedIndex = 0;
                        updateSelection();
                    } else {
                        hideAutocomplete();
                    }
                } else {
                    hideAutocomplete();
                }
            });

            // Keyboard navigation
            commentTextarea.addEventListener('keydown', function(e) {
                if (!isAutocompleteActive || !autocompleteList) {
                    // Reset selection index when autocomplete is not active
                    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                        const cursorPos = e.target.selectionStart;
                        const text = e.target.value.substring(0, cursorPos);
                        const match = text.match(/@([^\s@]*)$/);
                        if (match) {
                            // Re-trigger autocomplete if @ is present
                            const inputEvent = new Event('input', { bubbles: true });
                            textarea.dispatchEvent(inputEvent);
                            e.preventDefault();
                            return;
                        }
                    }
                    return;
                }
                
                const items = autocompleteList.querySelectorAll('li');
                if (items.length === 0 || currentMembers.length === 0) {
                    hideAutocomplete();
                    return;
                }
                
                switch(e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        e.stopPropagation();
                        selectedIndex = (selectedIndex + 1) % items.length;
                        updateSelection();
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        e.stopPropagation();
                        selectedIndex = selectedIndex <= 0 ? items.length - 1 : selectedIndex - 1;
                        updateSelection();
                        break;
                    case 'Enter':
                    case 'Tab':
                        e.preventDefault();
                        e.stopPropagation();
                        if (selectedIndex >= 0 && selectedIndex < items.length && selectedIndex < currentMembers.length) {
                            const member = currentMembers[selectedIndex];
                            if (member && member.name) {
                                selectMember(member);
                            }
                        }
                        break;
                    case 'Escape':
                        e.preventDefault();
                        e.stopPropagation();
                        hideAutocomplete();
                        // Remove the @ symbol if nothing was typed after it
                        const text = textarea.value;
                        const beforeCursor = text.substring(0, currentCursorPos);
                        const afterCursor = text.substring(currentCursorPos);
                        const match = beforeCursor.match(/@([^\s@]*)$/);
                        if (match && match[1].length === 0) {
                            textarea.value = beforeCursor.slice(0, -1) + afterCursor;
                            textarea.setSelectionRange(currentCursorPos - 1, currentCursorPos - 1);
                        }
                        break;
                }
            });

            function updateSelection() {
                if (!autocompleteList) return;
                
                const items = autocompleteList.querySelectorAll('li');
                if (items.length === 0 || selectedIndex < 0 || selectedIndex >= items.length) {
                    // Reset to 0 if out of bounds
                    if (selectedIndex < 0 || selectedIndex >= items.length) {
                        selectedIndex = items.length > 0 ? 0 : -1;
                    }
                    return;
                }
                
                items.forEach((item, index) => {
                    if (index === selectedIndex) {
                        item.classList.add('active');
                        item.style.backgroundColor = 'var(--brand-teal)';
                        item.style.color = 'white';
                        item.style.opacity = '1';
                        item.setAttribute('aria-selected', 'true');
                        // Scroll into view if needed
                        item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                    } else {
                        item.classList.remove('active');
                        item.style.backgroundColor = 'var(--bg-secondary)';
                        item.style.color = 'var(--text-color)';
                        item.style.opacity = '1';
                        item.setAttribute('aria-selected', 'false');
                    }
                });
            }

            function selectMember(member) {
                if (!member || !member.name) {
                    console.error('Invalid member selected:', member);
                    hideAutocomplete();
                    return;
                }
                
                const text = textarea.value;
                const beforeMatch = text.substring(0, currentCursorPos).replace(/@[^\s@]*$/, '');
                const afterMatch = text.substring(currentCursorPos);
                textarea.value = beforeMatch + '@' + member.name + ' ' + afterMatch;
                const newPos = beforeMatch.length + member.name.length + 2;
                textarea.setSelectionRange(newPos, newPos);
                hideAutocomplete();
            }

            function showAutocomplete(textarea, members, cursorPos) {
                hideAutocomplete();
                isAutocompleteActive = true;
                selectedIndex = 0;
                
                const rect = textarea.getBoundingClientRect();
                autocompleteList = document.createElement('ul');
                autocompleteList.className = 'autocomplete-list';
                autocompleteList.setAttribute('role', 'listbox');
                autocompleteList.setAttribute('aria-label', 'User suggestions');
                
                // Position dropdown below the textarea with more space
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                const topPosition = rect.bottom + scrollTop + 10; // 10px gap
                
                autocompleteList.style.cssText = `
                    position: absolute;
                    top: ${topPosition}px;
                    left: ${rect.left}px;
                    width: ${rect.width}px;
                    z-index: 1050;
                    max-height: 200px;
                    overflow-y: auto;
                    background-color: var(--bg-secondary);
                    border: 1px solid var(--border-color);
                    border-radius: 8px;
                    padding: 0.5rem;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
                    margin: 0;
                    opacity: 1;
                `;
                
                members.forEach((member, index) => {
                    const li = document.createElement('li');
                    li.className = 'autocomplete-item';
                    li.setAttribute('role', 'option');
                    li.setAttribute('aria-selected', index === selectedIndex ? 'true' : 'false');
                    li.style.cssText = `
                        cursor: pointer;
                        padding: 0.5rem 0.75rem;
                        margin: 0.125rem 0;
                        border-radius: 4px;
                        transition: all 0.15s ease;
                        background-color: var(--bg-secondary);
                        color: var(--text-color);
                        list-style: none;
                        opacity: 1;
                    `;
                    li.textContent = member.name;
                    
                    li.addEventListener('mouseenter', () => {
                        selectedIndex = index;
                        updateSelection();
                    });
                    
                    li.addEventListener('click', () => {
                        selectMember(member);
                    });
                    
                    autocompleteList.appendChild(li);
                });
                
                updateSelection();
                document.body.appendChild(autocompleteList);
            }

            function hideAutocomplete() {
                if (autocompleteList) {
                    autocompleteList.remove();
                    autocompleteList = null;
                    isAutocompleteActive = false;
                    selectedIndex = -1;
                    currentMembers = [];
                }
            }

            // Hide on click outside
            const clickHandler = function(e) {
                if (autocompleteList && !autocompleteList.contains(e.target) && e.target !== textarea) {
                    hideAutocomplete();
                }
            };
            document.addEventListener('click', clickHandler);

            // Hide when scrolling the page
            const scrollHandler = function() {
                hideAutocomplete();
            };
            window.addEventListener('scroll', scrollHandler, true);
            
            // Store handlers for cleanup if needed (optional)
            textarea._autocompleteHandlers = {
                click: clickHandler,
                scroll: scrollHandler
            };
        }
    });
</script>
@endpush
@endsection

