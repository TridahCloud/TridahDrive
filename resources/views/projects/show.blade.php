@extends('layouts.dashboard')

@section('title', $project->name . ' - ' . $drive->name)

@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .project-layout {
        overflow-x: hidden;
    }

    .wrapper,
    .main-content {
        overflow-x: hidden;
    }
    
    .kanban-board-wrapper {
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        position: relative;
    }
    
    .kanban-board {
        padding-bottom: 0.5rem;
    }
    
    .kanban-columns {
        width: max-content;
        min-height: 100%;
        scroll-snap-type: x proximity;
        align-items: flex-start;
    }
    
    .kanban-column-wrapper {
        flex: 0 0 320px;
        width: 320px;
        scroll-snap-align: start;
    }
    
    @media (max-width: 768px) {
        .kanban-column-wrapper {
            flex-basis: 280px;
            width: 280px;
        }
    }
    
    .kanban-column {
        background-color: var(--bg-secondary);
        border-radius: 12px;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        min-height: 320px;
        overflow-y: auto;
    }
    
    
    .kanban-column-header {
        flex-shrink: 0;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--border-color);
    }
    
    .kanban-column-content {
        flex: 1;
        overflow-y: auto;
        padding-right: 0.5rem;
        position: relative;
        min-height: 200px;
    }
    
    .kanban-empty-state {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: calc(100% - 1rem);
        pointer-events: none;
        z-index: 0;
        opacity: 0.5;
    }
    
    .kanban-column-content .task-card {
        position: relative;
        z-index: 1;
        pointer-events: auto;
    }
    
    /* Custom scrollbar for kanban columns */
    .kanban-column-content::-webkit-scrollbar {
        width: 6px;
    }
    
    .kanban-column-content::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .kanban-column-content::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 3px;
    }
    
    .kanban-column-content::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.3);
    }
    
    .task-card {
        background-color: var(--bg-primary);
        border: 2px solid var(--border-color);
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        text-align: left;
    }
    
    .task-card:hover {
        border-color: var(--brand-teal, #31d8b2);
        box-shadow: 0 4px 12px rgba(49, 216, 178, 0.2);
        transform: translateY(-2px);
    }
    
    .task-card.dragging {
        opacity: 0.5;
        cursor: grabbing;
    }
    
    .task-card.active {
        border-color: var(--brand-teal, #31d8b2);
        box-shadow: 0 0 0 3px rgba(49, 216, 178, 0.2);
    }
    
    .task-card-compact {
        padding: 0.5rem !important;
    }
    
    .task-card-compact .task-card-title {
        font-size: 0.85rem;
        margin-bottom: 0.25rem;
    }
    
    .task-card-compact .task-card-meta {
        margin-bottom: 0.25rem;
    }
    
    .task-card-compact .task-card-footer {
        padding-top: 0.25rem;
    }
    
    .quick-add-task {
        background-color: var(--bg-primary);
        border: 2px dashed var(--border-color);
        border-radius: 8px;
    }
    
    .quick-add-task input:focus {
        border-color: var(--brand-teal, #31d8b2);
        box-shadow: 0 0 0 2px rgba(49, 216, 178, 0.1);
    }
    
    .kanban-empty-state {
        transition: all 0.3s ease;
    }
    
    .kanban-empty-state:hover {
        opacity: 0.8;
    }
    
    .task-context-menu {
        position: fixed;
        z-index: 2000;
        background-color: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .task-context-menu .list-group-item {
        border: none;
        border-radius: 0;
        cursor: pointer;
    }
    
    .task-context-menu .list-group-item:first-child {
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }
    
    .task-context-menu .list-group-item:last-child {
        border-bottom-left-radius: 8px;
        border-bottom-right-radius: 8px;
    }
    
    .task-context-menu .list-group-item:hover {
        background-color: var(--bg-secondary);
    }
    
    .list-group-divider {
        height: 1px;
        background-color: var(--border-color);
        margin: 0.25rem 0;
    }
    
    /* Quill Editor Styling in Sidebar */
    #taskDescriptionEditor {
        border: 1px solid var(--border-color);
        border-radius: 4px;
        background-color: var(--bg-primary);
    }
    
    #taskDescriptionEditor .ql-editor {
        min-height: 150px;
        color: var(--text-color);
        background-color: var(--bg-primary);
    }
    
    #taskDescriptionEditor .ql-toolbar {
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
        background-color: var(--bg-secondary);
        border-bottom: 1px solid var(--border-color);
    }
    
    #taskDescriptionEditor .ql-toolbar .ql-stroke {
        stroke: var(--text-color);
    }
    
    #taskDescriptionEditor .ql-toolbar .ql-fill {
        fill: var(--text-color);
    }
    
    #taskDescriptionEditor .ql-toolbar button:hover,
    #taskDescriptionEditor .ql-toolbar button.ql-active {
        color: var(--brand-teal, #31d8b2);
    }
    
    #taskDescriptionEditor .ql-toolbar button:hover .ql-stroke,
    #taskDescriptionEditor .ql-toolbar button.ql-active .ql-stroke {
        stroke: var(--brand-teal, #31d8b2);
    }
    
    #taskDescriptionEditor .ql-toolbar button:hover .ql-fill,
    #taskDescriptionEditor .ql-toolbar button.ql-active .ql-fill {
        fill: var(--brand-teal, #31d8b2);
    }
    
    #taskDescriptionEditor .ql-container {
        border-bottom-left-radius: 4px;
        border-bottom-right-radius: 4px;
        background-color: var(--bg-primary);
        color: var(--text-color);
    }
    
    #taskDescriptionEditor .ql-container .ql-editor.ql-blank::before {
        color: var(--text-color);
        opacity: 0.5;
    }
    
    .task-card-header-image {
        width: 100%;
        height: 80px;
        object-fit: cover;
        border-radius: 6px;
        margin-bottom: 0.75rem;
        border: 1px solid var(--border-color);
    }
    
    .task-card-title {
        font-weight: 600;
        font-size: 0.95rem;
        line-height: 1.4;
        margin-bottom: 0.5rem;
        color: var(--text-color);
    }
    
    .task-card-description {
        font-size: 0.85rem;
        color: var(--text-color);
        opacity: 0.7;
        margin-bottom: 0.75rem;
        line-height: 1.4;
        word-wrap: break-word;
        overflow-wrap: break-word;
        text-align: left !important;
        max-height: 2.8em;
        overflow: hidden;
        position: relative;
        display: block;
        width: 100%;
    }
    
    .task-card-description p {
        margin: 0;
        padding: 0;
        display: inline;
    }
    
    .task-card-description p:not(:last-child)::after {
        content: '\A';
        white-space: pre;
    }
    
    .task-card-description br {
        display: block;
        content: '';
        margin-top: 0.25em;
    }
    
    .task-card-description::after {
        content: '';
        position: absolute;
        bottom: 0;
        right: 0;
        width: 20px;
        height: 1.4em;
        background: linear-gradient(to right, transparent, var(--bg-primary));
    }
    
    .task-card-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
    }
    
    .task-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 0.75rem;
        border-top: 1px solid var(--border-color);
    }
    
    .task-card-actions {
        display: none; /* Hide action buttons on card, will show in sidebar */
    }
    
    /* Task Sidebar */
    .task-sidebar {
        position: fixed;
        top: 0;
        right: -450px;
        width: 450px;
        height: 100vh;
        background-color: var(--bg-secondary);
        box-shadow: -4px 0 20px rgba(0, 0, 0, 0.3);
        z-index: 1050;
        transition: right 0.3s ease;
        overflow-y: auto;
        border-left: 2px solid var(--border-color);
    }
    
    .task-sidebar.active {
        right: 0;
    }
    
    .task-sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1049;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    
    .task-sidebar-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    
    .task-sidebar-header {
        padding: 1.5rem;
        border-bottom: 2px solid var(--border-color);
        background-color: var(--bg-primary);
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .task-sidebar-content {
        padding: 1.5rem;
    }
    
    .task-sidebar-section {
        margin-bottom: 2rem;
    }
    
    .task-sidebar-section-title {
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-color);
        opacity: 0.7;
        margin-bottom: 1rem;
    }
    
    .task-description-container {
        background-color: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 1rem;
        margin-top: 0.5rem;
        color: var(--text-color);
        line-height: 1.6;
        max-height: 400px;
        overflow-y: auto;
    }
    
    .task-description-container p {
        margin-bottom: 0.75rem;
    }
    
    .task-description-container p:last-child {
        margin-bottom: 0;
    }
    
    .task-description-container::-webkit-scrollbar {
        width: 6px;
    }
    
    .task-description-container::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .task-description-container::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 3px;
    }
    
    .task-description-container::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.3);
    }
    
    .task-sidebar .comment {
        border-bottom: 1px solid var(--border-color);
    }
    
    .task-sidebar .comment:last-child {
        border-bottom: none;
    }
    
    .task-sidebar .replies {
        border-left: 2px solid var(--border-color);
        padding-left: 0.75rem;
        margin-top: 0.5rem;
    }
    
    .task-sidebar .user-mention {
        color: var(--brand-teal, #31d8b2);
        text-decoration: none;
        font-weight: 500;
    }
    
    .task-sidebar .user-mention:hover {
        text-decoration: underline;
    }
    
    .view-switcher .btn {
        border-radius: 0;
    }
    .view-switcher .btn:first-child {
        border-top-left-radius: 8px;
        border-bottom-left-radius: 8px;
    }
    .view-switcher .btn:last-child {
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
    }
    
    [data-theme="light"] .task-card {
        border-color: rgba(0, 0, 0, 0.15);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    [data-theme="light"] .task-card:hover {
        border-color: #31d8b2;
        box-shadow: 0 4px 12px rgba(49, 216, 178, 0.15);
    }
    
    [data-theme="light"] .kanban-column {
        background-color: #f8f9fa;
        border: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    [data-theme="light"] .kanban-column-header {
        border-bottom-color: rgba(0, 0, 0, 0.1);
    }
    
    [data-theme="light"] .task-sidebar {
        background-color: #ffffff;
        border-left-color: rgba(0, 0, 0, 0.1);
    }
    
    [data-theme="light"] .task-sidebar-header {
        background-color: #f8f9fa;
        border-bottom-color: rgba(0, 0, 0, 0.1);
    }

    .project-meta-badge {
        background-color: rgba(49, 216, 178, 0.12);
        color: var(--text-color);
        border: 1px solid rgba(49, 216, 178, 0.25);
        font-weight: 500;
    }

    [data-theme="dark"] .project-meta-badge {
        background-color: rgba(49, 216, 178, 0.25);
        color: #f8fafc;
        border-color: rgba(49, 216, 178, 0.5);
    }

    [data-theme="dark"] .breadcrumb-item.active {
        color: #f8fafc;
    }

    [data-theme="dark"] .breadcrumb-item + .breadcrumb-item::before {
        color: var(--text-muted);
    }
</style>
@endpush

@section('content')
<div class="container-fluid project-layout">
    <!-- Page Header -->
    <div class="row g-3 align-items-center mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('drives.index') }}">Drives</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('drives.show', $drive) }}">{{ $drive->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('drives.projects.projects.index', $drive) }}">Projects</a></li>
                    <li class="breadcrumb-item active">{{ $project->name }}</li>
                </ol>
            </nav>
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                @php
                    $projectStart = $project->start_date ? \Illuminate\Support\Carbon::parse($project->start_date) : null;
                    $projectEnd = $project->end_date ? \Illuminate\Support\Carbon::parse($project->end_date) : null;
                @endphp
                <div class="flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <h1 class="h3 mb-0 brand-teal">{{ $project->name }}</h1>
                        <span class="badge project-meta-badge">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Created {{ $project->created_at->format('M d, Y') }}
                        </span>
                        @if($projectStart)
                            <span class="badge project-meta-badge">
                                <i class="fas fa-play me-1"></i>
                                Starts {{ $projectStart->format('M d, Y') }}
                            </span>
                        @endif
                        @if($projectEnd)
                            <span class="badge project-meta-badge">
                                <i class="fas fa-flag-checkered me-1"></i>
                                Due {{ $projectEnd->format('M d, Y') }}
                            </span>
                        @endif
                    </div>
                    @if($project->description)
                        <p class="text-muted mt-1 mb-0">{{ $project->description }}</p>
                    @endif
                    @if($project->is_public && $project->public_key)
                        <div class="mt-2 d-flex flex-wrap align-items-center gap-2">
                            <div class="input-group" style="max-width: 600px;">
                                <input type="text" 
                                       class="form-control form-control-sm" 
                                       id="publicLink" 
                                       value="{{ route('projects.public.show', $project->public_key) }}" 
                                       readonly
                                       style="font-size: 0.875rem;">
                                <button class="btn btn-outline-secondary btn-sm" 
                                        type="button" 
                                        id="copyPublicLinkBtn"
                                        onclick="copyPublicLink()"
                                        title="Copy public link">
                                    <i class="fas fa-copy me-1"></i>Copy Link
                                </button>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-globe me-1"></i>This project is publicly accessible via the link above
                            </small>
                        </div>
                    @else
                        <div class="mt-2 text-muted small d-flex align-items-center gap-2">
                            <small class="text-muted">
                                <i class="fas fa-lock me-1"></i>This project is private
                            </small>
                        </div>
                    @endif
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @if($drive->canEdit(auth()->user()))
                        <a href="{{ route('drives.projects.projects.edit', [$drive, $project]) }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-2"></i>Edit Project
                        </a>
                    @endif
                    <a href="{{ route('drives.projects.projects.index', $drive) }}" class="btn btn-outline-secondary">
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

    <!-- View Switcher -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-8">
            <div class="dashboard-card h-100">
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
                    <div class="view-switcher btn-group flex-wrap" role="group">
                        <a href="{{ route('drives.projects.projects.show', [$drive, $project, 'view' => 'list']) }}" 
                           class="btn btn-{{ $view === 'list' ? 'primary' : 'outline-primary' }}">
                            <i class="fas fa-list me-2"></i>List
                        </a>
                        <a href="{{ route('drives.projects.projects.show', [$drive, $project, 'view' => 'kanban']) }}" 
                           class="btn btn-{{ $view === 'kanban' ? 'primary' : 'outline-primary' }}">
                            <i class="fas fa-columns me-2"></i>Kanban
                        </a>
                        <a href="{{ route('drives.projects.projects.show', [$drive, $project, 'view' => 'gantt']) }}" 
                           class="btn btn-{{ $view === 'gantt' ? 'primary' : 'outline-primary' }}">
                            <i class="fas fa-chart-bar me-2"></i>Gantt
                        </a>
                        <a href="{{ route('drives.projects.projects.show', [$drive, $project, 'view' => 'calendar']) }}" 
                           class="btn btn-{{ $view === 'calendar' ? 'primary' : 'outline-primary' }}">
                            <i class="fas fa-calendar-alt me-2"></i>Calendar
                        </a>
                        <a href="{{ route('drives.projects.projects.show', [$drive, $project, 'view' => 'workload']) }}" 
                           class="btn btn-{{ $view === 'workload' ? 'primary' : 'outline-primary' }}">
                            <i class="fas fa-chart-pie me-2"></i>Workload
                        </a>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @if($view === 'kanban')
                            <button type="button" class="btn btn-outline-secondary" id="showKanbanFilters">
                                <i class="fas fa-filter me-2"></i>Filters
                            </button>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="kanbanViewToggle" data-view="expanded">
                                    <i class="fas fa-compress-alt me-1"></i>Compact
                                </button>
                            </div>
                        @endif
                        @if($drive->canEdit(auth()->user()))
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#manageStatusesModal">
                                <i class="fas fa-columns me-2"></i>Manage Statuses
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createLabelModal">
                                <i class="fas fa-tag me-2"></i>Create Label
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#manageCustomFieldsModal">
                                <i class="fas fa-list-alt me-2"></i>Custom Fields
                            </button>
                            <a href="{{ route('drives.projects.projects.tasks.create', [$drive, $project]) }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>New Task
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($drive->canEdit(auth()->user()) && isset($availableUsers))
        <div class="col-12 col-xl-4">
            <div class="dashboard-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2 brand-teal"></i>Assigned Users
                    </h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assignPeopleModal">
                        <i class="fas fa-user-plus me-1"></i>Assign
                    </button>
                </div>
                @if($project->users->count() > 0)
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($project->users as $user)
                            <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle px-2 py-1 d-flex align-items-center gap-2">
                                <i class="fas fa-user"></i>
                                <span>{{ $user->name }}</span>
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0 small">
                        <i class="fas fa-info-circle me-1"></i>No users assigned yet.
                    </p>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Assign Users Modal -->
    @if($drive->canEdit(auth()->user()) && isset($availableUsers))
    <div class="modal fade" id="assignPeopleModal" tabindex="-1" aria-labelledby="assignPeopleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignPeopleModalLabel">
                        <i class="fas fa-user-plus me-2"></i>Assign Users to Project
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('drives.projects.projects.assign-people', [$drive, $project]) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted small mb-3">Select users from your Drive to assign to this project.</p>
                        
                        @if($availableUsers->count() > 0)
                            <div class="list-group">
                                @foreach($availableUsers as $user)
                                    <label class="list-group-item list-group-item-action">
                                        <div class="d-flex align-items-center">
                                            <input type="checkbox" 
                                                   class="form-check-input me-3" 
                                                   name="user_ids[]" 
                                                   value="{{ $user->id }}"
                                                   {{ $project->users->contains($user->id) ? 'checked' : '' }}>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold">{{ $user->name }}</div>
                                                @if($user->email)
                                                    <small class="text-muted">{{ $user->email }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                No users available in this Drive.
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        @if($availableUsers->count() > 0)
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Assignments
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Create Label Modal -->
    <div class="modal fade" id="createLabelModal" tabindex="-1" aria-labelledby="createLabelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createLabelModalLabel">
                        <i class="fas fa-tag me-2"></i>Create Task Label
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createLabelForm" action="{{ route('drives.projects.task-labels.store', $drive) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show">
                                <strong>Please fix the following errors:</strong>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="label_name" class="form-label">Label Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="label_name" name="name" value="{{ old('name') }}" required placeholder="e.g., Bug, Feature, Urgent">
                        </div>

                        <div class="mb-3">
                            <label for="label_description" class="form-label">Description</label>
                            <textarea class="form-control" id="label_description" name="description" rows="2" placeholder="Optional description">{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="label_color" class="form-label">Color</label>
                            <div class="d-flex align-items-center gap-3">
                                <input type="color" class="form-control form-control-color" id="label_color" name="color" value="{{ old('color', '#6366F1') }}" title="Choose color" style="width: 80px; height: 40px;">
                                <div class="flex-grow-1">
                                    <small class="text-muted">Select a color for this label. It will be displayed on task cards.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Label
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Manage Custom Fields Modal -->
    @if($drive->canEdit(auth()->user()))
    <div class="modal fade" id="manageCustomFieldsModal" tabindex="-1" aria-labelledby="manageCustomFieldsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="manageCustomFieldsModalLabel">
                        <i class="fas fa-list-alt me-2"></i>Manage Custom Fields
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <button type="button" class="btn btn-sm btn-primary" id="addCustomFieldBtn" onclick="
                            const form = document.getElementById('addCustomFieldForm');
                            const btn = document.getElementById('addCustomFieldBtn');
                            if (form && btn) {
                                form.style.display = 'block';
                                btn.style.display = 'none';
                            }
                        ">
                            <i class="fas fa-plus me-1"></i>Add Custom Field
                        </button>
                    </div>
                    
                    <div id="customFieldsList">
                        @if(isset($customFieldDefinitions) && $customFieldDefinitions && $customFieldDefinitions->count() > 0)
                            @foreach($customFieldDefinitions as $field)
                                <div class="card mb-2 custom-field-item" data-field-id="{{ $field->id }}">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">{{ $field->name }}</h6>
                                                <small class="text-muted">Type: {{ ucfirst($field->type) }} | Slug: {{ $field->slug }}</small>
                                                @if($field->required)
                                                    <span class="badge bg-warning ms-2">Required</span>
                                                @endif
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-custom-field" data-field-id="{{ $field->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted">No custom fields defined. Click "Add Custom Field" to create one.</p>
                        @endif
                    </div>
                    
                    <div id="addCustomFieldForm" style="display: none;" class="mt-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="mb-3">Add New Custom Field</h6>
                                <form id="customFieldForm" onsubmit="handleCustomFieldSubmit(event); return false;">
                                    <div class="mb-3">
                                        <label class="form-label">Field Name</label>
                                        <input type="text" class="form-control" id="customFieldName" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Field Type</label>
                                        <select class="form-select" id="customFieldType" required>
                                            <option value="text">Text</option>
                                            <option value="number">Number</option>
                                            <option value="date">Date</option>
                                            <option value="select">Select (Dropdown)</option>
                                            <option value="checkbox">Checkbox</option>
                                            <option value="textarea">Textarea</option>
                                        </select>
                                    </div>
                                    <div class="mb-3" id="customFieldOptionsContainer" style="display: none;">
                                        <label class="form-label">Options (one per line)</label>
                                        <textarea class="form-control" id="customFieldOptions" rows="3" placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="customFieldRequired">
                                            <label class="form-check-label" for="customFieldRequired">Required</label>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary btn-sm">Save Field</button>
                                        <button type="button" class="btn btn-secondary btn-sm" id="cancelCustomField" onclick="
                                            const form = document.getElementById('addCustomFieldForm');
                                            const btn = document.getElementById('addCustomFieldBtn');
                                            const optionsContainer = document.getElementById('customFieldOptionsContainer');
                                            const fieldForm = document.getElementById('customFieldForm');
                                            if (form) form.style.display = 'none';
                                            if (btn) btn.style.display = 'block';
                                            if (fieldForm) fieldForm.reset();
                                            if (optionsContainer) optionsContainer.style.display = 'none';
                                        ">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- List View -->
    @if($view === 'list')
        @include('projects.views.list')
    @endif

    <!-- Kanban View -->
    @if($view === 'kanban')
        @include('projects.views.kanban')
    @endif

    <!-- Gantt View -->
    @if($view === 'gantt')
        @include('projects.views.gantt')
    @endif

    <!-- Calendar View -->
    @if($view === 'calendar')
        @include('projects.views.calendar')
    @endif

    <!-- Workload View -->
    @if($view === 'workload')
        @include('projects.views.workload')
    @endif
</div>

@if($drive->canEdit(auth()->user()))
    <div class="modal fade" id="manageStatusesModal" tabindex="-1" aria-labelledby="manageStatusesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="manageStatusesModalLabel">
                        <i class="fas fa-columns me-2"></i>Manage Task Statuses
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <h6>Create New Status</h6>
                        <form id="createStatusForm">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-5">
                                    <label class="form-label visually-hidden" for="statusNameInput">Status Name</label>
                                    <input type="text" id="statusNameInput" name="name" class="form-control" placeholder="Status name" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label visually-hidden" for="statusColorInput">Color</label>
                                    <input type="color" id="statusColorInput" name="color" class="form-control form-control-color" value="#6B7280" title="Pick a color">
                                </div>
                                <div class="col-md-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="statusCompletedInput" name="is_completed">
                                        <label class="form-check-label" for="statusCompletedInput">
                                            Completed
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-plus me-1"></i>Add
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Existing Statuses</h6>
                        <span class="text-muted small"><i class="fas fa-grip-vertical me-1"></i>Drag statuses to reorder</span>
                    </div>

                    <ul class="list-group" id="statusList">
                        @foreach($statuses as $status)
                            <li class="list-group-item d-flex align-items-center justify-content-between gap-3 flex-wrap" data-status-id="{{ $status->id }}">
                                <div class="d-flex align-items-center gap-3 flex-wrap flex-grow-1">
                                    <span class="drag-handle text-muted" style="cursor: move;">
                                        <i class="fas fa-grip-vertical"></i>
                                    </span>
                                    <div class="flex-grow-1">
                                        <label class="form-label small mb-1">Name</label>
                                        <input type="text" class="form-control form-control-sm status-name-input" value="{{ $status->name }}">
                                    </div>
                                    <div>
                                        <label class="form-label small mb-1 d-block">Color</label>
                                        <input type="color" class="form-control form-control-color status-color-input" value="{{ $status->color }}">
                                    </div>
                                    <div class="form-check mt-4">
                                        <input class="form-check-input status-completed-input" type="checkbox" {{ $status->is_completed ? 'checked' : '' }} id="statusCompleted{{ $status->id }}">
                                        <label class="form-check-label small" for="statusCompleted{{ $status->id }}">Completed</label>
                                    </div>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary status-save-btn">
                                        <i class="fas fa-save me-1"></i>Save
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger status-delete-btn">
                                        <i class="fas fa-trash me-1"></i>Delete
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    @if($statuses->isEmpty())
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="fas fa-info-circle me-2"></i>No statuses found. Use the form above to create your first status.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.9/dist/purify.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
    // Define custom field submit handler immediately (before DOMContentLoaded)
    window.handleCustomFieldSubmit = function(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        const name = document.getElementById('customFieldName');
        const type = document.getElementById('customFieldType');
        const options = document.getElementById('customFieldOptions');
        const isRequired = document.getElementById('customFieldRequired');
        
        if (!name || !type) {
            console.error('Custom field form elements not found');
            return false;
        }
        
        const fieldName = name.value.trim();
        const fieldType = type.value;
        const fieldOptions = options ? options.value : '';
        const fieldRequired = isRequired ? isRequired.checked : false;
        
        const form = document.getElementById('customFieldForm');
        const submitBtn = form ? form.querySelector('button[type="submit"]') : null;
        const originalText = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
        }
        
        function sanitizeHtml(markup) {
            if (!markup) {
                return '';
            }
            if (window.DOMPurify) {
                return DOMPurify.sanitize(markup, { USE_PROFILES: { html: true } });
            }
            const div = document.createElement('div');
            div.textContent = markup;
            return div.innerHTML;
        }
        
        fetch('{{ route("drives.projects.projects.custom-fields.store", [$drive, $project]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                name: fieldName,
                type: fieldType,
                options: fieldOptions,
                is_required: fieldRequired
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    console.error('Error response:', err);
                    return Promise.reject(err);
                }).catch(() => {
                    return Promise.reject({ error: 'Failed to create custom field' });
                });
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success && data.field) {
                // Add the new field to the DOM
                const customFieldsList = document.getElementById('customFieldsList');
                if (!customFieldsList) {
                    console.error('Custom fields list not found');
                    alert('Field created but could not update UI. Please refresh the page.');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                    return;
                }
                
                const field = data.field;
                
                // Remove "no fields" message if it exists
                const noFieldsMessage = customFieldsList.querySelector('p.text-muted');
                if (noFieldsMessage && noFieldsMessage.textContent.includes('No custom fields defined')) {
                    noFieldsMessage.remove();
                }
                
                // Create the new field card
                const fieldCard = document.createElement('div');
                fieldCard.className = 'card mb-2 custom-field-item';
                fieldCard.setAttribute('data-field-id', field.id);
                fieldCard.innerHTML = `
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">${sanitizeHtml(field.name)}</h6>
                                <small class="text-muted">Type: ${sanitizeHtml(field.type.charAt(0).toUpperCase() + field.type.slice(1))} | Slug: ${sanitizeHtml(field.slug)}</small>
                                ${field.required ? '<span class="badge bg-warning ms-2">Required</span>' : ''}
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-danger delete-custom-field" data-field-id="${field.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                
                // Add to the list
                customFieldsList.appendChild(fieldCard);
                
                // Reset and hide the form
                const addCustomFieldForm = document.getElementById('addCustomFieldForm');
                const addCustomFieldBtn = document.getElementById('addCustomFieldBtn');
                const customFieldOptionsContainer = document.getElementById('customFieldOptionsContainer');
                const customFieldForm = document.getElementById('customFieldForm');
                
                if (addCustomFieldForm) addCustomFieldForm.style.display = 'none';
                if (addCustomFieldBtn) addCustomFieldBtn.style.display = 'block';
                if (customFieldForm) customFieldForm.reset();
                if (customFieldOptionsContainer) customFieldOptionsContainer.style.display = 'none';
                
                // Reset submit button
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            } else {
                alert(data?.error || 'Failed to create custom field');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorMsg = error.error || error.message || 'Failed to create custom field';
            alert(errorMsg);
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
        
        return false;
    };

    // Kanban drag and drop
    @if($view === 'kanban')
    document.addEventListener('DOMContentLoaded', function() {
        @php
            $statusesConfig = $statuses->map(function ($status) {
                return [
                    'id' => $status->id,
                    'slug' => $status->slug,
                    'name' => $status->name,
                    'color' => $status->color,
                    'is_completed' => (bool) $status->is_completed,
                ];
            })->values()->all();
        @endphp

        const statusesConfig = @json($statusesConfig);

        const statusLookup = {};
        statusesConfig.forEach(status => {
            statusLookup[String(status.id)] = status;
        });

        statusesConfig.forEach(status => {
            const element = document.getElementById('kanban-status-' + status.id);
            if (element) {
                new Sortable(element, {
                    group: 'tasks',
                    animation: 150,
                    handle: '.task-card',
                    draggable: '.task-card',
                    ghostClass: 'dragging',
                    onStart: function(evt) {
                        evt.item.classList.add('dragging');
                    },
                    onEnd: function(evt) {
                        evt.item.classList.remove('dragging');
                        const taskId = evt.item.dataset.taskId;
                        const newStatusId = evt.to.dataset.statusId;
                        const oldStatusId = evt.from.dataset.statusId;

                        const destinationCards = Array.from(evt.to.querySelectorAll('.task-card'));
                        const sortOrder = destinationCards.findIndex(card => card.dataset.taskId === taskId.toString());
                        
                        // Update empty states
                        updateEmptyStates(oldStatusId, newStatusId);
                        
                        // Update sort_order for all cards in the destination column
                        const allCardsInColumn = Array.from(evt.to.querySelectorAll('.task-card'));
                        const updatePromises = [];
                        
                        allCardsInColumn.forEach((card, index) => {
                            const cardTaskId = card.dataset.taskId;
                            if (cardTaskId) {
                                updatePromises.push(
                                    fetch('{{ route("drives.projects.projects.tasks.update-status", [$drive, $project, ":task"]) }}'.replace(':task', cardTaskId), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                status_id: Number(newStatusId),
                                            sort_order: index
                                        })
                                    })
                                );
                            }
                        });
                        
                        Promise.all(updatePromises)
                        .then(responses => Promise.all(responses.map(r => r.json())))
                        .then(results => {
                            if (results.every(r => r.success)) {
                                const movedTaskId = evt.item.dataset.taskId;
                                if (statusLookup[newStatusId]) {
                                    const statusInfo = statusLookup[newStatusId];
                                    if (taskData[movedTaskId]) {
                                        taskData[movedTaskId].status = statusInfo;
                                    }
                                    updateCardStatusBadge(evt.item, statusInfo);
                                }
                                updateColumnTaskCount(newStatusId);
                                if (oldStatusId !== newStatusId) {
                                    updateColumnTaskCount(oldStatusId);
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            location.reload(); // Reload on error
                        });
                    }
                });
            }
        });
        
        // Function to update empty states when cards are moved
        function updateEmptyStates(oldStatusId, newStatusId) {
            // Hide empty state for destination column if it has cards now
            const newColumn = document.getElementById('kanban-status-' + newStatusId);
            if (newColumn) {
                const newEmptyState = document.getElementById('empty-status-' + newStatusId);
                const newColumnCards = newColumn.querySelectorAll('.task-card:not(.dragging)');
                if (newColumnCards.length > 0 && newEmptyState) {
                    newEmptyState.style.display = 'none';
                }
            }
            
            // Show empty state for source column if it's now empty
            const oldColumn = document.getElementById('kanban-status-' + oldStatusId);
            if (oldColumn) {
                const oldEmptyState = document.getElementById('empty-status-' + oldStatusId);
                const oldColumnCards = oldColumn.querySelectorAll('.task-card:not(.dragging)');
                if (oldColumnCards.length === 0 && oldEmptyState) {
                    oldEmptyState.style.display = 'block';
                }
            }
        }
        
        function updateCardStatusBadge(cardElement, statusInfo) {
            if (!cardElement || !statusInfo) {
                return;
            }

            const badge = cardElement.querySelector('[data-role="task-status-badge"]');
            if (badge) {
                badge.textContent = statusInfo.name;
                badge.style.backgroundColor = statusInfo.color;
            }
        }

        // Make updateColumnTaskCount globally accessible
        window.updateColumnTaskCount = function(statusId) {
            const column = document.getElementById('kanban-status-' + statusId);
            if (!column) {
                return;
            }

            const cardCount = column.querySelectorAll('.task-card').length;
            const headerBadge = column.closest('.dashboard-card')?.querySelector('.kanban-column-header h6 .badge');
            if (headerBadge) {
                headerBadge.textContent = cardCount;
            }
        };

        // Initialize empty states visibility
        function initializeEmptyStates() {
            statusesConfig.forEach(status => {
                const column = document.getElementById('kanban-status-' + status.id);
                const emptyState = document.getElementById('empty-status-' + status.id);
                if (column && emptyState) {
                    const cards = column.querySelectorAll('.task-card');
                    emptyState.style.display = cards.length === 0 ? 'block' : 'none';
                }
            });
        }
        
        // Initialize on page load
        initializeEmptyStates();
        
        // Kanban Filters
        const kanbanFiltersRow = document.getElementById('kanbanFiltersRow');
        const showKanbanFiltersBtn = document.getElementById('showKanbanFilters');
        const toggleKanbanFiltersBtn = document.getElementById('toggleKanbanFilters');
        const clearKanbanFiltersBtn = document.getElementById('clearKanbanFilters');
        
        if (showKanbanFiltersBtn && kanbanFiltersRow) {
            showKanbanFiltersBtn.addEventListener('click', function() {
                kanbanFiltersRow.style.display = kanbanFiltersRow.style.display === 'none' ? 'block' : 'none';
                showKanbanFiltersBtn.style.display = kanbanFiltersRow.style.display === 'block' ? 'none' : 'inline-block';
            });
        }
        
        if (toggleKanbanFiltersBtn && kanbanFiltersRow) {
            toggleKanbanFiltersBtn.addEventListener('click', function() {
                kanbanFiltersRow.style.display = 'none';
                if (showKanbanFiltersBtn) showKanbanFiltersBtn.style.display = 'inline-block';
            });
        }
        
        // Filter functionality
        const filterStatus = document.getElementById('kanbanFilterStatus');
        const filterPriority = document.getElementById('kanbanFilterPriority');
        const filterLabel = document.getElementById('kanbanFilterLabel');
        const filterAssignee = document.getElementById('kanbanFilterAssignee');
        
        function applyKanbanFilters() {
            const statusFilter = filterStatus ? filterStatus.value : '';
            const priorityFilter = filterPriority ? filterPriority.value : '';
            const labelFilter = filterLabel ? filterLabel.value : '';
            const assigneeFilter = filterAssignee ? filterAssignee.value : '';
            
            document.querySelectorAll('.task-card').forEach(card => {
                const cardStatus = card.dataset.statusSlug || '';
                const cardPriority = card.dataset.priority || '';
                const cardLabelIds = (card.dataset.labelIds || '').split(',').filter(Boolean);
                const cardMemberIds = (card.dataset.memberIds || '').split(',').filter(Boolean);
                const cardOwnerId = card.dataset.ownerId || '';
                
                let show = true;
                if (statusFilter && cardStatus !== statusFilter) show = false;
                if (priorityFilter && cardPriority !== priorityFilter) show = false;
                if (labelFilter && !cardLabelIds.includes(labelFilter)) show = false;
                if (assigneeFilter && !cardMemberIds.includes(assigneeFilter) && cardOwnerId !== assigneeFilter) show = false;
                
                card.style.display = show ? '' : 'none';
            });
            
            // Update column counts
            statusesConfig.forEach(status => {
                updateColumnTaskCount(status.id);
            });
        }
        
        if (filterStatus) filterStatus.addEventListener('change', applyKanbanFilters);
        if (filterPriority) filterPriority.addEventListener('change', applyKanbanFilters);
        if (filterLabel) filterLabel.addEventListener('change', applyKanbanFilters);
        if (filterAssignee) filterAssignee.addEventListener('change', applyKanbanFilters);
        
        if (clearKanbanFiltersBtn) {
            clearKanbanFiltersBtn.addEventListener('click', function() {
                if (filterStatus) filterStatus.value = '';
                if (filterPriority) filterPriority.value = '';
                if (filterLabel) filterLabel.value = '';
                if (filterAssignee) filterAssignee.value = '';
                applyKanbanFilters();
                saveFilterPreferences();
            });
        }
        
        // Save filter preferences
        function saveFilterPreferences() {
            const filters = {
                status: filterStatus ? filterStatus.value : '',
                priority: filterPriority ? filterPriority.value : '',
                label: filterLabel ? filterLabel.value : '',
                assignee: filterAssignee ? filterAssignee.value : '',
            };
            
            fetch('{{ route("drives.projects.projects.preferences.store", [$drive, $project]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    view: 'kanban',
                    filters: filters,
                    view_settings: {
                        compact: isCompactView
                    }
                })
            }).catch(error => console.error('Failed to save preferences:', error));
        }
        
        // Load saved filters on page load
        @if(isset($savedFilters) && !empty($savedFilters))
            @if(isset($savedFilters['status']) && $savedFilters['status'])
                if (filterStatus) filterStatus.value = '{{ $savedFilters['status'] }}';
            @endif
            @if(isset($savedFilters['priority']) && $savedFilters['priority'])
                if (filterPriority) filterPriority.value = '{{ $savedFilters['priority'] }}';
            @endif
            @if(isset($savedFilters['label']) && $savedFilters['label'])
                if (filterLabel) filterLabel.value = '{{ $savedFilters['label'] }}';
            @endif
            @if(isset($savedFilters['assignee']) && $savedFilters['assignee'])
                if (filterAssignee) filterAssignee.value = '{{ $savedFilters['assignee'] }}';
            @endif
            // Apply filters after a short delay to ensure DOM is ready
            setTimeout(applyKanbanFilters, 100);
        @endif
        
        // Save preferences when filters change
        if (filterStatus) filterStatus.addEventListener('change', saveFilterPreferences);
        if (filterPriority) filterPriority.addEventListener('change', saveFilterPreferences);
        if (filterLabel) filterLabel.addEventListener('change', saveFilterPreferences);
        if (filterAssignee) filterAssignee.addEventListener('change', saveFilterPreferences);
        
        // Quick Task Creation
        document.querySelectorAll('.quick-add-trigger').forEach(trigger => {
            trigger.addEventListener('click', function() {
                const statusId = this.dataset.statusId;
                const quickAdd = document.querySelector(`#kanban-status-${statusId} .quick-add-task`);
                const quickAddButton = document.querySelector(`#kanban-status-${statusId} .quick-add-button`);
                const emptyStateButton = document.querySelector(`#empty-status-${statusId} .quick-add-trigger`);
                if (quickAdd) {
                    quickAdd.style.display = 'block';
                    const input = quickAdd.querySelector('input');
                    if (input) input.focus();
                    // Store which button was clicked for restoration on cancel
                    quickAdd.dataset.clickedButton = this === quickAddButton?.querySelector('.quick-add-trigger') ? 'quick-add-button' : 
                                                     (this === emptyStateButton ? 'empty-state' : 'unknown');
                    // Hide all buttons
                    this.style.display = 'none';
                    if (quickAddButton) quickAddButton.style.display = 'none';
                    if (emptyStateButton) emptyStateButton.style.display = 'none';
                }
            });
        });
        
        document.querySelectorAll('.quick-add-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const input = this.querySelector('input');
                const title = input.value.trim();
                if (!title) return;
                
                const statusId = this.dataset.statusId;
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Adding...';
                
                fetch('{{ route("drives.projects.projects.tasks.store", [$drive, $project]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        title: title,
                        status_id: parseInt(statusId),
                        priority: 'medium'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.task) {
                        // Reload page to show new task with all relationships
                        location.reload();
                    } else {
                        throw new Error('Failed to create task');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
            });
            
            const input = form.querySelector('input');
            if (input) {
                input.addEventListener('focus', function() {
                    const options = form.querySelector('.quick-add-options');
                    if (options) options.style.display = 'block';
                });
                
                input.addEventListener('blur', function() {
                    setTimeout(() => {
                        if (!this.value.trim()) {
                            const options = form.querySelector('.quick-add-options');
                            if (options) options.style.display = 'none';
                        }
                    }, 200);
                });
            }
        });
        
        document.querySelectorAll('.cancel-quick-add').forEach(btn => {
            btn.addEventListener('click', function() {
                const quickAdd = this.closest('.quick-add-task');
                if (quickAdd) {
                    quickAdd.style.display = 'none';
                    const input = quickAdd.querySelector('input');
                    if (input) input.value = '';
                    const options = quickAdd.querySelector('.quick-add-options');
                    if (options) options.style.display = 'none';
                    const statusId = quickAdd.closest('.kanban-column-content').dataset.statusId;
                    
                    // Show the appropriate button(s) based on whether column has tasks
                    const column = document.getElementById('kanban-status-' + statusId);
                    const hasTasks = column && column.querySelectorAll('.task-card').length > 0;
                    
                    // Always show the appropriate button based on current state
                    const quickAddButtonContainer = document.querySelector(`#kanban-status-${statusId} .quick-add-button`);
                    const quickAddButtonInside = quickAddButtonContainer ? quickAddButtonContainer.querySelector('.quick-add-trigger') : null;
                    const emptyStateButton = document.querySelector(`#empty-status-${statusId} .quick-add-trigger`);
                    
                    if (hasTasks) {
                        // Show the quick-add-button container and button if there are tasks
                        if (quickAddButtonContainer) quickAddButtonContainer.style.display = 'block';
                        if (quickAddButtonInside) quickAddButtonInside.style.display = 'block';
                        // Hide empty state button if it exists
                        if (emptyStateButton) emptyStateButton.style.display = 'none';
                    } else {
                        // Show the empty state button if no tasks
                        if (emptyStateButton) emptyStateButton.style.display = 'inline-block';
                        // Hide quick-add-button container if it exists
                        if (quickAddButtonContainer) quickAddButtonContainer.style.display = 'none';
                        if (quickAddButtonInside) quickAddButtonInside.style.display = 'none';
                    }
                    
                    // Clear the stored button reference
                    delete quickAdd.dataset.clickedButton;
                }
            });
        });
        
        // Compact/Expanded View Toggle
        const kanbanViewToggle = document.getElementById('kanbanViewToggle');
        let isCompactView = false;
        
        if (kanbanViewToggle) {
            kanbanViewToggle.addEventListener('click', function() {
                isCompactView = !isCompactView;
                document.querySelectorAll('.task-card').forEach(card => {
                    if (isCompactView) {
                        card.classList.add('task-card-compact');
                        const description = card.querySelector('.task-card-description');
                        if (description) description.style.display = 'none';
                    } else {
                        card.classList.remove('task-card-compact');
                        const description = card.querySelector('.task-card-description');
                        if (description) description.style.display = '';
                    }
                });
                
                this.innerHTML = isCompactView 
                    ? '<i class="fas fa-expand-alt me-1"></i>Expanded'
                    : '<i class="fas fa-compress-alt me-1"></i>Compact';
                
                saveFilterPreferences();
            });
            
            // Load saved view settings
            @if(isset($viewSettings) && isset($viewSettings['compact']))
                if (@json($viewSettings['compact'])) {
                    isCompactView = true;
                    kanbanViewToggle.click();
                }
            @endif
        }
        
        // Task Context Menu
        let currentContextTaskId = null;
        
        window.showTaskContextMenu = function(event, taskId) {
            event.preventDefault();
            event.stopPropagation();
            currentContextTaskId = taskId;
            
            const card = document.querySelector(`.task-card[data-task-id="${taskId}"]`);
            if (!card) return;
            
            const contextMenu = document.getElementById('taskContextMenu');
            if (!contextMenu) return;
            
            // Get viewport dimensions (for position: fixed, we use viewport coordinates)
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            
            // Use clientX/clientY directly (already relative to viewport for position: fixed)
            let menuX = event.clientX;
            let menuY = event.clientY;
            
            // Get menu dimensions (after it's displayed)
            contextMenu.style.display = 'block';
            const menuWidth = contextMenu.offsetWidth;
            const menuHeight = contextMenu.offsetHeight;
            
            // Adjust position if menu would go off-screen
            // Check right edge
            if (menuX + menuWidth > viewportWidth) {
                menuX = viewportWidth - menuWidth - 10;
            }
            
            // Check bottom edge
            if (menuY + menuHeight > viewportHeight) {
                menuY = viewportHeight - menuHeight - 10;
            }
            
            // Check left edge
            if (menuX < 0) {
                menuX = 10;
            }
            
            // Check top edge
            if (menuY < 0) {
                menuY = 10;
            }
            
            // Set menu position (using viewport coordinates for position: fixed)
            contextMenu.style.left = menuX + 'px';
            contextMenu.style.top = menuY + 'px';
            
            // Update menu links
            const viewLink = document.getElementById('contextMenuView');
            const editLink = document.getElementById('contextMenuEdit');
            const duplicateLink = document.getElementById('contextMenuDuplicate');
            const archiveLink = document.getElementById('contextMenuArchive');
            
            if (viewLink) viewLink.href = card.dataset.taskUrl;
            if (editLink) editLink.href = card.dataset.taskEditUrl;
            if (duplicateLink) duplicateLink.href = '#';
            if (archiveLink) archiveLink.href = '#';
            
            // Populate status options
            const statusesContainer = document.getElementById('contextMenuStatuses');
            if (statusesContainer) {
                statusesContainer.innerHTML = '';
                statusesConfig.forEach(status => {
                    const item = document.createElement('a');
                    item.className = 'list-group-item list-group-item-action';
                    item.href = '#';
                    item.innerHTML = `<i class="fas fa-circle me-2" style="color: ${status.color};"></i>${status.name}`;
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        changeTaskStatus(taskId, status.id);
                        hideTaskContextMenu();
                    });
                    statusesContainer.appendChild(item);
                });
            }
            
            // Handle duplicate
            if (duplicateLink) {
                duplicateLink.onclick = function(e) {
                    e.preventDefault();
                    duplicateTask(taskId);
                    hideTaskContextMenu();
                };
            }
            
            // Handle archive
            if (archiveLink) {
                archiveLink.onclick = function(e) {
                    e.preventDefault();
                    if (confirm('Are you sure you want to archive this task?')) {
                        archiveTask(taskId);
                    }
                    hideTaskContextMenu();
                };
            }
        };
        
        function hideTaskContextMenu() {
            const contextMenu = document.getElementById('taskContextMenu');
            if (contextMenu) {
                contextMenu.style.display = 'none';
            }
        }
        
        function changeTaskStatus(taskId, statusId) {
            fetch('{{ route("drives.projects.projects.tasks.update-status", [$drive, $project, ":task"]) }}'.replace(':task', taskId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    status_id: Number(statusId),
                    sort_order: 0
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        
        function duplicateTask(taskId) {
            const card = document.querySelector(`.task-card[data-task-id="${taskId}"]`);
            if (!card || !card.dataset.taskDuplicateUrl) return;
            
            const originalTask = taskData[taskId];
            if (!originalTask) return;
            
            fetch(card.dataset.taskDuplicateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.task) {
                    const newTask = data.task;
                    const statusId = newTask.status ? newTask.status.id : originalTask.status.id;
                    const column = document.getElementById('kanban-status-' + statusId);
                    
                    if (column) {
                        // Add new task to taskData
                        taskData[newTask.id] = {
                            id: newTask.id,
                            title: newTask.title,
                            description: newTask.description || '',
                            priority: newTask.priority,
                            status: newTask.status,
                            labels: newTask.labels || [],
                            label_ids: newTask.labels.map(l => l.id) || [],
                            members: newTask.members || [],
                            member_ids: newTask.members.map(m => m.id) || [],
                            owner: newTask.owner,
                            owner_id: newTask.owner_id,
                            url: newTask.url,
                            edit_url: newTask.edit_url,
                            update_url: '{{ route("drives.projects.projects.tasks.update-labels-members", [$drive, $project, ":task"]) }}'.replace(':task', newTask.id),
                            comment_url: '{{ route("drives.projects.projects.tasks.comments.store", [$drive, $project, ":task"]) }}'.replace(':task', newTask.id),
                            dependency_url: '{{ route("drives.projects.projects.tasks.dependencies.store", [$drive, $project, ":task"]) }}'.replace(':task', newTask.id),
                            blocked_by: [],
                            blocks: [],
                            related: [],
                            created_at: new Date().toLocaleDateString(),
                            is_overdue: false,
                        };
                        
                        // Create new card element
                        const newCard = document.createElement('div');
                        newCard.className = 'task-card';
                        newCard.dataset.taskId = newTask.id;
                        newCard.dataset.taskTitle = newTask.title;
                        newCard.dataset.taskUrl = newTask.url;
                        newCard.dataset.taskEditUrl = newTask.edit_url;
                        newCard.dataset.taskDuplicateUrl = '{{ route("drives.projects.projects.tasks.duplicate", [$drive, $project, ":task"]) }}'.replace(':task', newTask.id);
                        newCard.dataset.taskArchiveUrl = '{{ route("drives.projects.projects.tasks.archive", [$drive, $project, ":task"]) }}'.replace(':task', newTask.id);
                        newCard.dataset.statusId = statusId;
                        newCard.dataset.statusSlug = newTask.status ? newTask.status.slug : '';
                        newCard.dataset.statusName = newTask.status ? newTask.status.name : '';
                        newCard.dataset.statusColor = newTask.status ? newTask.status.color : '';
                        newCard.dataset.priority = newTask.priority;
                        newCard.dataset.labelIds = newTask.labels.map(l => l.id).join(',');
                        newCard.dataset.memberIds = newTask.members.map(m => m.id).join(',');
                        newCard.dataset.ownerId = newTask.owner_id || '';
                        newCard.draggable = true;
                        newCard.oncontextmenu = function(e) {
                            e.preventDefault();
                            showTaskContextMenu(e, newTask.id);
                        };
                        
                        const priorityColors = {
                            'urgent': '#dc3545',
                            'high': '#ffc107',
                            'medium': '#0dcaf0',
                            'low': '#6c757d'
                        };
                        newCard.style.borderLeft = `4px solid ${priorityColors[newTask.priority] || '#6c757d'}`;
                        
                        // Build card HTML
                        let cardHtml = '';
                        if (newTask.description) {
                            let plainText = newTask.description.replace(/<br\s*\/?>/gi, '\n');
                            plainText = plainText.replace(/<\/p>|<\/div>/gi, '\n');
                            plainText = plainText.replace(/<[^>]+>/g, '');
                            plainText = plainText.trim();
                            if (plainText) {
                                cardHtml += `<div class="task-card-title">${sanitizeText(newTask.title)}</div>`;
                                cardHtml += `<div class="task-card-description">${sanitizeText(plainText.length > 100 ? plainText.substring(0, 100) + '...' : plainText)}</div>`;
                            } else {
                                cardHtml += `<div class="task-card-title">${sanitizeText(newTask.title)}</div>`;
                            }
                        } else {
                            cardHtml += `<div class="task-card-title">${sanitizeText(newTask.title)}</div>`;
                        }
                        
                        cardHtml += '<div class="task-card-meta">';
                        cardHtml += `<span class="badge bg-${newTask.priority === 'urgent' ? 'danger' : (newTask.priority === 'high' ? 'warning' : (newTask.priority === 'medium' ? 'info' : 'secondary'))}">`;
                        cardHtml += `<i class="fas fa-flag me-1"></i>${newTask.priority.charAt(0).toUpperCase() + newTask.priority.slice(1)}</span>`;
                        newTask.labels.slice(0, 3).forEach(label => {
                            cardHtml += `<span class="badge" style="background-color: ${label.color}; color: white;">${sanitizeText(label.name)}</span>`;
                        });
                        if (newTask.labels.length > 3) {
                            cardHtml += `<span class="badge bg-secondary">+${newTask.labels.length - 3}</span>`;
                        }
                        cardHtml += '</div>';
                        
                        cardHtml += '<div class="task-card-footer">';
                        cardHtml += '<div class="d-flex align-items-center gap-2">';
                        if (newTask.members.length > 0) {
                            cardHtml += `<small class="text-muted"><i class="fas fa-users me-1"></i>${newTask.members.length}</small>`;
                        }
                        cardHtml += '</div>';
                        if (newTask.status) {
                            cardHtml += `<div class="d-flex align-items-center gap-1">`;
                            cardHtml += `<span class="badge task-status-badge" style="background-color: ${newTask.status.color}; color: #fff;">${sanitizeText(newTask.status.name)}</span>`;
                            cardHtml += '</div>';
                        }
                        cardHtml += '</div>';
                        
                        newCard.innerHTML = cardHtml;
                        
                        // Insert after the original card
                        card.after(newCard);
                        
                        // Hide empty state
                        const emptyState = document.getElementById('empty-status-' + statusId);
                        if (emptyState) {
                            emptyState.style.display = 'none';
                        }
                        
                        // Update column count
                        updateColumnTaskCount(statusId);
                        
                        // Make card clickable
                        newCard.addEventListener('click', function() {
                            openTaskSidebar(newTask.id);
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        
        function archiveTask(taskId) {
            const card = document.querySelector(`.task-card[data-task-id="${taskId}"]`);
            if (!card || !card.dataset.taskArchiveUrl) return;
            
            fetch(card.dataset.taskArchiveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove card from DOM
                    const statusId = card.dataset.statusId;
                    card.remove();
                    
                    // Update column count
                    updateColumnTaskCount(statusId);
                    
                    // Show empty state if column is now empty
                    const column = document.getElementById('kanban-status-' + statusId);
                    if (column) {
                        const cards = column.querySelectorAll('.task-card');
                        const emptyState = document.getElementById('empty-status-' + statusId);
                        if (cards.length === 0 && emptyState) {
                            emptyState.style.display = 'block';
                        }
                    }
                    
                    // Remove from taskData
                    delete taskData[taskId];
                    
                    // Close sidebar if this task was open
                    if (currentTaskId === taskId) {
                        sidebar.classList.remove('active');
                        overlay.classList.remove('active');
                        document.body.style.overflow = '';
                        currentTaskId = null;
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        
        // Close context menu on click outside
        document.addEventListener('click', function(e) {
            const contextMenu = document.getElementById('taskContextMenu');
            if (contextMenu && !contextMenu.contains(e.target)) {
                hideTaskContextMenu();
            }
        });
        
        // Close context menu on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideTaskContextMenu();
            }
        });
        
        // Task sidebar functionality
        const sidebar = document.getElementById('taskSidebar');
        const overlay = document.getElementById('taskSidebarOverlay');
        const closeBtn = document.getElementById('taskSidebarClose');
        const fullViewBtn = document.getElementById('taskSidebarFullView');
        const editBtn = document.getElementById('taskSidebarEdit');
        
        @php
            $canEdit = $drive->canEdit(auth()->user());
        @endphp
        const canEdit = @json($canEdit);
        
        @php
            // Optimize task data loading with selective eager loading
            $taskData = $project->tasks()
                ->whereNull('deleted_at')
                ->with([
                    'comments' => function($query) {
                        $query->with(['user:id,name', 'replies.user:id,name']);
                    },
                    'dependsOn:id,title,task_status_id',
                    'dependsOn.status:id,name,color',
                    'blocks:id,title,task_status_id',
                    'blocks.status:id,name,color',
                    'blockedBy:id,title,task_status_id',
                    'blockedBy.status:id,name,color',
                    'customFieldValues.fieldDefinition:id,name,type',
                    'parent:id,title,task_status_id',
                    'parent.status:id,name,color'
                ])
                ->get()
                ->mapWithKeys(function ($task) use ($drive, $project, $customFieldDefinitions) {
                    $status = $task->status
                        ? [
                            'id' => $task->status->id,
                            'slug' => $task->status->slug,
                            'name' => $task->status->name,
                            'color' => $task->status->color,
                            'is_completed' => (bool) $task->status->is_completed,
                        ]
                        : null;

                    // Map custom field values
                    $customFields = [];
                    if ($customFieldDefinitions) {
                        foreach ($customFieldDefinitions as $fieldDef) {
                            $value = $task->customFieldValues->firstWhere('field_definition_id', $fieldDef->id);
                            if ($value && $value->value) {
                                $customFields[$fieldDef->id] = [
                                    'id' => $fieldDef->id,
                                    'name' => $fieldDef->name,
                                    'type' => $fieldDef->type,
                                    'value' => $value->value,
                                ];
                            }
                        }
                    }

                    return [
                        $task->id => [
                            'id' => $task->id,
                            'title' => $task->title,
                            'description' => $task->description ?? '',
                            'status' => $status,
                            'priority' => $task->priority,
                            'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
                            'start_date' => $task->start_date ? $task->start_date->format('Y-m-d') : null,
                            'estimated_hours' => $task->estimated_hours,
                            'actual_hours' => $task->actual_hours,
                            'owner' => $task->owner ? $task->owner->name : null,
                            'owner_id' => $task->owner_id,
                            'parent_id' => $task->parent_id,
                            'parent' => $task->parent ? [
                                'id' => $task->parent->id,
                                'title' => $task->parent->title,
                                'status' => $task->parent->status ? [
                                    'name' => $task->parent->status->name,
                                    'color' => $task->parent->status->color,
                                ] : null,
                            ] : null,
                            'member_ids' => $task->members->pluck('id')->toArray(),
                            'members' => $task->members->map(function ($member) {
                                return [
                                    'id' => $member->id,
                                    'name' => $member->name,
                                ];
                            })->toArray(),
                            'label_ids' => $task->labels->pluck('id')->toArray(),
                            'labels' => $task->labels->map(function ($label) {
                                return [
                                    'id' => $label->id,
                                    'name' => $label->name,
                                    'color' => $label->color,
                                ];
                            })->toArray(),
                            'custom_fields' => $customFields,
                            'comments' => $task->comments->map(function ($comment) {
                                return [
                                    'id' => $comment->id,
                                    'comment' => $comment->comment,
                                    'comment_html' => $comment->comment_html ?? nl2br(e($comment->comment)),
                                    'user' => [
                                        'id' => $comment->user->id,
                                        'name' => $comment->user->name,
                                    ],
                                    'created_at' => $comment->created_at->diffForHumans(),
                                    'created_at_full' => $comment->created_at->format('M d, Y g:i A'),
                                    'replies' => $comment->replies->map(function ($reply) {
                                        return [
                                            'id' => $reply->id,
                                            'comment' => $reply->comment,
                                            'comment_html' => $reply->comment_html ?? nl2br(e($reply->comment)),
                                            'user' => [
                                                'id' => $reply->user->id,
                                                'name' => $reply->user->name,
                                            ],
                                            'created_at' => $reply->created_at->diffForHumans(),
                                            'created_at_full' => $reply->created_at->format('M d, Y g:i A'),
                                        ];
                                    })->toArray(),
                                ];
                            })->toArray(),
                            'url' => route('drives.projects.projects.tasks.show', [$drive, $project, $task]),
                            'edit_url' => route('drives.projects.projects.tasks.edit', [$drive, $project, $task]),
                            'update_url' => route('drives.projects.projects.tasks.update-labels-members', [$drive, $project, $task]),
                            'comment_url' => route('drives.projects.projects.tasks.comments.store', [$drive, $project, $task]),
                            'dependency_url' => route('drives.projects.projects.tasks.dependencies.store', [$drive, $project, $task]),
                            'blocked_by' => $task->blockedBy->map(function ($dep) {
                                return [
                                    'id' => $dep->id,
                                    'title' => $dep->title,
                                    'status' => $dep->status ? [
                                        'name' => $dep->status->name,
                                        'color' => $dep->status->color,
                                    ] : null,
                                ];
                            })->toArray(),
                            'blocks' => $task->blocks->map(function ($dep) {
                                return [
                                    'id' => $dep->id,
                                    'title' => $dep->title,
                                    'status' => $dep->status ? [
                                        'name' => $dep->status->name,
                                        'color' => $dep->status->color,
                                    ] : null,
                                ];
                            })->toArray(),
                            'related' => $task->relatedTasks->map(function ($dep) {
                                return [
                                    'id' => $dep->id,
                                    'title' => $dep->title,
                                    'status' => $dep->status ? [
                                        'name' => $dep->status->name,
                                        'color' => $dep->status->color,
                                    ] : null,
                                ];
                            })->toArray(),
                            'created_at' => $task->created_at->format('M d, Y'),
                            'is_overdue' => (bool) $task->isOverdue(),
                        ],
                    ];
                })
                ->toArray();
            
            // Get custom field definitions for display
            $availableCustomFields = $customFieldDefinitions->where('is_active', true)->map(function ($fieldDef) {
                return [
                    'id' => $fieldDef->id,
                    'name' => $fieldDef->name,
                    'type' => $fieldDef->type,
                    'options' => $fieldDef->options,
                ];
            })->toArray();
            
            $availableLabels = $labels->map(function ($label) {
                return [
                    'id' => $label->id,
                    'name' => $label->name,
                    'color' => $label->color,
                ];
            })->toArray();
            
            $availableMembers = $driveMembers->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                ];
            })->toArray();
        @endphp

        // Task data storage
        const taskData = @json($taskData);
        const availableLabels = @json($availableLabels);
        const availableMembers = @json($availableMembers);
        const availableCustomFields = @json($availableCustomFields ?? []);
        let currentTaskId = null;
        let isEditMode = false;
        
        window.openTaskSidebar = function(taskId) {
            const task = taskData[taskId];
            if (!task) return;
            
            currentTaskId = taskId;
            isEditMode = false;
            
            // Update sidebar title
            document.getElementById('taskSidebarTitle').textContent = task.title;
            
            // Update links
            fullViewBtn.href = task.url;
            if (editBtn) {
            editBtn.href = task.edit_url;
            
                // Reset edit button
                editBtn.innerHTML = '<i class="fas fa-edit me-2"></i>Edit';
                editBtn.classList.remove('btn-primary');
                editBtn.classList.add('btn-outline-secondary');
                editBtn.onclick = function(e) {
                    e.preventDefault();
                    toggleEditMode();
                };
            }
            
            renderSidebarContent(task, false);
            
            // Show sidebar
            sidebar.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        };
        
        function renderSidebarContent(task, editMode) {
            let html = '';
            
            // Status and Priority
            html += '<div class="task-sidebar-section">';
            html += '<div class="d-flex gap-2 mb-3">';
            if (task.status && task.status.name) {
                html += `<span class="badge fs-6" style="background-color: ${task.status.color}; color: #fff;">`;
                html += `${sanitizeText(task.status.name)}</span>`;
            }
            if (editMode && canEdit) {
                html += '<select class="form-select form-select-sm" id="taskPrioritySelect" style="width: auto;" onchange="updateTaskPriority()">';
                html += `<option value="low" ${task.priority === 'low' ? 'selected' : ''}>Low</option>`;
                html += `<option value="medium" ${task.priority === 'medium' ? 'selected' : ''}>Medium</option>`;
                html += `<option value="high" ${task.priority === 'high' ? 'selected' : ''}>High</option>`;
                html += `<option value="urgent" ${task.priority === 'urgent' ? 'selected' : ''}>Urgent</option>`;
                html += '</select>';
            } else {
            html += `<span class="badge bg-${task.priority === 'urgent' ? 'danger' : (task.priority === 'high' ? 'warning' : (task.priority === 'medium' ? 'info' : 'secondary'))} fs-6">`;
            html += `${task.priority.charAt(0).toUpperCase() + task.priority.slice(1)}</span>`;
            }
            html += '</div>';
            html += '</div>';
            
            // Description
            html += '<div class="task-sidebar-section">';
            html += '<div class="task-sidebar-section-title">Description</div>';
            if (editMode && canEdit) {
                html += '<div id="taskDescriptionEditor" style="min-height: 150px;"></div>';
                html += '<input type="hidden" id="taskDescriptionHidden" value="">';
            } else {
                if (task.description) {
                    html += '<div class="task-description-container">';
                    html += sanitizeHtml(task.description);
                    html += '</div>';
                } else {
                    html += '<p class="text-muted small mb-0">No description</p>';
                }
            }
            html += '</div>';
            
            // Start Date
            if (task.start_date) {
                html += '<div class="task-sidebar-section">';
                html += '<div class="task-sidebar-section-title">Start Date</div>';
                const startDate = new Date(task.start_date + 'T00:00:00');
                const formattedStartDate = startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                html += `<p style="color: var(--text-color);">${formattedStartDate}</p>`;
                html += '</div>';
            }
            
            // Due Date
            if (task.due_date) {
                html += '<div class="task-sidebar-section">';
                html += '<div class="task-sidebar-section-title">Due Date</div>';
                const dueDate = new Date(task.due_date + 'T00:00:00');
                const formattedDate = dueDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                html += `<p style="color: var(--text-color);">${formattedDate}${task.is_overdue ? ' <span class="badge bg-danger">Overdue</span>' : ''}</p>`;
                html += '</div>';
            }
            
            // Time Tracking
            if (task.estimated_hours || task.actual_hours) {
                html += '<div class="task-sidebar-section">';
                html += '<div class="task-sidebar-section-title">Time Tracking</div>';
                html += '<p style="color: var(--text-color);">';
                if (task.estimated_hours) {
                    html += `<strong>Estimated:</strong> ${task.estimated_hours}h`;
                    if (task.actual_hours) {
                        html += '<br>';
                    }
                }
                if (task.actual_hours) {
                    html += `<strong>Actual:</strong> ${task.actual_hours}h`;
                }
                html += '</p>';
                html += '</div>';
            }
            
            // Owner
            if (task.owner) {
                html += '<div class="task-sidebar-section">';
                html += '<div class="task-sidebar-section-title">Owner</div>';
                html += `<p style="color: var(--text-color);">${sanitizeText(task.owner)}</p>`;
                html += '</div>';
            }
            
            // Parent Task
            if (task.parent) {
                html += '<div class="task-sidebar-section">';
                html += '<div class="task-sidebar-section-title">Parent Task</div>';
                html += '<p style="color: var(--text-color);">';
                html += `<a href="#" onclick="openTaskSidebar(${task.parent.id}); return false;" class="text-decoration-none" style="color: var(--text-color);">`;
                if (task.parent.status) {
                    html += `<span class="badge me-2" style="background-color: ${task.parent.status.color}; color: #fff;">${sanitizeText(task.parent.status.name)}</span>`;
                }
                html += `${sanitizeText(task.parent.title)}</a>`;
                html += '</p>';
                html += '</div>';
            }
            
            // Custom Fields
            if (task.custom_fields && Object.keys(task.custom_fields).length > 0) {
                html += '<div class="task-sidebar-section">';
                html += '<div class="task-sidebar-section-title">Custom Fields</div>';
                html += '<div class="mt-2">';
                Object.values(task.custom_fields).forEach(field => {
                    html += '<div class="mb-2">';
                    html += `<strong class="small">${sanitizeText(field.name)}:</strong> `;
                    html += '<div class="small" style="color: var(--text-color);">';
                    if (field.type === 'checkbox') {
                        html += field.value == '1' ? 'Yes' : 'No';
                    } else if (field.type === 'date') {
                        const date = new Date(field.value + 'T00:00:00');
                        html += date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                    } else {
                        html += sanitizeText(field.value);
                    }
                    html += '</div>';
                    html += '</div>';
                });
                html += '</div>';
                html += '</div>';
            }
            
            // Labels
            html += '<div class="task-sidebar-section" id="labelsSection">';
            html += '<div class="d-flex justify-content-between align-items-center mb-2">';
            html += '<div class="task-sidebar-section-title mb-0">Labels</div>';
            if (editMode && canEdit) {
                html += '<button type="button" class="btn btn-sm btn-outline-primary" onclick="addLabelToTask()">';
                html += '<i class="fas fa-plus me-1"></i>Add';
                html += '</button>';
            }
            html += '</div>';
            if (editMode && canEdit) {
                html += '<div class="d-flex flex-wrap gap-1 mb-2" id="labelsEditContainer">';
                task.labels.forEach(label => {
                    html += `<span class="badge d-inline-flex align-items-center gap-1" style="background-color: ${label.color}; color: white;">`;
                    html += `${sanitizeText(label.name)}`;
                    html += `<button type="button" class="btn-close btn-close-white" style="font-size: 0.6rem;" onclick="removeLabelFromTask(${label.id})" aria-label="Remove"></button>`;
                    html += `</span>`;
                });
                html += '</div>';
                html += '<select class="form-select form-select-sm" id="labelSelect" onchange="selectLabelToAdd()">';
                html += '<option value="">Select a label...</option>';
                const currentLabelIds = task.label_ids || [];
                availableLabels.forEach(label => {
                    if (!currentLabelIds.includes(label.id)) {
                        html += `<option value="${label.id}" data-name="${sanitizeText(label.name)}" data-color="${label.color}">${sanitizeText(label.name)}</option>`;
                    }
                });
                html += '</select>';
            } else {
            if (task.labels.length > 0) {
                html += '<div class="d-flex flex-wrap gap-1">';
                task.labels.forEach(label => {
                    html += `<span class="badge" style="background-color: ${label.color}; color: white;">${sanitizeText(label.name)}</span>`;
                });
                html += '</div>';
                } else {
                    html += '<p class="text-muted small mb-0">No labels assigned</p>';
                }
            }
                html += '</div>';
            
            // Members
            html += '<div class="task-sidebar-section" id="membersSection">';
            html += '<div class="d-flex justify-content-between align-items-center mb-2">';
            html += '<div class="task-sidebar-section-title mb-0">Assigned Members</div>';
            if (editMode && canEdit) {
                html += '<button type="button" class="btn btn-sm btn-outline-primary" onclick="addMemberToTask()">';
                html += '<i class="fas fa-plus me-1"></i>Add';
                html += '</button>';
            }
            html += '</div>';
            if (editMode && canEdit) {
                html += '<div class="d-flex flex-wrap gap-1 mb-2" id="membersEditContainer">';
                task.members.forEach(member => {
                    html += `<span class="badge bg-secondary d-inline-flex align-items-center gap-1">`;
                    html += `${sanitizeText(member.name)}`;
                    html += `<button type="button" class="btn-close btn-close-white" style="font-size: 0.6rem;" onclick="removeMemberFromTask(${member.id})" aria-label="Remove"></button>`;
                    html += `</span>`;
                });
                html += '</div>';
                html += '<select class="form-select form-select-sm" id="memberSelect" onchange="selectMemberToAdd()">';
                html += '<option value="">Select a member...</option>';
                const currentMemberIds = task.member_ids || [];
                availableMembers.forEach(member => {
                    if (!currentMemberIds.includes(member.id)) {
                        html += `<option value="${member.id}" data-name="${sanitizeText(member.name)}">${sanitizeText(member.name)}</option>`;
                    }
                });
                html += '</select>';
            } else {
                if (task.members.length > 0) {
                    html += '<div class="d-flex flex-wrap gap-1">';
                    task.members.forEach(member => {
                        html += `<span class="badge bg-secondary">${sanitizeText(member.name)}</span>`;
                    });
                    html += '</div>';
                } else {
                    html += '<p class="text-muted small mb-0">No members assigned</p>';
                }
            }
            html += '</div>';
            
            // Created Date
            html += '<div class="task-sidebar-section">';
            html += '<div class="task-sidebar-section-title">Created</div>';
            html += `<p class="text-muted small">${sanitizeText(task.created_at)}</p>`;
            html += '</div>';
            
            // Dependencies Section
            if (task.blocked_by && task.blocked_by.length > 0 || task.blocks && task.blocks.length > 0 || task.related && task.related.length > 0) {
                html += '<div class="task-sidebar-section" id="dependenciesSection">';
                html += '<div class="d-flex justify-content-between align-items-center mb-2">';
                html += '<div class="task-sidebar-section-title mb-0">Dependencies</div>';
                if (editMode && canEdit) {
                    html += '<button type="button" class="btn btn-sm btn-outline-primary" onclick="showAddDependencyModal()">';
                    html += '<i class="fas fa-plus me-1"></i>Add';
                    html += '</button>';
                }
                html += '</div>';
                
                if (task.blocked_by && task.blocked_by.length > 0) {
                    html += '<div class="mb-2">';
                    html += '<small class="text-muted d-block mb-1">Blocked By:</small>';
                    task.blocked_by.forEach(dep => {
                        html += '<div class="d-flex align-items-center justify-content-between mb-1 p-2 rounded" style="background-color: var(--bg-primary);">';
                        html += '<a href="#" onclick="openTaskSidebar(' + dep.id + '); return false;" class="text-decoration-none">';
                        html += '<span class="badge me-2" style="background-color: ' + (dep.status ? dep.status.color : '#6c757d') + ';">' + (dep.status ? dep.status.name : '') + '</span>';
                        html += sanitizeText(dep.title);
                        html += '</a>';
                        if (editMode && canEdit) {
                            html += '<button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeDependency(' + dep.id + ', \'blocked_by\')">';
                            html += '<i class="fas fa-times"></i>';
                            html += '</button>';
                        }
                        html += '</div>';
                    });
                    html += '</div>';
                }
                
                if (task.blocks && task.blocks.length > 0) {
                    html += '<div class="mb-2">';
                    html += '<small class="text-muted d-block mb-1">Blocks:</small>';
                    task.blocks.forEach(dep => {
                        html += '<div class="d-flex align-items-center justify-content-between mb-1 p-2 rounded" style="background-color: var(--bg-primary);">';
                        html += '<a href="#" onclick="openTaskSidebar(' + dep.id + '); return false;" class="text-decoration-none">';
                        html += '<span class="badge me-2" style="background-color: ' + (dep.status ? dep.status.color : '#6c757d') + ';">' + (dep.status ? dep.status.name : '') + '</span>';
                        html += sanitizeText(dep.title);
                        html += '</a>';
                        if (editMode && canEdit) {
                            html += '<button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeDependency(' + dep.id + ', \'blocks\')">';
                            html += '<i class="fas fa-times"></i>';
                            html += '</button>';
                        }
                        html += '</div>';
                    });
                    html += '</div>';
                }
                
                if (task.related && task.related.length > 0) {
                    html += '<div class="mb-2">';
                    html += '<small class="text-muted d-block mb-1">Related:</small>';
                    task.related.forEach(dep => {
                        html += '<div class="d-flex align-items-center justify-content-between mb-1 p-2 rounded" style="background-color: var(--bg-primary);">';
                        html += '<a href="#" onclick="openTaskSidebar(' + dep.id + '); return false;" class="text-decoration-none">';
                        html += '<span class="badge me-2" style="background-color: ' + (dep.status ? dep.status.color : '#6c757d') + ';">' + (dep.status ? dep.status.name : '') + '</span>';
                        html += sanitizeText(dep.title);
                        html += '</a>';
                        if (editMode && canEdit) {
                            html += '<button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeDependency(' + dep.id + ', \'related\')">';
                            html += '<i class="fas fa-times"></i>';
                            html += '</button>';
                        }
                        html += '</div>';
                    });
                    html += '</div>';
                }
                
                if (editMode && canEdit && (!task.blocked_by || task.blocked_by.length === 0) && (!task.blocks || task.blocks.length === 0) && (!task.related || task.related.length === 0)) {
                    html += '<p class="text-muted small mb-0">No dependencies. Click "Add" to link related tasks.</p>';
                }
                html += '</div>';
            } else if (editMode && canEdit) {
                html += '<div class="task-sidebar-section" id="dependenciesSection">';
                html += '<div class="d-flex justify-content-between align-items-center mb-2">';
                html += '<div class="task-sidebar-section-title mb-0">Dependencies</div>';
                html += '<button type="button" class="btn btn-sm btn-outline-primary" onclick="showAddDependencyModal()">';
                html += '<i class="fas fa-plus me-1"></i>Add';
                html += '</button>';
                html += '</div>';
                html += '<p class="text-muted small mb-0">No dependencies. Click "Add" to link related tasks.</p>';
                html += '</div>';
            }
            
            // Comments Section
            html += '<div class="task-sidebar-section" id="commentsSection">';
            html += '<div class="task-sidebar-section-title">Comments</div>';
            
            // Comment Form
            if (canEdit) {
                html += '<form id="commentForm" class="mb-3">';
                html += '<div class="mb-2">';
                html += '<textarea class="form-control form-control-sm" id="commentText" rows="3" placeholder="Type @username to mention someone..." required></textarea>';
                html += '<small class="text-muted">Type @username to mention drive members</small>';
                html += '</div>';
                html += '<button type="submit" class="btn btn-primary btn-sm">';
                html += '<i class="fas fa-comment me-1"></i>Post Comment';
                html += '</button>';
                html += '</form>';
            }
            
            // Comments List
            html += '<div id="commentsList">';
            if (task.comments && task.comments.length > 0) {
                task.comments.forEach(comment => {
                    html += renderComment(comment);
                });
            } else {
                html += '<p class="text-muted small mb-0">No comments yet. Be the first to comment!</p>';
            }
            html += '</div>';
            html += '</div>';
            
            document.getElementById('taskSidebarContent').innerHTML = html;
            
            // Attach comment form handler after a brief delay to ensure DOM is ready
            if (canEdit) {
                setTimeout(() => {
                    const commentForm = document.getElementById('commentForm');
                    if (commentForm) {
                        commentForm.addEventListener('submit', function(e) {
                            e.preventDefault();
                            submitComment();
                        });
                    }
                }, 10);
            }
        }
        
        function renderComment(comment, parentId = null) {
            const isReply = parentId !== null;
            let html = '<div class="comment mb-3 pb-3 border-bottom" id="comment-' + comment.id + '">';
            html += '<div class="d-flex">';
            html += '<div class="flex-shrink-0">';
            html += '<div class="' + (isReply ? 'bg-secondary' : 'bg-primary') + ' rounded-circle d-flex align-items-center justify-content-center" style="width: ' + (isReply ? '24' : '32') + 'px; height: ' + (isReply ? '24' : '32') + 'px; font-size: ' + (isReply ? '0.7' : '0.8') + 'rem;">';
            html += '<span style="color: white;">' + sanitizeText(comment.user.name.charAt(0)) + '</span>';
            html += '</div>';
            html += '</div>';
            html += '<div class="flex-grow-1 ms-2">';
            html += '<div class="d-flex justify-content-between align-items-start mb-1">';
            html += '<div>';
            html += '<strong class="small" style="color: var(--text-color);">' + sanitizeText(comment.user.name) + '</strong>';
            html += '<small class="text-muted ms-2">' + sanitizeText(comment.created_at) + '</small>';
            html += '</div>';
            html += '</div>';
            html += '<div class="small" style="color: var(--text-color);">';
            html += sanitizeHtml(comment.comment_html || comment.comment);
            html += '</div>';
            
            // Reply button (only for top-level comments)
            if (!isReply && canEdit) {
                html += '<div class="mt-2">';
                html += '<button class="btn btn-sm btn-link text-muted p-0" onclick="showReplyForm(' + comment.id + ')">';
                html += '<i class="fas fa-reply me-1"></i>Reply';
                html += '</button>';
                html += '</div>';
            }
            
            // Replies
            if (comment.replies && comment.replies.length > 0) {
                html += '<div class="replies mt-2 ms-3">';
                comment.replies.forEach(reply => {
                    html += renderComment(reply, comment.id);
                });
                html += '</div>';
            }
            
            // Reply form (hidden by default)
            if (!isReply && canEdit) {
                html += '<div class="reply-form d-none mt-2 ms-3" id="reply-form-' + comment.id + '">';
                html += '<form onsubmit="submitReply(event, ' + comment.id + ')">';
                html += '<textarea class="form-control form-control-sm mb-2" id="reply-text-' + comment.id + '" rows="2" placeholder="Reply to ' + sanitizeText(comment.user.name) + '..." required></textarea>';
                html += '<div class="d-flex gap-2">';
                html += '<button type="submit" class="btn btn-sm btn-primary">Reply</button>';
                html += '<button type="button" class="btn btn-sm btn-secondary" onclick="hideReplyForm(' + comment.id + ')">Cancel</button>';
                html += '</div>';
                html += '</form>';
                html += '</div>';
            }
            
            html += '</div>';
            html += '</div>';
            html += '</div>';
            return html;
        }
        
        function showReplyForm(commentId) {
            const replyForm = document.getElementById('reply-form-' + commentId);
            if (replyForm) {
                replyForm.classList.remove('d-none');
                const textarea = document.getElementById('reply-text-' + commentId);
                if (textarea) {
                    textarea.focus();
                }
            }
        }
        
        function hideReplyForm(commentId) {
            const replyForm = document.getElementById('reply-form-' + commentId);
            if (replyForm) {
                replyForm.classList.add('d-none');
                const textarea = document.getElementById('reply-text-' + commentId);
                if (textarea) {
                    textarea.value = '';
                }
            }
        }
        
        function submitReply(event, parentCommentId) {
            event.preventDefault();
            if (!currentTaskId) return;
            
            const task = taskData[currentTaskId];
            if (!task || !task.comment_url) return;
            
            const textarea = document.getElementById('reply-text-' + parentCommentId);
            if (!textarea || !textarea.value.trim()) return;
            
            const form = event.target;
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Disable form
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Posting...';
            
            fetch(task.comment_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    comment: textarea.value.trim(),
                    parent_id: parentCommentId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Find parent comment in task data
                    const parentComment = task.comments.find(c => c.id === parentCommentId);
                    if (parentComment) {
                        if (!parentComment.replies) parentComment.replies = [];
                        parentComment.replies.push(data.comment);
                    }
                    
                    // Hide reply form
                    hideReplyForm(parentCommentId);
                    
                    // Re-render the parent comment to show the new reply
                    const commentElement = document.getElementById('comment-' + parentCommentId);
                    if (commentElement && parentComment) {
                        // Find the replies container
                        let repliesContainer = commentElement.querySelector('.replies');
                        if (!repliesContainer) {
                            // Create replies container
                            const commentContent = commentElement.querySelector('.flex-grow-1');
                            if (commentContent) {
                                repliesContainer = document.createElement('div');
                                repliesContainer.className = 'replies mt-2 ms-3';
                                const replyButton = commentContent.querySelector('.mt-2');
                                if (replyButton) {
                                    replyButton.insertAdjacentElement('afterend', repliesContainer);
                                } else {
                                    commentContent.appendChild(repliesContainer);
                                }
                            }
                        }
                        if (repliesContainer) {
                            // Add new reply
                            const replyHtml = renderComment(data.comment, parentCommentId);
                            repliesContainer.insertAdjacentHTML('beforeend', replyHtml);
                            
                            // Scroll to new reply
                            const newReply = document.getElementById('comment-' + data.comment.id);
                            if (newReply) {
                                newReply.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                            }
                        }
                    }
                    
                    // Update comment count on card
                    updateTaskCard(currentTaskId);
                }
            })
            .catch(error => {
                console.error('Error posting reply:', error);
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        }
        
        function submitComment() {
            if (!currentTaskId) return;
            
            const task = taskData[currentTaskId];
            if (!task || !task.comment_url) return;
            
            const commentText = document.getElementById('commentText');
            if (!commentText || !commentText.value.trim()) return;
            
            const form = document.getElementById('commentForm');
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Disable form
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Posting...';
            
            fetch(task.comment_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    comment: commentText.value.trim()
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Add comment to task data
                    if (!task.comments) task.comments = [];
                    task.comments.push(data.comment);
                    
                    // Clear form
                    commentText.value = '';
                    
                    // Add comment to UI
                    const commentsList = document.getElementById('commentsList');
                    if (commentsList) {
                        // Remove "no comments" message if present
                        const noCommentsMsg = commentsList.querySelector('p.text-muted');
                        if (noCommentsMsg) {
                            noCommentsMsg.remove();
                        }
                        
                        // Add new comment
                        const commentHtml = renderComment(data.comment);
                        commentsList.insertAdjacentHTML('beforeend', commentHtml);
                        
                        // Scroll to new comment
                        const newComment = document.getElementById('comment-' + data.comment.id);
                        if (newComment) {
                            newComment.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }
                        
                        // Update comment count on card
                        updateTaskCard(currentTaskId);
                    }
                }
            })
            .catch(error => {
                console.error('Error posting comment:', error);
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        }
        
        let quillEditor = null;
        
        function toggleEditMode() {
            if (!currentTaskId || !editBtn) return;
            
            const task = taskData[currentTaskId];
            if (!task) return;
            
            if (isEditMode) {
                // Exiting edit mode - save changes
                saveTaskChanges();
                
                // Destroy Quill editor
                if (quillEditor) {
                    const editorElement = document.getElementById('taskDescriptionEditor');
                    if (editorElement) {
                        editorElement.innerHTML = '';
                    }
                    quillEditor = null;
                }
                
                editBtn.innerHTML = '<i class="fas fa-edit me-2"></i>Edit';
                editBtn.classList.remove('btn-primary');
                editBtn.classList.add('btn-outline-secondary');
                isEditMode = false;
                renderSidebarContent(task, false);
            } else {
                // Entering edit mode
                editBtn.innerHTML = '<i class="fas fa-times me-2"></i>Cancel';
                editBtn.classList.remove('btn-outline-secondary');
                editBtn.classList.add('btn-primary');
                isEditMode = true;
                renderSidebarContent(task, true);
                
                // Initialize Quill editor after a short delay to ensure DOM is ready
                setTimeout(() => {
                    initializeQuillEditor(task);
                }, 100);
            }
        }
        
        function initializeQuillEditor(task) {
            const editorElement = document.getElementById('taskDescriptionEditor');
            if (!editorElement) return;
            
            // Destroy existing editor if any
            if (quillEditor) {
                quillEditor = null;
            }
            
            quillEditor = new Quill('#taskDescriptionEditor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ header: [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['blockquote', 'code-block'],
                        ['link'],
                        ['clean']
                    ],
                },
            });
            
            // Store reference on the element for fallback access
            editorElement.__quill = quillEditor;
            
            // Set initial content
            const initialContent = task.description || '';
            if (initialContent) {
                quillEditor.root.innerHTML = initialContent;
            }
        }
        
        function addLabelToTask() {
            const select = document.getElementById('labelSelect');
            if (select && select.value) {
                selectLabelToAdd();
            }
        }
        
        function selectLabelToAdd() {
            const select = document.getElementById('labelSelect');
            if (!select || !select.value) return;
            
            const option = select.options[select.selectedIndex];
            const labelId = parseInt(select.value);
            const labelName = option.dataset.name;
            const labelColor = option.dataset.color;
            
            const task = taskData[currentTaskId];
            if (!task) return;
            
            // Add to task data
            if (!task.label_ids) task.label_ids = [];
            if (!task.labels) task.labels = [];
            
            if (!task.label_ids.includes(labelId)) {
                task.label_ids.push(labelId);
                task.labels.push({
                    id: labelId,
                    name: labelName,
                    color: labelColor
                });
            }
            
            // Update UI
            const container = document.getElementById('labelsEditContainer');
            const badge = document.createElement('span');
            badge.className = 'badge d-inline-flex align-items-center gap-1';
            badge.style.backgroundColor = labelColor;
            badge.style.color = 'white';
            badge.innerHTML = `${sanitizeText(labelName)}<button type="button" class="btn-close btn-close-white" style="font-size: 0.6rem;" onclick="removeLabelFromTask(${labelId})" aria-label="Remove"></button>`;
            container.appendChild(badge);
            
            // Remove from select
            option.remove();
            select.value = '';
            
            // Save changes
            saveLabelsAndMembers();
        }
        
        function removeLabelFromTask(labelId) {
            const task = taskData[currentTaskId];
            if (!task) return;
            
            // Remove from task data
            task.label_ids = task.label_ids.filter(id => id !== labelId);
            task.labels = task.labels.filter(label => label.id !== labelId);
            
            // Find the label in available labels and add back to select
            const label = availableLabels.find(l => l.id === labelId);
            if (label) {
                const select = document.getElementById('labelSelect');
                const option = document.createElement('option');
                option.value = label.id;
                option.dataset.name = label.name;
                option.dataset.color = label.color;
                option.textContent = label.name;
                select.appendChild(option);
            }
            
            // Update UI
            const container = document.getElementById('labelsEditContainer');
            const badge = container.querySelector(`button[onclick="removeLabelFromTask(${labelId})"]`)?.closest('.badge');
            if (badge) badge.remove();
            
            // Save changes
            saveLabelsAndMembers();
        }
        
        function addMemberToTask() {
            const select = document.getElementById('memberSelect');
            if (select && select.value) {
                selectMemberToAdd();
            }
        }
        
        function selectMemberToAdd() {
            const select = document.getElementById('memberSelect');
            if (!select || !select.value) return;
            
            const option = select.options[select.selectedIndex];
            const memberId = parseInt(select.value);
            const memberName = option.dataset.name;
            
            const task = taskData[currentTaskId];
            if (!task) return;
            
            // Add to task data
            if (!task.member_ids) task.member_ids = [];
            if (!task.members) task.members = [];
            
            if (!task.member_ids.includes(memberId)) {
                task.member_ids.push(memberId);
                task.members.push({
                    id: memberId,
                    name: memberName
                });
            }
            
            // Update UI
            const container = document.getElementById('membersEditContainer');
            const badge = document.createElement('span');
            badge.className = 'badge bg-secondary d-inline-flex align-items-center gap-1';
            badge.innerHTML = `${sanitizeText(memberName)}<button type="button" class="btn-close btn-close-white" style="font-size: 0.6rem;" onclick="removeMemberFromTask(${memberId})" aria-label="Remove"></button>`;
            container.appendChild(badge);
            
            // Remove from select
            option.remove();
            select.value = '';
            
            // Save changes
            saveLabelsAndMembers();
        }
        
        function removeMemberFromTask(memberId) {
            const task = taskData[currentTaskId];
            if (!task) return;
            
            // Remove from task data
            task.member_ids = task.member_ids.filter(id => id !== memberId);
            task.members = task.members.filter(member => member.id !== memberId);
            
            // Find the member in available members and add back to select
            const member = availableMembers.find(m => m.id === memberId);
            if (member) {
                const select = document.getElementById('memberSelect');
                const option = document.createElement('option');
                option.value = member.id;
                option.dataset.name = member.name;
                option.textContent = member.name;
                select.appendChild(option);
            }
            
            // Update UI
            const container = document.getElementById('membersEditContainer');
            const badge = container.querySelector(`button[onclick="removeMemberFromTask(${memberId})"]`)?.closest('.badge');
            if (badge) badge.remove();
            
            // Save changes
            saveLabelsAndMembers();
        }
        
        function updateTaskPriority() {
            if (!currentTaskId) return;
            
            const task = taskData[currentTaskId];
            if (!task) return;
            
            const prioritySelect = document.getElementById('taskPrioritySelect');
            if (!prioritySelect) return;
            
            const newPriority = prioritySelect.value;
            if (newPriority === task.priority) return;
            
            task.priority = newPriority;
            saveTaskChanges();
        }
        
        function saveLabelsAndMembers() {
            saveTaskChanges();
        }
        
        function saveTaskChanges() {
            if (!currentTaskId) return;
            
            const task = taskData[currentTaskId];
            if (!task || !task.update_url) return;
            
            const labelIds = task.label_ids || [];
            const memberIds = task.member_ids || [];
            const priority = task.priority || 'medium';
            
            // Get description from Quill editor if in edit mode
            let description = task.description || '';
            const editorElement = document.getElementById('taskDescriptionEditor');
            if (editorElement && quillEditor) {
                description = quillEditor.root.innerHTML.trim();
            } else if (editorElement) {
                // Fallback: try to get content directly from the editor element
                const quillInstance = editorElement.__quill;
                if (quillInstance) {
                    description = quillInstance.root.innerHTML.trim();
                }
            }
            
            fetch(task.update_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    label_ids: labelIds,
                    member_ids: memberIds,
                    priority: priority,
                    description: description
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update task data with server response
                    if (data.task.labels) {
                        task.label_ids = data.task.labels.map(l => l.id);
                        task.labels = data.task.labels;
                    }
                    if (data.task.members) {
                        task.member_ids = data.task.members.map(m => m.id);
                        task.members = data.task.members;
                    }
                    if (data.task.priority) {
                        task.priority = data.task.priority;
                    }
                    if (data.task.description !== undefined) {
                        task.description = data.task.description;
                    }
                    
                    // Update the card on the board
                    updateTaskCard(currentTaskId);
                }
            })
            .catch(error => {
                console.error('Error updating task:', error);
            });
        }
        
        function updateTaskCard(taskId) {
            const card = document.querySelector(`.task-card[data-task-id="${taskId}"]`);
            if (!card) return;
            
            const task = taskData[taskId];
            if (!task) return;
            
            // Update priority border color
            const priorityColors = {
                'urgent': '#dc3545',
                'high': '#ffc107',
                'medium': '#0dcaf0',
                'low': '#6c757d'
            };
            const priorityColor = priorityColors[task.priority] || '#6c757d';
            card.style.borderLeftColor = priorityColor;
            card.dataset.priority = task.priority;
            
            // Update description on card
            const descriptionEl = card.querySelector('.task-card-description');
            if (descriptionEl) {
                if (task.description) {
                    // Preserve HTML formatting
                    let html = task.description;
                    // Get plain text length for truncation
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    const plainText = tempDiv.textContent || tempDiv.innerText || '';
                    
                    if (plainText.length > 100) {
                        // Truncate HTML while preserving tags
                        let truncated = '';
                        let plainPos = 0;
                        let inTag = false;
                        let tagBuffer = '';
                        
                        for (let i = 0; i < html.length && plainPos < 100; i++) {
                            const char = html[i];
                            if (char === '<') {
                                inTag = true;
                                tagBuffer = char;
                            } else if (char === '>') {
                                inTag = false;
                                tagBuffer += char;
                                truncated += tagBuffer;
                                tagBuffer = '';
                            } else if (inTag) {
                                tagBuffer += char;
                            } else {
                                truncated += char;
                                plainPos++;
                            }
                        }
                        html = truncated + '...';
                    }
                    
                    descriptionEl.innerHTML = html;
                    descriptionEl.style.display = '';
                } else {
                    descriptionEl.style.display = 'none';
                }
            }
            
            // Update labels on card
            const metaContainer = card.querySelector('.task-card-meta');
            if (metaContainer) {
                // Remove existing label badges (keep priority badge)
                const priorityBadge = metaContainer.querySelector('.badge:first-child');
                metaContainer.innerHTML = '';
                if (priorityBadge) {
                    metaContainer.appendChild(priorityBadge);
                }
                
                // Add label badges
                task.labels.slice(0, 3).forEach(label => {
                    const badge = document.createElement('span');
                    badge.className = 'badge';
                    badge.style.backgroundColor = label.color;
                    badge.style.color = 'white';
                    badge.textContent = label.name;
                    metaContainer.appendChild(badge);
                });
                
                if (task.labels.length > 3) {
                    const moreBadge = document.createElement('span');
                    moreBadge.className = 'badge bg-secondary';
                    moreBadge.textContent = `+${task.labels.length - 3}`;
                    metaContainer.appendChild(moreBadge);
                }
            }
            
            // Update members count and comment count on card
            const footer = card.querySelector('.task-card-footer');
            if (footer) {
                const leftSide = footer.querySelector('.d-flex.align-items-center.gap-2');
                if (leftSide) {
                    // Update members count
                    let membersCount = leftSide.querySelector('small:has(i.fa-users)');
                    if (!membersCount) {
                        // Try alternative selector
                        membersCount = Array.from(leftSide.querySelectorAll('small')).find(el => 
                            el.querySelector('i.fa-users')
                        );
                    }
                    if (membersCount) {
                        if (task.members.length > 0) {
                            const icon = membersCount.querySelector('i');
                            membersCount.innerHTML = '';
                            membersCount.appendChild(icon);
                            membersCount.appendChild(document.createTextNode(`\u00A0${task.members.length}`));
                            membersCount.style.display = '';
                        } else {
                            membersCount.style.display = 'none';
                        }
                    }
                    
                    // Update or add comment count
                    let commentCount = leftSide.querySelector('small:has(i.fa-comment)');
                    if (!commentCount) {
                        // Try alternative selector
                        commentCount = Array.from(leftSide.querySelectorAll('small')).find(el => 
                            el.querySelector('i.fa-comment')
                        );
                    }
                    
                    const totalComments = task.comments ? task.comments.reduce((sum, comment) => {
                        return sum + 1 + (comment.replies ? comment.replies.length : 0);
                    }, 0) : 0;
                    
                    if (totalComments > 0) {
                        if (!commentCount) {
                            // Create comment count element
                            commentCount = document.createElement('small');
                            commentCount.className = 'text-muted';
                            const icon = document.createElement('i');
                            icon.className = 'fas fa-comment me-1';
                            commentCount.appendChild(icon);
                            commentCount.appendChild(document.createTextNode(totalComments));
                            leftSide.appendChild(commentCount);
                        } else {
                            // Update existing comment count
                            const icon = commentCount.querySelector('i');
                            commentCount.innerHTML = '';
                            commentCount.appendChild(icon);
                            commentCount.appendChild(document.createTextNode(`\u00A0${totalComments}`));
                            commentCount.style.display = '';
                        }
                    } else if (commentCount) {
                        commentCount.style.display = 'none';
                    }
                }
            }
        }
        
        // Dependency Management
        window.showAddDependencyModal = function() {
            if (!currentTaskId) return;
            const task = taskData[currentTaskId];
            if (!task) return;
            
            // Create modal HTML
            let modalHtml = '<div class="modal fade" id="addDependencyModal" tabindex="-1">';
            modalHtml += '<div class="modal-dialog">';
            modalHtml += '<div class="modal-content">';
            modalHtml += '<div class="modal-header">';
            modalHtml += '<h5 class="modal-title">Add Dependency</h5>';
            modalHtml += '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>';
            modalHtml += '</div>';
            modalHtml += '<div class="modal-body">';
            modalHtml += '<div class="mb-3">';
            modalHtml += '<label class="form-label">Task</label>';
            modalHtml += '<select class="form-select" id="dependencyTaskSelect">';
            modalHtml += '<option value="">Select a task...</option>';
            Object.values(taskData).forEach(t => {
                if (t.id !== task.id) {
                    modalHtml += '<option value="' + t.id + '">' + sanitizeText(t.title) + '</option>';
                }
            });
            modalHtml += '</select>';
            modalHtml += '</div>';
            modalHtml += '<div class="mb-3">';
            modalHtml += '<label class="form-label">Relationship Type</label>';
            modalHtml += '<select class="form-select" id="dependencyTypeSelect">';
            modalHtml += '<option value="related">Related</option>';
            modalHtml += '<option value="blocked_by">Blocked By</option>';
            modalHtml += '<option value="blocks">Blocks</option>';
            modalHtml += '</select>';
            modalHtml += '</div>';
            modalHtml += '</div>';
            modalHtml += '<div class="modal-footer">';
            modalHtml += '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>';
            modalHtml += '<button type="button" class="btn btn-primary" onclick="addDependency()">Add</button>';
            modalHtml += '</div>';
            modalHtml += '</div>';
            modalHtml += '</div>';
            modalHtml += '</div>';
            
            // Remove existing modal if any
            const existing = document.getElementById('addDependencyModal');
            if (existing) existing.remove();
            
            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('addDependencyModal'));
            modal.show();
            
            // Clean up on hide
            document.getElementById('addDependencyModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        };
        
        window.addDependency = function() {
            if (!currentTaskId) return;
            const task = taskData[currentTaskId];
            if (!task || !task.dependency_url) return;
            
            const taskSelect = document.getElementById('dependencyTaskSelect');
            const typeSelect = document.getElementById('dependencyTypeSelect');
            
            if (!taskSelect || !taskSelect.value || !typeSelect) return;
            
            fetch(task.dependency_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    depends_on_task_id: parseInt(taskSelect.value),
                    type: typeSelect.value
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Failed to add dependency');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to add dependency');
            });
        };
        
        window.removeDependency = function(dependsOnTaskId, type) {
            if (!currentTaskId) return;
            const task = taskData[currentTaskId];
            if (!task) return;
            
            // Find the dependency ID - we'll need to reload or store dependency IDs
            // For now, we'll need to make an API call to get dependencies first
            // This is a simplified version - you may want to enhance this
            if (confirm('Remove this dependency?')) {
                // We'll need the dependency ID, so for now reload
                location.reload();
            }
        };
        
        // Make functions globally accessible for onclick handlers
        window.addLabelToTask = addLabelToTask;
        window.selectLabelToAdd = selectLabelToAdd;
        window.removeLabelFromTask = removeLabelFromTask;
        window.addMemberToTask = addMemberToTask;
        window.selectMemberToAdd = selectMemberToAdd;
        window.removeMemberFromTask = removeMemberFromTask;
        window.toggleEditMode = toggleEditMode;
        window.updateTaskPriority = updateTaskPriority;
        window.renderComment = renderComment;
        window.submitComment = submitComment;
        window.showReplyForm = showReplyForm;
        window.hideReplyForm = hideReplyForm;
        window.submitReply = submitReply;
        
        function sanitizeText(text) {
            const div = document.createElement('div');
            div.textContent = text ?? '';
            return div.innerHTML;
        }
        
        // Custom Fields Management - Use event delegation for dynamic content
        document.addEventListener('click', function(e) {
            // Handle Add Custom Field button (check button or its children)
            const addCustomFieldBtn = document.getElementById('addCustomFieldBtn');
            if (addCustomFieldBtn && (e.target === addCustomFieldBtn || addCustomFieldBtn.contains(e.target))) {
                e.preventDefault();
                e.stopPropagation();
                const addCustomFieldForm = document.getElementById('addCustomFieldForm');
                if (addCustomFieldForm) {
                    addCustomFieldForm.style.display = 'block';
                    addCustomFieldBtn.style.display = 'none';
                }
                return;
            }
            
            // Handle Cancel button
            if (e.target && e.target.id === 'cancelCustomField') {
                e.preventDefault();
                const addCustomFieldForm = document.getElementById('addCustomFieldForm');
                const addCustomFieldBtn = document.getElementById('addCustomFieldBtn');
                if (addCustomFieldForm) {
                    addCustomFieldForm.style.display = 'none';
                }
                if (addCustomFieldBtn) {
                    addCustomFieldBtn.style.display = 'block';
                }
                const customFieldForm = document.getElementById('customFieldForm');
                const customFieldOptionsContainer = document.getElementById('customFieldOptionsContainer');
                if (customFieldForm) customFieldForm.reset();
                if (customFieldOptionsContainer) customFieldOptionsContainer.style.display = 'none';
            }
            
            // Handle delete custom field buttons
            if (e.target && e.target.closest('.delete-custom-field')) {
                e.preventDefault();
                const btn = e.target.closest('.delete-custom-field');
                if (!confirm('Are you sure you want to delete this custom field? This will also delete all values for this field.')) {
                    return;
                }
                
                const fieldId = btn.dataset.fieldId;
                const fieldItem = btn.closest('.custom-field-item');
                
                // Disable the button during deletion
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                
                fetch('{{ route("drives.projects.projects.custom-fields.destroy", [$drive, $project, ":field"]) }}'.replace(':field', fieldId), {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => Promise.reject(err));
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Remove the field from the DOM
                        if (fieldItem) {
                            fieldItem.remove();
                        }
                        
                        // Check if there are any fields left, if not show the "no fields" message
                        const customFieldsList = document.getElementById('customFieldsList');
                        const remainingFields = customFieldsList.querySelectorAll('.custom-field-item');
                        if (remainingFields.length === 0) {
                            const noFieldsMessage = document.createElement('p');
                            noFieldsMessage.className = 'text-muted';
                            noFieldsMessage.textContent = 'No custom fields defined. Click "Add Custom Field" to create one.';
                            customFieldsList.appendChild(noFieldsMessage);
                        }
                    } else {
                        alert('Failed to delete custom field');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-trash"></i>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert(error.error || error.message || 'Failed to delete custom field');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-trash"></i>';
                });
            }
        });
        
        // Handle custom field type change
        document.addEventListener('change', function(e) {
            if (e.target && e.target.id === 'customFieldType') {
                const customFieldOptionsContainer = document.getElementById('customFieldOptionsContainer');
                if (customFieldOptionsContainer) {
                    if (e.target.value === 'select' || e.target.value === 'checkbox') {
                        customFieldOptionsContainer.style.display = 'block';
                    } else {
                        customFieldOptionsContainer.style.display = 'none';
                    }
                }
            }
        });
        
        function sanitizeHtml(markup) {
            if (!markup) {
                return '';
            }
            if (window.DOMPurify) {
                return DOMPurify.sanitize(markup, { USE_PROFILES: { html: true } });
            }
            return markup;
        }
        
        function closeTaskSidebar() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        closeBtn.addEventListener('click', closeTaskSidebar);
        overlay.addEventListener('click', closeTaskSidebar);
        
        // Prevent sidebar from closing when clicking inside it
        sidebar.addEventListener('click', function(e) {
            e.stopPropagation();
        });
        
        // Close sidebar on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                closeTaskSidebar();
            }
        });
        
        // Handle task card clicks (but not during drag)
        let isDragging = false;
        let dragStartTime = 0;
        
        document.querySelectorAll('.task-card').forEach(card => {
            card.addEventListener('mousedown', function(e) {
                dragStartTime = Date.now();
                isDragging = false;
            });
            
            card.addEventListener('dragstart', function(e) {
                isDragging = true;
                this.classList.add('dragging');
            });
            
            card.addEventListener('dragend', function(e) {
                this.classList.remove('dragging');
                setTimeout(() => {
                    isDragging = false;
                }, 100);
            });
            
            card.addEventListener('click', function(e) {
                // Only open sidebar if it wasn't a drag operation
                const timeSinceDragStart = Date.now() - dragStartTime;
                if (!isDragging && timeSinceDragStart < 300) {
                    const taskId = parseInt(this.dataset.taskId);
                    openTaskSidebar(taskId);
                }
            });
        });
        
        // Handle label creation form submission
        const createLabelForm = document.getElementById('createLabelForm');
        if (createLabelForm) {
            createLabelForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                // Disable submit button
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating...';
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(response => {
                    if (response.ok) {
                        return response.json();
                    }
                    return response.json().then(err => Promise.reject(err));
                })
                .then(data => {
                    if (data.success) {
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('createLabelModal'));
                        if (modal) {
                            modal.hide();
                        }
                        
                        // Show success message
                        if (typeof showToast === 'function') {
                            showToast('success', data.message || 'Label created successfully!');
                        }
                        
                        // Reload page to show new label
                        setTimeout(() => {
                            location.reload();
                        }, 500);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                    // Handle validation errors
                    if (error.errors) {
                        let errorHtml = '<div class="alert alert-danger alert-dismissible fade show"><strong>Please fix the following errors:</strong><ul class="mb-0">';
                        for (const [key, messages] of Object.entries(error.errors)) {
                            messages.forEach(message => {
                                errorHtml += `<li>${message}</li>`;
                            });
                        }
                        errorHtml += '</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                        
                        const modalBody = document.querySelector('#createLabelModal .modal-body');
                        if (modalBody) {
                            const existingAlert = modalBody.querySelector('.alert-danger');
                            if (existingAlert) {
                                existingAlert.remove();
                            }
                            modalBody.insertAdjacentHTML('afterbegin', errorHtml);
                        }
                    } else {
                        alert(error.message || 'An error occurred while creating the label. Please try again.');
                    }
                    
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
            });
            
            // Reset form when modal is closed
            const createLabelModal = document.getElementById('createLabelModal');
            if (createLabelModal) {
                createLabelModal.addEventListener('hidden.bs.modal', function() {
                    createLabelForm.reset();
                    const errorAlerts = createLabelForm.querySelectorAll('.alert-danger');
                    errorAlerts.forEach(alert => alert.remove());
                    const submitBtn = createLabelForm.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Create Label';
                    }
                });
            }
        }
    });
    @endif
    
    // Copy public link functionality
    function copyPublicLink() {
        const publicLinkInput = document.getElementById('publicLink');
        const copyBtn = document.getElementById('copyPublicLinkBtn');
        
        if (publicLinkInput && copyBtn) {
            publicLinkInput.select();
            publicLinkInput.setSelectionRange(0, 99999); // For mobile devices
            
            try {
                navigator.clipboard.writeText(publicLinkInput.value).then(function() {
                    // Show success feedback
                    const originalHTML = copyBtn.innerHTML;
                    copyBtn.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
                    copyBtn.classList.remove('btn-outline-secondary');
                    copyBtn.classList.add('btn-success');
                    
                    // Reset after 2 seconds
                    setTimeout(function() {
                        copyBtn.innerHTML = originalHTML;
                        copyBtn.classList.remove('btn-success');
                        copyBtn.classList.add('btn-outline-secondary');
                    }, 2000);
                    
                    // Show toast notification if available
                    if (typeof showToast === 'function') {
                        showToast('success', 'Public link copied to clipboard!');
                    }
                }).catch(function(err) {
                    console.error('Failed to copy: ', err);
                    // Fallback for older browsers
                    document.execCommand('copy');
                    alert('Link copied to clipboard!');
                });
            } catch (err) {
                // Fallback for older browsers
                try {
                    document.execCommand('copy');
                    alert('Link copied to clipboard!');
                } catch (e) {
                    alert('Failed to copy link. Please select and copy manually.');
                }
            }
        }
    }
    
    // Make copy function available globally
    window.copyPublicLink = copyPublicLink;

    document.addEventListener('DOMContentLoaded', function() {
        const createStatusForm = document.getElementById('createStatusForm');
        const statusListEl = document.getElementById('statusList');
        const csrfToken = '{{ csrf_token() }}';
        const storeStatusUrl = '{{ route('drives.projects.projects.task-statuses.store', [$drive, $project]) }}';
        const reorderStatusUrl = '{{ route('drives.projects.projects.task-statuses.reorder', [$drive, $project]) }}';
        const updateStatusUrlTemplate = '{{ route('drives.projects.projects.task-statuses.update', [$drive, $project, '__STATUS__']) }}';
        const deleteStatusUrlTemplate = '{{ route('drives.projects.projects.task-statuses.destroy', [$drive, $project, '__STATUS__']) }}';

        function showStatusError(error) {
            if (error && error.errors) {
                const firstErrorGroup = Object.values(error.errors)[0];
                if (Array.isArray(firstErrorGroup) && firstErrorGroup.length > 0) {
                    alert(firstErrorGroup[0]);
                    return;
                }
            }

            const message = (error && error.message) ? error.message : 'Unable to update statuses right now. Please try again.';
            alert(message);
        }

        function sendJson(url, method, payload = null) {
            const options = {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            };

            if (payload !== null) {
                options.body = JSON.stringify(payload);
            }

            return fetch(url, options).then(response => {
                if (response.ok) {
                    return response.json().catch(() => ({}));
                }

                return response.json().then(err => Promise.reject(err));
            });
        }

        if (createStatusForm) {
            createStatusForm.addEventListener('submit', function(event) {
                event.preventDefault();

                const nameInput = createStatusForm.querySelector('input[name="name"]');
                const colorInput = createStatusForm.querySelector('input[name="color"]');
                const completedInput = createStatusForm.querySelector('input[name="is_completed"]');
                const submitButton = createStatusForm.querySelector('button[type="submit"]');

                if (!nameInput.value.trim()) {
                    alert('Status name is required.');
                    return;
                }

                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Adding';

                sendJson(storeStatusUrl, 'POST', {
                    name: nameInput.value.trim(),
                    color: colorInput.value,
                    is_completed: completedInput.checked,
                })
                .then(() => location.reload())
                .catch(error => {
                    showStatusError(error);
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="fas fa-plus me-1"></i>Add';
                });
            });
        }

        if (statusListEl && typeof Sortable !== 'undefined') {
            new Sortable(statusListEl, {
                handle: '.drag-handle',
                animation: 150,
                onEnd: function() {
                    const order = Array.from(statusListEl.querySelectorAll('li[data-status-id]'))
                        .map(item => Number(item.dataset.statusId));

                    sendJson(reorderStatusUrl, 'POST', { order })
                        .catch(showStatusError);
                }
            });

            statusListEl.querySelectorAll('.status-save-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const listItem = this.closest('li[data-status-id]');
                    if (!listItem) {
                        return;
                    }

                    const statusId = listItem.dataset.statusId;
                    const nameInput = listItem.querySelector('.status-name-input');
                    const colorInput = listItem.querySelector('.status-color-input');
                    const completedInput = listItem.querySelector('.status-completed-input');

                    if (!nameInput.value.trim()) {
                        alert('Status name cannot be empty.');
                        return;
                    }

                    const updateUrl = updateStatusUrlTemplate.replace('__STATUS__', statusId);
                    const saveButton = this;
                    saveButton.disabled = true;
                    const originalText = saveButton.innerHTML;
                    saveButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving';

                    sendJson(updateUrl, 'PATCH', {
                        name: nameInput.value.trim(),
                        color: colorInput.value,
                        is_completed: completedInput.checked,
                    })
                    .then(() => location.reload())
                    .catch(error => {
                        showStatusError(error);
                        saveButton.disabled = false;
                        saveButton.innerHTML = originalText;
                    });
                });
            });

            statusListEl.querySelectorAll('.status-delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const listItem = this.closest('li[data-status-id]');
                    if (!listItem) {
                        return;
                    }

                    const statusId = listItem.dataset.statusId;
                    const deleteUrl = deleteStatusUrlTemplate.replace('__STATUS__', statusId);

                    if (!confirm('Are you sure you want to delete this status?')) {
                        return;
                    }

                    const deleteButton = this;
                    deleteButton.disabled = true;
                    const originalText = deleteButton.innerHTML;
                    deleteButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Deleting';

                    sendJson(deleteUrl, 'DELETE')
                        .then(() => location.reload())
                        .catch(error => {
                            showStatusError(error);
                            deleteButton.disabled = false;
                            deleteButton.innerHTML = originalText;
                        });
                });
            });
        }
    });

    // Laravel Reverb Real-time Updates
    @if(isset($reverbConfig) && $reverbConfig['isEnabled'] && !empty($reverbConfig['key']))
    // Load Laravel Echo and Pusher JS
    (function() {
        // Check if Echo is already loaded
        if (typeof Echo !== 'undefined') {
            initializeRealtimeUpdates();
            return;
        }

        // Load Pusher JS first
        const pusherScript = document.createElement('script');
        pusherScript.src = 'https://js.pusher.com/8.2.0/pusher.min.js';
        pusherScript.onload = function() {
            // Load Laravel Echo
            const echoScript = document.createElement('script');
            echoScript.src = 'https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.min.js';
            echoScript.onload = function() {
                initializeRealtimeUpdates();
            };
            document.head.appendChild(echoScript);
        };
        document.head.appendChild(pusherScript);
    })();

    function initializeRealtimeUpdates() {
        // Reverb uses Pusher protocol, so we configure Echo to use Pusher client
        // but point it to the Reverb server
        // Get CSRF token from meta tag to ensure it matches the session
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        
        const reverbConfig = {
            key: '{{ $reverbConfig['key'] }}',
            wsHost: '{{ $reverbConfig['host'] }}',
            wsPort: {{ $reverbConfig['scheme'] === 'https' ? 443 : $reverbConfig['port'] }},
            wssPort: {{ $reverbConfig['scheme'] === 'https' ? 443 : $reverbConfig['port'] }},
            forceTLS: {{ $reverbConfig['scheme'] === 'https' ? 'true' : 'false' }},
            enabledTransports: ['ws', 'wss'],
            authEndpoint: '{{ url('/broadcasting/auth') }}',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            }
        };

        // Initialize Laravel Echo with Pusher client configured for Reverb
        try {
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: reverbConfig.key,
                cluster: '', // Required by Pusher JS library, but not used by Reverb
                wsHost: reverbConfig.wsHost,
                wsPort: reverbConfig.wsPort,
                wssPort: reverbConfig.wssPort,
                forceTLS: reverbConfig.forceTLS === 'true' || reverbConfig.forceTLS === true,
                enabledTransports: reverbConfig.enabledTransports,
                disableStats: true,
                authEndpoint: reverbConfig.authEndpoint,
                auth: reverbConfig.auth
            });

            // Add connection event listeners
            window.Echo.connector.pusher.connection.bind('error', function(err) {
                console.error('Reverb: Connection error:', err);
            });

            const projectId = {{ $project->id }};
            // Echo.private() automatically adds 'private-' prefix, so we use 'project.{id}' not 'private-project.{id}'
            const projectChannel = `project.${projectId}`;

            // Listen for task created events
            // Echo.private() will automatically prefix with 'private-', making it 'private-project.{id}'
            const channel = window.Echo.private(projectChannel);
            
            // Listen for events
            channel
                .listen('.task.created', (e) => {
                    handleTaskCreated(e.task);
                })
                .listen('.task.updated', (e) => {
                    handleTaskUpdated(e.task);
                })
                .listen('.task.deleted', (e) => {
                    handleTaskDeleted(e.task_id);
                })
                .listen('.task.moved', (e) => {
                    handleTaskMoved(e);
                })
                .listen('.task.comment.added', (e) => {
                    handleTaskCommentAdded(e);
                })
                .error((error) => {
                    console.error('Reverb: Channel subscription error:', error);
                });
        } catch (error) {
            console.error('Reverb: Failed to initialize Echo:', error);
        }
    }

    function handleTaskCreated(taskData) {
        // Only update if we're on the kanban view
        if (typeof currentView === 'undefined' || currentView !== 'kanban') {
            return;
        }

        // Add task to the appropriate column
        const statusId = taskData.status?.id;
        if (!statusId) {
            return;
        }

        // Check if task already exists (to prevent duplicates from own actions)
        if (document.querySelector(`[data-task-id="${taskData.id}"]`)) {
            return;
        }

        // Add task to kanban board
        const column = document.querySelector(`#kanban-status-${statusId}`);
        if (column) {
            // Hide empty state if it exists
            const emptyState = document.getElementById(`empty-status-${statusId}`);
            if (emptyState) {
                emptyState.style.display = 'none';
            }
            
            // Hide quick add button if it exists
            const quickAddButton = column.querySelector('.quick-add-button');
            if (quickAddButton) {
                quickAddButton.style.display = 'block';
            }
            
            // Create and add task card
            const taskCard = createTaskCard(taskData);
            const quickAdd = column.querySelector('.quick-add-task');
            if (quickAdd && quickAdd.nextSibling) {
                column.insertBefore(taskCard, quickAdd.nextSibling);
            } else {
                column.appendChild(taskCard);
            }
            
            // Update task data
            if (typeof window.taskData !== 'undefined') {
                window.taskData[taskData.id] = formatTaskData(taskData);
            }
            
            // Update column task count
            updateColumnTaskCount(statusId);
            
            // Show notification
            showToast('New task created: ' + taskData.title, 'success');
        }
    }

    function handleTaskUpdated(taskData) {
        // Update task in kanban board if it exists
        const taskCard = document.querySelector(`[data-task-id="${taskData.id}"]`);
        if (!taskCard) {
            // Task might not be on the board, check if we should add it
            if (currentView === 'kanban') {
                handleTaskCreated(taskData);
            }
            return;
        }

        // Update task card content
        updateTaskCard(taskCard, taskData);
        
        // Update task data
        if (typeof window.taskData !== 'undefined') {
            window.taskData[taskData.id] = formatTaskData(taskData);
        }
        
        // If task is open in sidebar, refresh it
        if (window.currentOpenTaskId === taskData.id) {
            renderSidebarContent(taskData);
        }
    }

    function handleTaskDeleted(taskId) {
        // Remove task from kanban board
        const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
        if (taskCard) {
            const statusId = taskCard.getAttribute('data-status-id');
            taskCard.remove();
            
            // Update task data
            if (typeof window.taskData !== 'undefined') {
                delete window.taskData[taskId];
            }
            
            // Update column task count
            if (statusId) {
                updateColumnTaskCount(statusId);
                
                // Show empty state if column is now empty
                const column = document.getElementById(`kanban-status-${statusId}`);
                if (column) {
                    const cards = column.querySelectorAll('.task-card:not(.dragging)');
                    const emptyState = document.getElementById(`empty-status-${statusId}`);
                    if (cards.length === 0 && emptyState) {
                        emptyState.style.display = 'block';
                    }
                    // Hide quick add button if column is empty
                    const quickAddButton = column.querySelector('.quick-add-button');
                    if (quickAddButton && cards.length === 0) {
                        quickAddButton.style.display = 'none';
                    }
                }
            }
            
            // Close sidebar if this task was open
            if (typeof window.currentOpenTaskId !== 'undefined' && window.currentOpenTaskId === taskId) {
                if (typeof closeTaskSidebar === 'function') {
                    closeTaskSidebar();
                }
            }
            
            // Show notification
            showToast('Task deleted', 'info');
        }
    }

    function handleTaskMoved(eventData) {
        const taskData = eventData.task;
        const oldStatusId = eventData.old_status_id;
        const newStatusId = eventData.new_status_id;
        
        // Find task card
        const taskCard = document.querySelector(`[data-task-id="${taskData.id}"]`);
        if (!taskCard) {
            return;
        }

        // If status changed, move to new column
        if (oldStatusId !== newStatusId) {
            const oldColumn = document.getElementById(`kanban-status-${oldStatusId}`);
            const newColumn = document.getElementById(`kanban-status-${newStatusId}`);
            
            if (oldColumn && newColumn && taskCard.parentElement === oldColumn) {
                // Hide empty state for new column
                const newEmptyState = document.getElementById(`empty-status-${newStatusId}`);
                if (newEmptyState) {
                    newEmptyState.style.display = 'none';
                }
                
                // Show quick add button for new column if it exists
                const newQuickAddButton = newColumn.querySelector('.quick-add-button');
                if (newQuickAddButton) {
                    newQuickAddButton.style.display = 'block';
                }
                
                // Remove from old column
                taskCard.remove();
                
                // Update task card attributes first
                taskCard.setAttribute('data-status-id', newStatusId);
                if (taskData.status) {
                    taskCard.setAttribute('data-status-slug', taskData.status.slug || '');
                }
                
                // Update status badge
                if (taskData.status) {
                    const statusBadge = taskCard.querySelector('[data-role="task-status-badge"]');
                    if (statusBadge) {
                        statusBadge.textContent = taskData.status.name;
                        statusBadge.style.backgroundColor = taskData.status.color;
                    }
                }
                
                // Update sort order
                const newSortOrder = eventData.new_sort_order !== undefined ? eventData.new_sort_order : (taskData.sort_order || 0);
                taskCard.style.order = newSortOrder;
                
                // Insert task card in the correct position based on new_sort_order (which is the index)
                // Get all existing task cards in the new column (in DOM order, only before quick-add-task)
                const quickAdd = newColumn.querySelector('.quick-add-task');
                
                // Get all children of the column
                const allChildren = Array.from(newColumn.children);
                
                // Find task cards that come before quick-add-task
                const existingCards = [];
                for (const child of allChildren) {
                    if (child === quickAdd) {
                        break; // Stop when we reach quick-add-task
                    }
                    if (child.classList.contains('task-card') && !child.classList.contains('dragging') && child !== taskCard) {
                        existingCards.push(child);
                    }
                }
                
                // new_sort_order is the 0-based index where the card should be positioned
                // Insert the card at that index position
                // NEVER insert before quick-add-task - always insert before task cards
                let insertBefore = null;
                
                if (newSortOrder < existingCards.length && existingCards[newSortOrder]) {
                    // Insert at the specified index position (before the card at that index)
                    insertBefore = existingCards[newSortOrder];
                } else {
                    // Index is beyond existing cards - find the last task card and insert after it
                    // Get ALL task cards in the column (including any that might be after quick-add-task)
                    const allTaskCards = Array.from(newColumn.querySelectorAll('.task-card:not(.dragging)'))
                        .filter(card => card !== taskCard);
                    
                    if (allTaskCards.length > 0) {
                        // Insert after the last task card
                        const lastTaskCard = allTaskCards[allTaskCards.length - 1];
                        insertBefore = lastTaskCard.nextSibling; // This will be quick-add-task or null
                    } else if (quickAdd && quickAdd.parentNode === newColumn) {
                        // No task cards exist, insert before quick-add-task as last resort
                        insertBefore = quickAdd;
                    }
                }
                
                // Insert the card at the correct position
                if (insertBefore && insertBefore.parentNode === newColumn) {
                    newColumn.insertBefore(taskCard, insertBefore);
                } else if (quickAdd && quickAdd.parentNode === newColumn) {
                    // Fallback: insert before quick-add-task only if absolutely necessary
                    newColumn.insertBefore(taskCard, quickAdd);
                } else {
                    // Last resort: append to end
                    newColumn.appendChild(taskCard);
                }
                
                // Update column task counts
                if (typeof updateColumnTaskCount === 'function') {
                    updateColumnTaskCount(oldStatusId);
                    updateColumnTaskCount(newStatusId);
                } else if (typeof window.updateColumnTaskCount === 'function') {
                    window.updateColumnTaskCount(oldStatusId);
                    window.updateColumnTaskCount(newStatusId);
                }
                
                // Show empty state for old column if it's now empty
                const oldCards = oldColumn.querySelectorAll('.task-card:not(.dragging)');
                const oldEmptyState = document.getElementById(`empty-status-${oldStatusId}`);
                if (oldCards.length === 0 && oldEmptyState) {
                    oldEmptyState.style.display = 'block';
                }
                // Hide quick add button for old column if it's empty
                const oldQuickAddButton = oldColumn.querySelector('.quick-add-button');
                if (oldQuickAddButton && oldCards.length === 0) {
                    oldQuickAddButton.style.display = 'none';
                }
            }
        } else {
            // Same column - just reorder within the column
            const column = document.getElementById(`kanban-status-${newStatusId}`);
            if (!column) {
                return;
            }
            
            // Update sort order CSS property
            const newSortOrder = eventData.new_sort_order !== undefined ? eventData.new_sort_order : (taskData.sort_order || 0);
            taskCard.style.order = newSortOrder;
            
            // Temporarily remove the card from DOM to get accurate list of remaining cards
            const wasInDOM = taskCard.parentNode === column;
            if (wasInDOM) {
                taskCard.remove();
            }
            
            // Get all children of the column
            const allChildren = Array.from(column.children);
            const quickAdd = column.querySelector('.quick-add-task');
            
            // Find task cards that come before quick-add-task
            const existingCards = [];
            for (const child of allChildren) {
                if (child === quickAdd) {
                    break; // Stop when we reach quick-add-task
                }
                if (child.classList.contains('task-card') && !child.classList.contains('dragging')) {
                    existingCards.push(child);
                }
            }
            
            // Find the correct insertion point based on the new sort order (index)
            // NEVER insert before quick-add-task - always insert before task cards
            let insertBefore = null;
            
            if (newSortOrder < existingCards.length && existingCards[newSortOrder]) {
                // Insert at the specified index position (before the card at that index)
                insertBefore = existingCards[newSortOrder];
            } else {
                // Index is beyond existing cards - find the last task card and insert after it
                // Get ALL task cards in the column (including any that might be after quick-add-task)
                const allTaskCards = Array.from(column.querySelectorAll('.task-card:not(.dragging)'));
                
                if (allTaskCards.length > 0) {
                    // Insert after the last task card
                    const lastTaskCard = allTaskCards[allTaskCards.length - 1];
                    insertBefore = lastTaskCard.nextSibling; // This will be quick-add-task or null
                } else if (quickAdd && quickAdd.parentNode === column) {
                    // No task cards exist, insert before quick-add-task as last resort
                    insertBefore = quickAdd;
                }
            }
            
            // Move the card to the correct position
            if (insertBefore && insertBefore.parentNode === column) {
                column.insertBefore(taskCard, insertBefore);
            } else if (quickAdd && quickAdd.parentNode === column) {
                // Fallback: insert before quick-add-task only if absolutely necessary
                column.insertBefore(taskCard, quickAdd);
            } else if (!wasInDOM) {
                // If card wasn't in DOM and we couldn't find insertion point, append
                column.appendChild(taskCard);
            }
        }
        
        // Update task data
        if (typeof window.taskData !== 'undefined' && window.taskData[taskData.id]) {
            window.taskData[taskData.id].status = taskData.status;
            window.taskData[taskData.id].sort_order = taskData.sort_order;
        }
    }

    function handleTaskCommentAdded(eventData) {
        // Update task comment count if displayed
        const taskCard = document.querySelector(`[data-task-id="${eventData.task_id}"]`);
        if (taskCard) {
            // You can update comment count here if you display it on the card
            // For now, just show a notification
            showToast('New comment added to task', 'info');
        }
        
        // If task is open in sidebar, refresh comments
        if (window.currentOpenTaskId === eventData.task_id) {
            // Reload task data and refresh sidebar
            // This would require an API call to get updated task with comments
            // For now, just show a notification
        }
    }

    function formatTaskData(taskData) {
        // Format task data to match the structure used in the kanban view
        return {
            id: taskData.id,
            title: taskData.title,
            description: taskData.description || '',
            priority: taskData.priority,
            due_date: taskData.due_date,
            start_date: taskData.start_date,
            estimated_hours: taskData.estimated_hours,
            actual_hours: taskData.actual_hours,
            status: taskData.status ? {
                id: taskData.status.id,
                name: taskData.status.name,
                color: taskData.status.color,
            } : null,
            owner: taskData.owner,
            members: taskData.members || [],
            labels: taskData.labels || [],
            custom_fields: taskData.custom_fields || {},
            sort_order: taskData.sort_order || 0,
        };
    }

    function createTaskCard(taskData) {
        // Create a task card element matching the structure from the Blade partial
        const card = document.createElement('div');
        card.className = 'task-card';
        card.setAttribute('data-task-id', taskData.id);
        card.setAttribute('data-task-title', taskData.title);
        card.setAttribute('data-status-id', taskData.status?.id || '');
        // Note: slug might not be available in event data, will be set when card is rendered
        card.setAttribute('data-status-slug', '');
        card.setAttribute('data-priority', taskData.priority || '');
        card.setAttribute('data-label-ids', (taskData.labels || []).map(l => l.id).join(','));
        card.setAttribute('data-member-ids', (taskData.members || []).map(m => m.id).join(','));
        card.setAttribute('data-owner-id', taskData.owner?.id || '');
        card.setAttribute('draggable', 'true');
        card.style.order = taskData.sort_order || 0;
        
        // Set priority border color
        const priorityColors = {
            'urgent': '#dc3545',
            'high': '#ffc107',
            'medium': '#0dcaf0',
            'low': '#6c757d'
        };
        card.style.borderLeft = `4px solid ${priorityColors[taskData.priority] || '#6c757d'}`;
        
        // Build task card HTML
        let cardHTML = '';
        
        // Task card title
        cardHTML += `<div class="task-card-title">${sanitizeText(taskData.title)}</div>`;
        
        // Task card description
        if (taskData.description) {
            const desc = sanitizeText(taskData.description.substring(0, 100));
            cardHTML += `<div class="task-card-description">${desc}${taskData.description.length > 100 ? '...' : ''}</div>`;
        }
        
        // Task card meta (priority and labels)
        cardHTML += '<div class="task-card-meta">';
        const priorityClasses = {
            'urgent': 'danger',
            'high': 'warning',
            'medium': 'info',
            'low': 'secondary'
        };
        cardHTML += `<span class="badge bg-${priorityClasses[taskData.priority] || 'secondary'}"><i class="fas fa-flag me-1"></i>${(taskData.priority || '').charAt(0).toUpperCase() + (taskData.priority || '').slice(1)}</span>`;
        if (taskData.labels && taskData.labels.length > 0) {
            taskData.labels.slice(0, 3).forEach(label => {
                cardHTML += `<span class="badge" style="background-color: ${label.color}; color: white;">${sanitizeText(label.name)}</span>`;
            });
            if (taskData.labels.length > 3) {
                cardHTML += `<span class="badge bg-secondary">+${taskData.labels.length - 3}</span>`;
            }
        }
        cardHTML += '</div>';
        
        // Task card footer
        cardHTML += '<div class="task-card-footer">';
        cardHTML += '<div class="d-flex align-items-center gap-2">';
        if (taskData.due_date) {
            const dueDate = new Date(taskData.due_date + 'T00:00:00');
            const isOverdue = dueDate < new Date();
            cardHTML += `<small class="text-${isOverdue ? 'danger' : 'muted'}"><i class="fas fa-calendar-alt me-1"></i>${dueDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}</small>`;
        }
        if (taskData.owner) {
            const ownerInitial = taskData.owner.name ? taskData.owner.name.charAt(0).toUpperCase() : '?';
            cardHTML += `<div class="bg-info rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.7rem;" title="${sanitizeText(taskData.owner.name)}"><span style="color: white;">${ownerInitial}</span></div>`;
        }
        if (taskData.members && taskData.members.length > 0) {
            cardHTML += `<small class="text-muted"><i class="fas fa-users me-1"></i>${taskData.members.length}</small>`;
        }
        cardHTML += '</div>';
        cardHTML += '<div class="d-flex align-items-center gap-1">';
        if (taskData.status) {
            cardHTML += `<span class="badge task-status-badge" style="background-color: ${taskData.status.color}; color: #fff;" data-role="task-status-badge">${sanitizeText(taskData.status.name)}</span>`;
        }
        cardHTML += '</div>';
        cardHTML += '</div>';
        
        card.innerHTML = cardHTML;
        
        // Add event listeners
        card.addEventListener('click', function(e) {
            if (typeof openTaskSidebar === 'function') {
                openTaskSidebar(taskData.id);
            }
        });
        
        card.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            if (typeof showTaskContextMenu === 'function') {
                showTaskContextMenu(e, taskData.id);
            }
        });
        
        // Initialize drag and drop if available
        if (typeof initializeDragAndDrop === 'function') {
            initializeDragAndDrop(card);
        }
        
        return card;
    }

    function updateTaskCard(taskCard, taskData) {
        // Update task card title
        const titleEl = taskCard.querySelector('.task-card-title');
        if (titleEl) {
            titleEl.textContent = taskData.title;
        }
        taskCard.setAttribute('data-task-title', taskData.title);
        
        // Update task card description
        const descEl = taskCard.querySelector('.task-card-description');
        if (taskData.description) {
            const desc = sanitizeText(taskData.description.substring(0, 100));
            if (descEl) {
                descEl.innerHTML = desc + (taskData.description.length > 100 ? '...' : '');
                descEl.style.display = '';
            } else {
                // Create description element if it doesn't exist
                const titleEl = taskCard.querySelector('.task-card-title');
                if (titleEl) {
                    const newDescEl = document.createElement('div');
                    newDescEl.className = 'task-card-description';
                    newDescEl.innerHTML = desc + (taskData.description.length > 100 ? '...' : '');
                    titleEl.after(newDescEl);
                }
            }
        } else {
            if (descEl) {
                descEl.style.display = 'none';
            }
        }
        
        // Update priority border
        const priorityColors = {
            'urgent': '#dc3545',
            'high': '#ffc107',
            'medium': '#0dcaf0',
            'low': '#6c757d'
        };
        taskCard.style.borderLeft = `4px solid ${priorityColors[taskData.priority] || '#6c757d'}`;
        taskCard.setAttribute('data-priority', taskData.priority || '');
        
        // Update status badge
        if (taskData.status) {
            const statusBadge = taskCard.querySelector('[data-role="task-status-badge"]');
            if (statusBadge) {
                statusBadge.textContent = taskData.status.name;
                statusBadge.style.backgroundColor = taskData.status.color;
            }
            taskCard.setAttribute('data-status-id', taskData.status.id);
            taskCard.setAttribute('data-status-slug', taskData.status.slug || '');
        }
        
        // Update labels (simplified - would need to rebuild meta section for full update)
        if (taskData.labels) {
            taskCard.setAttribute('data-label-ids', taskData.labels.map(l => l.id).join(','));
        }
        
        // Update members
        if (taskData.members) {
            taskCard.setAttribute('data-member-ids', taskData.members.map(m => m.id).join(','));
            const memberCountEl = taskCard.querySelector('.task-card-footer .fa-users')?.parentElement;
            if (memberCountEl && taskData.members.length > 0) {
                memberCountEl.innerHTML = `<i class="fas fa-users me-1"></i>${taskData.members.length}`;
            }
        }
        
        // Update owner
        if (taskData.owner) {
            taskCard.setAttribute('data-owner-id', taskData.owner.id);
        }
    }

    function showToast(message, type = 'info') {
        // Use your existing toast system (from public/js/toast.js)
        // Use toastManager directly to avoid recursion with window.showToast
        if (typeof window.toastManager !== 'undefined' && typeof window.toastManager.show === 'function') {
            window.toastManager.show(message, type);
        } else {
            // Fallback to console log
            console.log('Toast:', message, type);
        }
    }
    @elseif(isset($reverbConfig))
        console.warn('Reverb: Real-time updates are DISABLED');
        console.warn('Current broadcasting connection:', '{{ $reverbConfig['connection'] ?? 'not set' }}');
        console.warn('Expected: reverb');
        console.warn('To enable real-time updates:');
        console.warn('1. Set BROADCAST_CONNECTION=reverb in your .env file');
        console.warn('2. Set REVERB_APP_KEY, REVERB_APP_SECRET, REVERB_APP_ID in your .env file');
        console.warn('3. Start the Reverb server: php artisan reverb:start');
        console.warn('4. Refresh this page');
    @endif
</script>
@endpush

