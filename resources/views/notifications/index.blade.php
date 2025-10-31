@extends('layouts.dashboard')

@section('title', 'Notifications')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="display-6 mb-0 brand-teal">Notifications</h1>
                @if($unreadCount > 0)
                    <form action="{{ route('notifications.read-all') }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check-double me-2"></i>Mark All as Read
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    @if($notifications->count() > 0)
        <div class="row">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="list-group list-group-flush">
                        @foreach($notifications as $notification)
                            <div class="list-group-item {{ $notification->read ? '' : 'bg-dark border-primary' }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-2">
                                            @if(!$notification->read)
                                                <span class="badge bg-primary me-2">New</span>
                                            @endif
                                            <h6 class="mb-0">{{ $notification->title }}</h6>
                                        </div>
                                        <p class="mb-2 text-muted">{{ $notification->message }}</p>
                                        <small class="text-muted">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </small>
                                        @if(isset($notification->data['url']))
                                            <div class="mt-2">
                                                <a href="{{ $notification->data['url'] }}" class="btn btn-sm btn-outline-primary">
                                                    View <i class="fas fa-arrow-right ms-1"></i>
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            @if($notification->read)
                                                <li>
                                                    <form action="{{ route('notifications.unread', $notification) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fas fa-envelope me-2"></i>Mark as Unread
                                                        </button>
                                                    </form>
                                                </li>
                                            @else
                                                <li>
                                                    <form action="{{ route('notifications.read', $notification) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fas fa-check me-2"></i>Mark as Read
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('notifications.destroy', $notification) }}" method="POST" 
                                                      onsubmit="return confirm('Are you sure you want to delete this notification?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-trash me-2"></i>Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $notifications->links() }}
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-12">
                <div class="dashboard-card text-center py-5">
                    <i class="fas fa-bell fa-3x text-muted mb-3"></i>
                    <h5>No notifications</h5>
                    <p class="text-muted">You're all caught up! You'll see notifications here when you're mentioned or when there's activity on your tasks.</p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

