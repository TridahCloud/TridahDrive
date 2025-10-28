@extends('layouts.homepage')

@section('title', 'Home')

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="hero-title">Your All-in-One Organizational Platform</h1>
                    <p class="hero-subtitle">
                        TridahDrive is a comprehensive platform for managing drives, invoicing, and business operations. 
                        Join us in building integrated tools that help organizations thrive.
                    </p>
                    <div class="hero-buttons">
                        <a href="{{ route('register') }}" class="btn btn-lg btn-primary">
                            <i class="fas fa-rocket me-2"></i>
                            Get Started
                        </a>
                        <a href="https://github.com/TridahCloud/TridahDrive" target="_blank" class="btn btn-lg btn-outline-light btn-outline-teal">
                            <i class="fab fa-github me-2"></i>
                            Contribute on GitHub
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <div class="logo-animation">
                    <img src="{{ asset('images/tridah icon.png') }}" alt="Tridah" class="img-fluid" style="max-width: 400px; filter: drop-shadow(0 20px 40px rgba(49, 216, 178, 0.3));">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Platform Features</h2>
            <p class="section-subtitle">Everything you need to manage your organization</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #31d8b2 0%, #204e7e 100%);">
                        <i class="fas fa-folder"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Drive Management</h4>
                    <p class="feature-description">
                        Create personal or shared drives for your projects and teams with customizable settings.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #204e7e 0%, #31d8b2 100%);">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Invoicing System</h4>
                    <p class="feature-description">
                        Create professional invoices with client management, custom branding, and project tracking.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #ffa91e 0%, #31d8b2 100%);">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Team Collaboration</h4>
                    <p class="feature-description">
                        Share drives with team members, manage permissions, and collaborate seamlessly.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #204e7e 0%, #ffa91e 100%);">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Customizable Theming</h4>
                    <p class="feature-description">
                        Dark and light mode support with customizable accent colors for branding.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #31d8b2 0%, #ffa91e 100%);">
                        <i class="fas fa-box"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Planned Features</h4>
                    <p class="feature-description">
                        Book Keeping, Staff Management, and more tools are coming soon.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #ffa91e 0%, #204e7e 100%);">
                        <i class="fas fa-code-branch"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Open Source</h4>
                    <p class="feature-description">
                        Built by the community, for the community. Contribute and shape the platform.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section id="about" class="section about-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="feature-card interactive-card">
                    <div class="mb-3">
                        <span class="badge bg-brand-teal me-2 mb-2">About</span>
                        <span class="badge bg-brand-amber mb-2">Nonprofit</span>
                    </div>
                    <h2 class="fw-bold mb-4">About TridahDrive</h2>
                    <p class="lead mb-4 about-text">
                        TridahDrive is a collaborative platform designed to help organizations manage their resources, 
                        finances, and operations all in one place. Built with Laravel and Bootstrap, it's designed to 
                        be both powerful and easy to use.
                    </p>
                    <p class="about-text mb-4">
                        This is a work in progress with new features being added regularly. We're building an 
                        ecosystem of integrated business tools, starting with drives and invoicing.
                    </p>
                    <p class="about-text mb-4">
                        Visit our <a href="https://github.com/TridahCloud/TridahDrive" target="_blank" class="brand-link">GitHub repository</a> 
                        to learn more, contribute, or report issues.
                    </p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="feature-card interactive-card">
                    <h4 class="fw-bold mb-4 brand-teal">Our Mission</h4>
                    <ul class="list-unstyled mission-list">
                        <li class="mission-item">
                            <div class="mission-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <strong>Create personal and shared drives</strong>
                                <p class="mb-0 small">Organize your projects and collaborate with teams</p>
                            </div>
                        </li>
                        <li class="mission-item">
                            <div class="mission-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <strong>Manage invoices professionally</strong>
                                <p class="mb-0 small">Track clients, projects, and payments</p>
                            </div>
                        </li>
                        <li class="mission-item">
                            <div class="mission-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <strong>Collaborate and contribute</strong>
                                <p class="mb-0 small">Help build the tools your organization needs</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section id="contact" class="section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="cta-card">
                    <div class="cta-icon mb-4">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h2 class="fw-bold mb-3">Join the Community</h2>
                    <p class="cta-text mb-4">
                        TridahDrive is open source and looking for contributors! Whether you're a developer, 
                        designer, or project manager, there's a place for you.
                    </p>
                    <div class="d-flex flex-wrap gap-3 justify-content-center">
                        <a href="{{ route('register') }}" class="btn btn-lg btn-success">
                            <i class="fas fa-user-plus me-2"></i>
                            Create Account
                        </a>
                        <a href="https://github.com/TridahCloud/TridahDrive" target="_blank" class="btn btn-lg btn-outline-teal">
                            <i class="fab fa-github me-2"></i>
                            Contribute on GitHub
                        </a>
                        <a href="https://drive.tridah.cloud" target="_blank" class="btn btn-lg btn-outline-light">
                            <i class="fas fa-globe me-2"></i>
                            Try Live Demo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.logo-animation {
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}

.feature-description {
    color: rgba(255, 255, 255, 0.8);
    transition: color 0.3s ease;
}

.mission-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.mission-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: rgba(49, 216, 178, 0.1);
    border-radius: 0.5rem;
    border-left: 3px solid #31d8b2;
    transition: all 0.3s ease;
}

.mission-item:hover {
    transform: translateX(5px);
    background: rgba(49, 216, 178, 0.2);
}

.mission-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #31d8b2 0%, #204e7e 100%);
    border-radius: 50%;
    color: white;
    font-size: 1.25rem;
}

.cta-card {
    background-color: #2a2a3a;
    border-radius: 1.5rem;
    padding: 4rem 2rem;
    border: 2px solid rgba(49, 216, 178, 0.2);
    transition: all 0.3s ease;
}

.cta-card:hover {
    border-color: #31d8b2;
    transform: translateY(-5px);
    box-shadow: 0 16px 48px rgba(49, 216, 178, 0.2);
}

.cta-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto;
    background: linear-gradient(135deg, #31d8b2 0%, #204e7e 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: white;
}

.cta-text {
    color: rgba(255, 255, 255, 0.8);
}

.about-section {
    background: linear-gradient(135deg, #1e1e28 0%, #141420 100%);
}

.interactive-card:hover {
    border-color: rgba(49, 216, 178, 0.3) !important;
}

.about-text {
    color: rgba(255, 255, 255, 0.9);
}

.brand-link {
    color: #31d8b2 !important;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.brand-link:hover {
    color: #ffa91e !important;
}

.btn-outline-teal {
    border: 2px solid #31d8b2;
    color: #31d8b2;
}

.btn-outline-teal:hover {
    background: linear-gradient(135deg, #31d8b2 0%, #204e7e 100%);
    border-color: transparent;
}

[data-theme="light"] .feature-description,
[data-theme="light"] .about-text,
[data-theme="light"] .cta-text {
    color: rgba(30, 30, 40, 0.8) !important;
}

[data-theme="light"] .mission-item {
    background: rgba(49, 216, 178, 0.05);
    border-left-color: #204e7e;
}

[data-theme="light"] .mission-item:hover {
    background: rgba(49, 216, 178, 0.1);
}

[data-theme="light"] .cta-card {
    background-color: #f8f9fa;
    border-color: rgba(49, 216, 178, 0.3);
}

[data-theme="light"] .hero-title {
    background: linear-gradient(135deg, #204e7e 0%, #31d8b2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

[data-theme="light"] .navbar .nav-link {
    color: rgba(30, 30, 40, 0.8) !important;
}

[data-theme="light"] .navbar .nav-link:hover {
    color: #31d8b2 !important;
}
</style>
@endsection
