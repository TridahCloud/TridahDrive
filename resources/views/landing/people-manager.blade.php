@extends('layouts.homepage')

@section('title', 'People Manager')
@section('description', 'Manage employees, volunteers, and contractors with scheduling, time tracking, and payroll. Comprehensive HR solution for businesses and non-profits. Integrated with BookKeeper for seamless financial management.')
@section('keywords', 'HR software, employee management, time tracking, payroll, scheduling, volunteer management, staff management')

@section('content')
<!-- Hero Section -->
<section class="hero-section landing-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="hero-title">People Management Made Simple</h1>
                    <p class="hero-subtitle">
                        Manage employees, volunteers, and contractors all in one place. Track schedules, log hours, process payroll, and keep your team organized with TridahDrive People Manager.
                    </p>
                    <div class="hero-buttons">
                        <a href="{{ route('register') }}" class="btn btn-lg btn-primary">
                            <i class="fas fa-rocket me-2"></i>
                            Get Started Free
                        </a>
                        <a href="#features" class="btn btn-lg btn-outline-light btn-outline-teal">
                            <i class="fas fa-eye me-2"></i>
                            See How It Works
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-preview">
                    <div class="browser-window hero-browser" data-app="people-manager">
                        <div class="browser-header">
                            <div class="browser-controls">
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                            </div>
                            <div class="browser-url">drive.tridah.cloud/people-manager</div>
                        </div>
                        <div class="browser-content">
                            <div class="dashboard-preview p-4">
                                <h5 class="mb-3"><i class="fas fa-users me-2"></i>Team Overview</h5>
                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <div class="stat-card p-3 bg-light rounded">
                                            <h4 class="mb-0">24</h4>
                                            <small class="text-muted">Active People</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-card p-3 bg-light rounded">
                                            <h4 class="mb-0">1,240h</h4>
                                            <small class="text-muted">Hours This Month</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-group">
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <span><strong>John Doe</strong> - Employee</span>
                                            <span class="badge bg-success">Active</span>
                                        </div>
                                    </div>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <span><strong>Jane Smith</strong> - Volunteer</span>
                                            <span class="badge bg-info">On Leave</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="features-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Everything You Need to Manage Your Team</h2>
                <p class="section-subtitle">Comprehensive tools for businesses, non-profits, and teams of all sizes</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-users fa-3x text-primary"></i>
                    </div>
                    <h4>People Management</h4>
                    <p>Manage employees, contractors, and volunteers with detailed profiles, employment information, and custom fields.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-calendar-alt fa-3x text-primary"></i>
                    </div>
                    <h4>Schedule Management</h4>
                    <p>Create schedules, manage shifts, set up recurring schedules, and track who's working when.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-clock fa-3x text-primary"></i>
                    </div>
                    <h4>Time Tracking</h4>
                    <p>Log hours worked, track clock in/out times, manage breaks, and automatically calculate overtime.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-money-check-alt fa-3x text-primary"></i>
                    </div>
                    <h4>Payroll Processing</h4>
                    <p>Calculate payroll, manage deductions, track payments, and sync seamlessly with BookKeeper for accounting.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-chart-line fa-3x text-primary"></i>
                    </div>
                    <h4>Reports & Analytics</h4>
                    <p>View hours worked, payroll summaries, schedule overviews, and team performance metrics.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-link fa-3x text-primary"></i>
                    </div>
                    <h4>Integrated Workflow</h4>
                    <p>Payroll automatically syncs with BookKeeper, keeping your finances and people management in sync.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="mb-3">Ready to Manage Your Team Better?</h2>
        <p class="lead mb-4">Join TridahDrive and start organizing your people, schedules, and payroll today.</p>
        <a href="{{ route('register') }}" class="btn btn-lg btn-light">
            <i class="fas fa-rocket me-2"></i>Get Started Free
        </a>
    </div>
</section>
@endsection
