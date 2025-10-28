@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-6 mb-0 brand-teal">Dashboard Overview</h1>
            <p class="text-muted">Your central command center for managing everything</p>
        </div>
    </div>
    
    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="dashboard-card stat-card" data-color="teal">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <p class="card-title">Active Projects</p>
                        <h2 class="mb-0 brand-teal">12</h2>
                        <small class="text-muted">3 new this week</small>
                    </div>
                    <div class="stat-icon bg-brand-teal">
                        <i class="fas fa-folder-open"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="dashboard-card stat-card" data-color="blue">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <p class="card-title">Team Members</p>
                        <h2 class="mb-0 brand-blue">24</h2>
                        <small class="text-muted">All active</small>
                    </div>
                    <div class="stat-icon bg-brand-blue">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="dashboard-card stat-card" data-color="amber">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <p class="card-title">Pending Tasks</p>
                        <h2 class="mb-0 brand-amber">8</h2>
                        <small class="text-muted">2 urgent</small>
                    </div>
                    <div class="stat-icon bg-brand-amber">
                        <i class="fas fa-tasks"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="dashboard-card stat-card" data-color="teal">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <p class="card-title">Productivity</p>
                        <h2 class="mb-0 brand-teal">92%</h2>
                        <small class="text-muted">This month</small>
                    </div>
                    <div class="stat-icon bg-brand-teal">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="row mb-4">
        <div class="col-md-8 mb-3">
            <div class="dashboard-card interactive-card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Activity</h5>
                    <button class="btn btn-sm btn-outline-light">
                        <i class="fas fa-list me-1"></i>
                        View All
                    </button>
                </div>
                <div class="activity-list">
                    <div class="activity-item d-flex align-items-start p-3 mb-2 bg-light bg-opacity-5 rounded">
                        <div class="activity-icon bg-brand-teal bg-opacity-20 p-2 rounded-circle me-3">
                            <i class="fas fa-user-plus brand-teal"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium">New team member added</div>
                            <small class="text-muted">Sarah Johnson joined the design team • 2 hours ago</small>
                        </div>
                        <div class="text-muted small">Just now</div>
                    </div>
                    
                    <div class="activity-item d-flex align-items-start p-3 mb-2 bg-light bg-opacity-5 rounded">
                        <div class="activity-icon bg-brand-blue bg-opacity-20 p-2 rounded-circle me-3">
                            <i class="fas fa-file-alt brand-blue"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium">Project "Summer Update" completed</div>
                            <small class="text-muted">Deployed to production • 5 hours ago</small>
                        </div>
                        <div class="text-muted small">5h ago</div>
                    </div>
                    
                    <div class="activity-item d-flex align-items-start p-3 mb-2 bg-light bg-opacity-5 rounded">
                        <div class="activity-icon bg-brand-amber bg-opacity-20 p-2 rounded-circle me-3">
                            <i class="fas fa-bell brand-amber"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium">Weekly report published</div>
                            <small class="text-muted">Available for review • Yesterday</small>
                        </div>
                        <div class="text-muted small">Yesterday</div>
                    </div>
                    
                    <div class="activity-item d-flex align-items-start p-3 mb-2 bg-light bg-opacity-5 rounded">
                        <div class="activity-icon bg-brand-teal bg-opacity-20 p-2 rounded-circle me-3">
                            <i class="fas fa-check-circle brand-teal"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium">Code review completed</div>
                            <small class="text-muted">3 pull requests merged • 2 days ago</small>
                        </div>
                        <div class="text-muted small">2 days ago</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="dashboard-card interactive-card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="quick-actions-list">
                    <a href="#" class="quick-action-item">
                        <div class="quick-action-icon bg-brand-teal">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium">New Project</div>
                            <small class="text-muted">Start a new project</small>
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    
                    <a href="#" class="quick-action-item">
                        <div class="quick-action-icon bg-brand-blue">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium">Add Member</div>
                            <small class="text-muted">Invite team member</small>
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    
                    <a href="#" class="quick-action-item">
                        <div class="quick-action-icon bg-brand-amber">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium">Generate Report</div>
                            <small class="text-muted">Create analytics report</small>
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    
                    <a href="#" class="quick-action-item">
                        <div class="quick-action-icon bg-brand-teal">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium">Settings</div>
                            <small class="text-muted">Configure preferences</small>
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Dashboard Features -->
    <div class="row">
        <div class="col-12">
            <div class="dashboard-card feature-showcase">
                <h4 class="mb-4">Dashboard Features</h4>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="feature-box">
                            <div class="feature-box-icon bg-brand-teal">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <h5 class="mt-3 mb-2">Lightning Fast</h5>
                            <p class="text-muted">Built with performance in mind. Real-time updates and instant loading.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-box">
                            <div class="feature-box-icon bg-brand-blue">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <h5 class="mt-3 mb-2">Fully Responsive</h5>
                            <p class="text-muted">Optimized for all devices. Desktop, tablet, and mobile.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-box">
                            <div class="feature-box-icon bg-brand-amber">
                                <i class="fas fa-palette"></i>
                            </div>
                            <h5 class="mt-3 mb-2">Theme Toggle</h5>
                            <p class="text-muted">Switch between dark and light mode with one click.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-box">
                            <div class="feature-box-icon bg-brand-teal">
                                <i class="fas fa-puzzle-piece"></i>
                            </div>
                            <h5 class="mt-3 mb-2">Modular Design</h5>
                            <p class="text-muted">Easy to customize and extend. Component-based architecture.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-box">
                            <div class="feature-box-icon bg-brand-blue">
                                <i class="fas fa-code"></i>
                            </div>
                            <h5 class="mt-3 mb-2">No Build Step</h5>
                            <p class="text-muted">Zero configuration. Bootstrap loads via CDN. Just start coding!</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-box">
                            <div class="feature-box-icon bg-brand-amber">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <h5 class="mt-3 mb-2">Open Source</h5>
                            <p class="text-muted">Freely usable and accessible. Built for the community.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Toast Examples -->
    <div class="row">
        <div class="col-12">
            <div class="dashboard-card">
                <h4 class="mb-4">Toast Notification Examples</h4>
                <p class="text-muted mb-4">Click the buttons below to see toast notifications in action:</p>
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-success" onclick="showToast('Operation completed successfully!', 'success', 'Success')">
                        <i class="fas fa-check-circle me-2"></i>
                        Success Toast
                    </button>
                    <button class="btn btn-danger" onclick="showToast('An error occurred. Please try again.', 'error', 'Error')">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Error Toast
                    </button>
                    <button class="btn btn-warning" onclick="showToast('This action requires your attention.', 'warning', 'Warning')">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Warning Toast
                    </button>
                    <button class="btn btn-info" onclick="showToast('Here is some useful information for you.', 'info', 'Information')">
                        <i class="fas fa-info-circle me-2"></i>
                        Info Toast
                    </button>
                </div>
                <div class="mt-4">
                    <h6 class="mb-3">Usage in Your Code:</h6>
                    <pre class="bg-dark text-white p-3 rounded" style="font-size: 0.875rem;"><code>// Simple toast
ToastManager.success('Operation completed successfully!');

// With custom title
ToastManager.show('Custom message here', 'info', 'Custom Title');

// Error toast
ToastManager.error('Something went wrong');

// Using the helper function
showToast('Message here', 'success', 'Title');

// All methods available:
ToastManager.success(message, title);
ToastManager.error(message, title);
ToastManager.warning(message, title);
ToastManager.info(message, title);</code></pre>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.brand-teal { color: #31d8b2 !important; }
.brand-blue { color: #204e7e !important; }
.brand-amber { color: #ffa91e !important; }
.bg-brand-teal { background-color: #31d8b2 !important; }
.bg-brand-blue { background-color: #204e7e !important; }
.bg-brand-amber { background-color: #ffa91e !important; }
.bg-brand-teal { background: #31d8b2; }
.bg-brand-blue { background: #204e7e; }
.bg-brand-amber { background: #ffa91e; }

.stat-card {
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.stat-card[data-color="teal"]::before {
    background: linear-gradient(135deg, rgba(49, 216, 178, 0.1) 0%, transparent 100%);
}

.stat-card[data-color="blue"]::before {
    background: linear-gradient(135deg, rgba(32, 78, 126, 0.1) 0%, transparent 100%);
}

.stat-card[data-color="amber"]::before {
    background: linear-gradient(135deg, rgba(255, 169, 30, 0.1) 0%, transparent 100%);
}

.stat-card:hover::before {
    opacity: 1;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 32px rgba(49, 216, 178, 0.15);
}

.stat-icon {
    width: 56px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    color: white;
    font-size: 1.5rem;
}

.interactive-card {
    transition: all 0.3s ease;
}

.interactive-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(49, 216, 178, 0.1);
}

.activity-item {
    transition: all 0.3s ease;
}

.activity-item:hover {
    background: rgba(255, 255, 255, 0.08) !important;
    transform: translateX(5px);
}

.activity-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.quick-action-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-radius: 0.5rem;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
    margin-bottom: 0.5rem;
}

.quick-action-item:hover {
    background: rgba(255, 255, 255, 0.05);
    transform: translateX(5px);
    color: inherit;
}

.quick-action-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    color: white;
    margin-right: 1rem;
}

.feature-showcase {
    background: linear-gradient(135deg, rgba(49, 216, 178, 0.05) 0%, rgba(32, 78, 126, 0.05) 100%);
}

.feature-box {
    text-align: center;
    transition: all 0.3s ease;
    padding: 1.5rem;
    border-radius: 0.75rem;
}

.feature-box:hover {
    transform: translateY(-5px);
}

.feature-box-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    color: white;
    font-size: 1.75rem;
}

[data-theme="light"] .activity-item:hover {
    background: rgba(0, 0, 0, 0.03) !important;
}

[data-theme="light"] .quick-action-item:hover {
    background: rgba(0, 0, 0, 0.03);
}

[data-theme="light"] .feature-showcase {
    background: linear-gradient(135deg, rgba(49, 216, 178, 0.03) 0%, rgba(32, 78, 126, 0.03) 100%);
}

pre code {
    font-family: 'Courier New', monospace;
}

[data-theme="light"] pre {
    background-color: #1e1e28 !important;
    color: #ffffff !important;
}
</style>
@endsection
