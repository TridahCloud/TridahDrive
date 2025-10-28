<header class="dashboard-header">
    <div class="header-content container-fluid">
        <!-- Search Bar -->
        <div class="header-search flex-grow-1">
            <div class="input-group">
                <span class="input-group-text bg-transparent border-0 text-muted">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" class="form-control border-0 bg-transparent text-white" placeholder="Search...">
            </div>
        </div>
        
        <!-- Right Section -->
        <div class="header-actions d-flex align-items-center gap-3">
            <!-- Status -->
            <span class="badge bg-success d-flex align-items-center gap-1">
                <span class="status-dot"></span>
                <span class="d-none d-md-inline">Online</span>
            </span>
            
            <!-- Notifications -->
            <div class="dropdown">
                <button class="btn btn-link text-white position-relative p-0" id="notificationDropdown" data-bs-toggle="dropdown" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationBadge" style="display: none;">0</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end notification-dropdown" style="min-width: 350px; max-width: 400px;">
                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                        <h6 class="mb-0 fw-semibold">Notifications</h6>
                        <button class="btn btn-sm btn-link text-decoration-none p-0" id="markAllReadBtn" style="display: none;">
                            <small>Mark all as read</small>
                        </button>
                    </div>
                    <div id="notificationsList" class="notification-list" style="max-height: 400px; overflow-y: auto;">
                        <div class="text-center p-4" style="color: rgba(255, 255, 255, 0.6);">
                            <i class="fas fa-spinner fa-spin"></i> Loading...
                        </div>
                    </div>
                    <div class="text-center p-2 border-top">
                        <small style="color: rgba(255, 255, 255, 0.6);">No more notifications</small>
                    </div>
                </div>
            </div>
            
            <!-- Help -->
            <button class="btn btn-link text-white p-0" title="Help">
                <i class="fas fa-question-circle"></i>
            </button>
            
            <!-- User Profile -->
            <div class="dropdown">
                <button class="btn btn-link text-white text-decoration-none dropdown-toggle d-flex align-items-center gap-2 p-0" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle"></i>
                    <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                    <i class="fas fa-chevron-down small"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
            
            <!-- Theme Toggle -->
            <button class="btn btn-link text-white p-0 theme-toggle" id="themeToggle" title="Toggle Theme">
                <i class="fas fa-moon"></i>
            </button>
        </div>
    </div>
</header>
