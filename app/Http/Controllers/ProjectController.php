<?php

namespace App\Http\Controllers;

use App\Models\Drive;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    /**
     * Display shared projects (projects user has access to but is not a drive member)
     */
    public function shared()
    {
        $user = auth()->user();
        
        // Get all projects where user is a member but not a drive member
        $sharedProjects = Project::whereHas('users', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->whereDoesntHave('drive.users', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['drive', 'creator', 'users' => function($query) {
            $query->withPivot('role');
        }, 'tasks' => function($query) {
            $query->whereNull('deleted_at');
        }])
        ->withCount([
            'tasks as total_tasks' => function($query) {
                $query->whereNull('deleted_at');
            },
            'tasks as completed_tasks' => function($query) {
                $query->whereNull('deleted_at')
                    ->whereHas('status', fn($statusQuery) => $statusQuery->where('is_completed', true));
            },
        ])
        ->orderBy('created_at', 'desc')
        ->get();

        return view('projects.shared', compact('sharedProjects'));
    }

    /**
     * Display a listing of projects for the drive
     */
    public function index(Drive $drive)
    {
        try {
            $user = auth()->user();
            
            // Check if user has access to view the project board
            // This considers both drive-level and project-level permissions
            $canViewProjectBoard = false;
            
            if ($drive->hasMember($user)) {
                // Drive member: Check if they can view project board via role OR project-level access
                // First check role-based permission
                if ($drive->userCanViewProjectBoard($user)) {
                    $canViewProjectBoard = true;
                } else {
                    // Even if role doesn't allow, check if they have project-level access
                    // (they might be shared to projects even as a drive member)
                    $hasProjectAccess = \App\Models\Project::where('drive_id', $drive->id)
                        ->whereHas('users', function($query) use ($user) {
                            $query->where('user_id', $user->id);
                        })
                        ->exists();
                    
                    if ($hasProjectAccess) {
                        $canViewProjectBoard = true;
                    }
                }
                
                // If they can view, verify drive authorization
                if ($canViewProjectBoard) {
                    $this->authorize('view', $drive);
                }
            } else {
                // Not a drive member: Check if they have project-level access
                $hasProjectAccess = \App\Models\Project::where('drive_id', $drive->id)
                    ->whereHas('users', function($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->exists();
                
                if ($hasProjectAccess) {
                    $canViewProjectBoard = true;
                }
            }
            
            if (!$canViewProjectBoard) {
                abort(403, 'You do not have permission to access Project Board.');
            }

            // Get all projects and filter based on user permissions
            $allProjects = $drive->projects()
                ->with(['creator', 'users', 'tasks' => function($query) {
                    $query->whereNull('deleted_at');
                }])
                ->withCount([
                    'tasks as total_tasks' => function($query) {
                        $query->whereNull('deleted_at');
                    },
                    'tasks as completed_tasks' => function($query) {
                        $query->whereNull('deleted_at')
                            ->whereHas('status', fn($statusQuery) => $statusQuery->where('is_completed', true));
                    },
                ])
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Filter projects based on user permissions
            $filteredProjects = $allProjects->filter(function ($project) use ($drive, $user) {
                try {
                    return $drive->userCanViewProject($user, $project);
                } catch (\Exception $e) {
                    \Log::error('Error checking project view permission', [
                        'project_id' => $project->id,
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                    return false;
                }
            });

            // Convert filtered collection to paginated results
            $currentPage = request()->get('page', 1);
            $perPage = 20;
            $items = $filteredProjects->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $total = $filteredProjects->count();
            
            $projects = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $currentPage,
                ['path' => request()->url(), 'query' => request()->query()]
            );

            return view('projects.index', compact('drive', 'projects'));
        } catch (\Exception $e) {
            \Log::error('Error in ProjectController@index', [
                'user_id' => auth()->id(),
                'drive_id' => $drive->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return a proper error response instead of empty response
            abort(500, 'An error occurred while loading the project board. Please try again.');
        }
    }

    /**
     * Show the form for creating a new project
     */
    public function create(Drive $drive)
    {
        // Check if user has permission to view Project Board
        if (!$drive->userCanViewProjectBoard(auth()->user())) {
            abort(403, 'You do not have permission to access Project Board.');
        }
        
        // If user is not a drive member, they can't create projects
        if (!$drive->hasMember(auth()->user())) {
            abort(403, 'Only drive members can create projects.');
        }
        
        // User is a drive member, verify drive authorization
        $this->authorize('view', $drive);
        
        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create projects.');
        }
        
        return view('projects.create', compact('drive'));
    }

    /**
     * Store a newly created project
     */
    public function store(Request $request, Drive $drive)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create projects.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_public' => 'nullable|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'header_image' => 'nullable|image|max:10240', // 10MB
        ]);

        // Convert dates from user timezone to drive timezone
        $driveTimezone = $drive->getEffectiveTimezone();
        $userTimezone = \App\Helpers\TimezoneHelper::getUserTimezone(auth()->user(), $drive);
        
        if (isset($validated['start_date'])) {
            $startDate = \Carbon\Carbon::parse($validated['start_date'], $userTimezone);
            $startDate->setTimezone($driveTimezone);
            $validated['start_date'] = $startDate->format('Y-m-d');
        }
        
        if (isset($validated['end_date'])) {
            $endDate = \Carbon\Carbon::parse($validated['end_date'], $userTimezone);
            $endDate->setTimezone($driveTimezone);
            $validated['end_date'] = $endDate->format('Y-m-d');
        }

        $project = $drive->projects()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? '#3B82F6',
            'is_public' => $request->has('is_public'),
            'public_key' => $request->has('is_public') ? Project::generatePublicKey() : null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'created_by' => Auth::id(),
            'status' => 'active',
        ]);

        $defaultStatuses = [
            ['name' => 'To-Do', 'slug' => 'todo', 'color' => '#6B7280', 'is_completed' => false],
            ['name' => 'In Progress', 'slug' => 'in_progress', 'color' => '#3B82F6', 'is_completed' => false],
            ['name' => 'Review', 'slug' => 'review', 'color' => '#0EA5E9', 'is_completed' => false],
            ['name' => 'Done', 'slug' => 'done', 'color' => '#10B981', 'is_completed' => true],
            ['name' => 'Blocked', 'slug' => 'blocked', 'color' => '#EF4444', 'is_completed' => false],
        ];

        $sortOrder = 0;
        foreach ($defaultStatuses as $status) {
            $project->taskStatuses()->create([
                'name' => $status['name'],
                'slug' => $status['slug'],
                'color' => $status['color'],
                'is_completed' => $status['is_completed'],
                'sort_order' => $sortOrder,
            ]);
            $sortOrder += 10;
        }

        // Handle header image upload
        if ($request->hasFile('header_image')) {
            $this->storeHeaderImage($project, $request->file('header_image'));
        }

        return redirect()->route('drives.projects.projects.show', [$drive, $project])
            ->with('success', 'Project created successfully!');
    }

    /**
     * Display the specified project
     */
    public function show(Drive $drive, Project $project, Request $request)
    {
        $user = auth()->user();
        
        // If user is not a drive member, check project-level access first
        if (!$drive->hasMember($user)) {
            // Check if user has project-level access
            if (!$project->userCanView($user)) {
                abort(403, 'You do not have permission to view this project.');
            }
            // User has project-level access - no need to check drive authorization
            // They can access the project even though they're not a drive member
        } else {
            // User is a drive member, check drive-level permissions
            if (!$drive->userCanViewProject($user, $project)) {
                abort(403, 'You do not have permission to view this project.');
            }
            // Verify drive authorization
            $this->authorize('view', $drive);
        }

        if ($project->drive_id !== $drive->id) {
            abort(404);
        }

        // Get user preferences for saved view and filters
        $userPreference = null;
        $savedFilters = [];
        $viewSettings = [];
        
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('user_project_preferences')) {
                $userPreference = \App\Models\UserProjectPreference::where('user_id', auth()->id())
                    ->where('project_id', $project->id)
                    ->first();
                
                $savedFilters = $userPreference?->filters ?? [];
                $viewSettings = $userPreference?->view_settings ?? [];
            }
        } catch (\Exception $e) {
            // Table doesn't exist yet - migrations haven't run
            // Use defaults
        }
        
        $view = $request->get('view', $userPreference?->view ?? 'kanban'); // list, kanban, gantt, calendar, workload

        // Optimize queries with eager loading and pagination for large projects
        $project->load([
            'tasks' => function($query) {
                $query->whereNull('deleted_at')
                    ->with(['members', 'labels', 'owner', 'creator', 'attachments', 'status', 'checklistItems'])
                    ->orderBy('sort_order')
                    ->orderBy('created_at', 'desc');
            },
            'users',
        ]);

        $statuses = $project->taskStatuses()
            ->with(['tasks' => function($query) {
                $query->with(['owner', 'creator', 'members', 'labels', 'attachments', 'status', 'checklistItems'])
                    ->whereNull('deleted_at');
            }])
            ->orderBy('sort_order')
            ->get();

        $tasksByStatus = $statuses->mapWithKeys(function ($status) {
            return [$status->slug => $status->tasks];
        });

        $statusSummary = $statuses->map(function ($status) {
            return [
                'id' => $status->id,
                'slug' => $status->slug,
                'name' => $status->name,
                'color' => $status->color,
                'is_completed' => $status->is_completed,
                'count' => $status->tasks->count(),
            ];
        });

        // Get drive members for task assignment
        $driveMembers = $drive->users()->get();
        
        // Get available users for project assignment
        $availableUsers = $drive->users()->orderBy('name')->get();
        
        // Get task labels
        $labels = $drive->taskLabels()->where('is_active', true)->get();
        
        // Get custom field definitions (load all, not just active)
        $customFieldDefinitions = $project->customFieldDefinitions()->get();

        // For workload view, prepare member statistics
        $memberStats = [];
        if ($view === 'workload') {
            foreach ($driveMembers as $member) {
                $memberTasks = $project->tasks()->whereNull('deleted_at')
                    ->with('status')
                    ->where(function($query) use ($member) {
                        $query->where('owner_id', $member->id)
                            ->orWhereHas('taskMembers', function($q) use ($member) {
                                $q->where('user_id', $member->id);
                            });
                    })
                    ->get();
                
                $memberStats[$member->id] = [
                    'name' => $member->name,
                    'total' => $memberTasks->count(),
                    'active' => $memberTasks->filter(fn($task) => !$task->status?->is_completed)->count(),
                    'done' => $memberTasks->filter(fn($task) => $task->status?->is_completed)->count(),
                    'overdue' => $memberTasks->filter(function($task) {
                        return $task->isOverdue();
                    })->count(),
                    'estimated_hours' => $memberTasks->sum('estimated_hours') ?? 0,
                ];
            }
        }

        // Reverb configuration for real-time updates
        $broadcastConnection = config('broadcasting.default');
        $isReverbEnabled = $broadcastConnection === 'reverb';
        // Determine host for JavaScript clients
        // Priority: 1. REVERB_HOST from .env, 2. Request host (if HTTPS/secure), 3. localhost for local dev
        $clientHost = env('REVERB_HOST');
        if (!$clientHost) {
            // If we're on HTTPS or production URL, use request host
            // This handles cases where APP_ENV might be set to 'local' but we're actually in production
            if (request()->secure() || str_contains(request()->getHost(), '.')) {
                $clientHost = request()->getHost();
            } else {
                // Truly local development
                $clientHost = 'localhost';
            }
        }

        // Determine port - use REVERB_PORT from env, or detect from scheme
        $clientPort = env('REVERB_PORT');
        if (!$clientPort) {
            $clientPort = request()->secure() ? 443 : 8080;
        }

        // Determine scheme - use REVERB_SCHEME from env, or detect from request
        $clientScheme = env('REVERB_SCHEME');
        if (!$clientScheme) {
            $clientScheme = request()->secure() ? 'https' : 'http';
        }

        $reverbConfig = [
            'connection' => $broadcastConnection,
            'isEnabled' => $isReverbEnabled && auth()->check(),
            'key' => config('broadcasting.connections.reverb.key'),
            // For JavaScript clients: browsers need 'localhost' in dev, actual domain in production
            // In production, this should match your domain (e.g., drive.tridah.cloud)
            'host' => $clientHost,
            'port' => (int)$clientPort,
            'scheme' => $clientScheme,
        ];

        return view('projects.show', compact(
            'drive',
            'project',
            'view',
            'tasksByStatus',
            'driveMembers',
            'availableUsers',
            'labels',
            'memberStats',
            'statuses',
            'statusSummary',
            'savedFilters',
            'viewSettings',
            'customFieldDefinitions',
            'reverbConfig'
        ));
    }

    /**
     * Show the form for editing the specified project
     */
    public function edit(Drive $drive, Project $project)
    {
        // Check if user has permission to edit
        // Project-level permissions take priority - if user is a viewer at project level, they cannot edit
        if (!$project->userCanEdit(auth()->user())) {
            abort(403, 'You do not have permission to edit this project.');
        }

        // If user is not a drive member, check if they have project-level access
        if (!$drive->hasMember(auth()->user())) {
            if (!$project->userCanView(auth()->user())) {
                abort(403, 'You do not have permission to view this project.');
            }
        } else {
            // User is a drive member, verify drive authorization
            $this->authorize('view', $drive);
        }

        if ($project->drive_id !== $drive->id) {
            abort(404);
        }

        // Load users relationship with pivot data
        $project->load(['users' => function($query) {
            $query->withPivot('role');
        }]);

        // Get available people for assignment (from drive)
        $availableUsers = $drive->users()->orderBy('name')->get();

        return view('projects.edit', compact('drive', 'project', 'availableUsers'));
    }

    /**
     * Update the specified project
     */
    public function update(Request $request, Drive $drive, Project $project)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to edit
        // Project-level permissions take priority - if user is a viewer at project level, they cannot edit
        if (!$project->userCanEdit(auth()->user())) {
            abort(403, 'You do not have permission to edit this project.');
        }

        if ($project->drive_id !== $drive->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_public' => 'nullable|boolean',
            'status' => 'required|in:active,archived,completed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'header_image' => 'nullable|image|max:10240',
        ]);

        // Convert dates from user timezone to drive timezone
        $driveTimezone = $drive->getEffectiveTimezone();
        $userTimezone = \App\Helpers\TimezoneHelper::getUserTimezone(auth()->user(), $drive);
        
        if (isset($validated['start_date'])) {
            $startDate = \Carbon\Carbon::parse($validated['start_date'], $userTimezone);
            $startDate->setTimezone($driveTimezone);
            $validated['start_date'] = $startDate->format('Y-m-d');
        }
        
        if (isset($validated['end_date'])) {
            $endDate = \Carbon\Carbon::parse($validated['end_date'], $userTimezone);
            $endDate->setTimezone($driveTimezone);
            $validated['end_date'] = $endDate->format('Y-m-d');
        }

        // Handle public key generation if making project public
        if ($request->has('is_public') && !$project->is_public) {
            $validated['public_key'] = Project::generatePublicKey();
        } elseif (!$request->has('is_public') && $project->is_public) {
            $validated['public_key'] = null;
        }

        $project->update($validated);

        // Handle header image upload
        if ($request->hasFile('header_image')) {
            // Delete old header image
            if ($project->header_image_path) {
                Storage::disk('public')->delete($project->header_image_path);
            }
            $this->storeHeaderImage($project, $request->file('header_image'));
        }

        return redirect()->route('drives.projects.projects.show', [$drive, $project])
            ->with('success', 'Project updated successfully!');
    }

    /**
     * Assign users to a project
     */
    public function assignPeople(Request $request, Drive $drive, Project $project)
    {
        if ($project->drive_id !== $drive->id) {
            abort(404);
        }

        // Check if user can assign users to projects
        // Project-level permissions take priority
        $canAssign = false;
        
        // First check if user has a project-level role
        $projectRole = $project->getUserRole(auth()->user());
        if ($projectRole !== null) {
            // If user has project-level role, only editors can assign
            if ($projectRole === 'editor') {
                $canAssign = true;
            } else {
                // Viewers cannot assign, even if drive allows it
                $canAssign = false;
            }
        } else {
            // No project-level role, check drive permissions
            if ($drive->canEdit(auth()->user()) || $drive->isOwnerOrAdmin(auth()->user())) {
                $canAssign = true;
            }
        }
        
        // Return JSON for AJAX requests
        $isAjax = $request->wantsJson() || $request->ajax();
        
        if (!$canAssign) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to assign users to this project. You need to be a drive owner, admin, member, or project editor.'
                ], 403);
            }
            return redirect()->back()
                ->withErrors(['permission' => 'You do not have permission to assign users to this project. You need to be a drive owner, admin, member, or project editor.'])
                ->withInput();
        }

        // If user is not a drive member, check if they have project-level access
        if (!$drive->hasMember(auth()->user())) {
            if (!$project->userCanView(auth()->user())) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to view this project.'
                    ], 403);
                }
                return redirect()->back()
                    ->withErrors(['permission' => 'You do not have permission to view this project.'])
                    ->withInput();
            }
        } else {
            // User is a drive member, verify drive authorization
            try {
                $this->authorize('view', $drive);
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to access this drive.'
                    ], 403);
                }
                return redirect()->back()
                    ->withErrors(['permission' => 'You do not have permission to access this drive.'])
                    ->withInput();
            }
        }

        try {
            $validated = $request->validate([
                'users' => 'nullable|array',
                'users.*.id' => 'required_with:users|exists:users,id',
                'users.*.role' => 'required_with:users|in:viewer,editor',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
                ], 422);
            }
            throw $e;
        }

        // Sync assigned users with roles
        $usersData = [];
        if (!empty($validated['users'])) {
            foreach ($validated['users'] as $userData) {
                if (isset($userData['id']) && isset($userData['role'])) {
                    $usersData[$userData['id']] = ['role' => $userData['role']];
                }
            }
        }
        
        $project->users()->sync($usersData);

        // Return JSON response for AJAX requests
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Users assigned to project successfully!'
            ]);
        }

        return redirect()->back()->with('success', 'Users assigned to project successfully!');
    }

    /**
     * Search for users by email to add to project
     */
    public function searchUsers(Request $request, Drive $drive, Project $project)
    {
        // Check if user has permission to add users
        // Project-level permissions take priority
        if (!$project->userCanEdit(auth()->user())) {
            abort(403, 'You do not have permission to add users to projects.');
        }

        // If user is not a drive member, check if they have project-level access
        if (!$drive->hasMember(auth()->user())) {
            if (!$project->userCanView(auth()->user())) {
                abort(403, 'You do not have permission to view this project.');
            }
        } else {
            // User is a drive member, verify drive authorization
            $this->authorize('view', $drive);
        }

        if ($project->drive_id !== $drive->id) {
            abort(404);
        }

        $request->validate([
            'email' => 'required|email',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'error' => 'User not found with that email address.',
            ], 404);
        }

        // Check if user is already assigned
        if ($project->users()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'error' => 'User is already assigned to this project.',
            ], 422);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Remove the specified project
     */
    public function destroy(Drive $drive, Project $project)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to delete
        // Project-level permissions take priority - viewers cannot delete
        if (!$project->userCanEdit(auth()->user())) {
            abort(403, 'You do not have permission to delete this project.');
        }

        if ($project->drive_id !== $drive->id) {
            abort(404);
        }

        // Delete header image if exists
        if ($project->header_image_path) {
            Storage::disk('public')->delete($project->header_image_path);
        }

        $project->delete();

        return redirect()->route('drives.projects.projects.index', $drive)
            ->with('success', 'Project deleted successfully!');
    }

    /**
     * Store header image for project
     */
    protected function storeHeaderImage(Project $project, $file)
    {
        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = 'project-headers/' . $project->drive_id . '/' . $project->id;
        
        $filePath = $file->storeAs($path, $filename, 'public');

        $project->update([
            'header_image_path' => $filePath,
            'header_image_original_name' => $file->getClientOriginalName(),
        ]);
    }

    /**
     * Public view of project board
     */
    public function publicShow(string $publicKey)
    {
        $project = Project::where('public_key', $publicKey)
            ->where('is_public', true)
            ->where('status', 'active')
            ->firstOrFail();

        $project->load([
            'drive',
            'tasks.members',
            'tasks.labels',
            'tasks.owner',
            'tasks.attachments',
            'tasks.status',
            'tasks.checklistItems',
        ]);

        $statuses = $project->taskStatuses()
            ->with(['tasks' => function($query) {
                $query->with(['owner', 'members', 'labels', 'attachments', 'status', 'checklistItems'])
                    ->whereNull('deleted_at');
            }])
            ->orderBy('sort_order')
            ->get();

        $tasksByStatus = $statuses->mapWithKeys(function ($status) {
            return [$status->slug => $status->tasks];
        });

        return view('projects.public', compact('project', 'tasksByStatus', 'statuses'));
    }
}
