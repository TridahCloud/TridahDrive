@extends('layouts.homepage')

@section('title', 'Home')

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="hero-title">Your All-in-One Business Management Platform</h1>
                    <p class="hero-subtitle">
                        TridahDrive combines <strong>Invoicer</strong>, <strong>BookKeeper</strong>, and <strong>Project Board</strong> 
                        into one integrated platform. Manage invoices, track finances, and organize projects all in one place.
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
        
        <div class="row g-5 mb-5">
            <!-- Interactive App Previews -->
            <div class="col-md-4">
                <div class="app-preview-container">
                    <h4 class="fw-bold mb-3 text-center">
                        <a href="{{ route('landing.invoicer') }}" class="text-decoration-none brand-link-hover">Invoicer</a>
                    </h4>
                    <a href="{{ route('landing.invoicer') }}" class="text-decoration-none">
                        <div class="browser-window" data-app="invoicer">
                        <div class="browser-header">
                            <div class="browser-controls">
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                            </div>
                            <div class="browser-url">drive.tridah.cloud/invoices</div>
                        </div>
                        <div class="browser-content invoice-preview">
                            <div class="preview-toolbar">
                                <button class="preview-btn btn-sm"><i class="fas fa-plus"></i> New Invoice</button>
                                <div class="preview-dropdown">
                                    <span>Status: All</span>
                                </div>
                            </div>
                            <div class="preview-table">
                                <div class="preview-row">
                                    <div class="preview-cell"><strong>INV-000001</strong></div>
                                    <div class="preview-cell">Acme Corp</div>
                                    <div class="preview-cell text-success"><strong>$1,250.00</strong></div>
                                    <div class="preview-cell"><span class="preview-badge bg-success">Paid</span></div>
                                </div>
                                <div class="preview-row">
                                    <div class="preview-cell"><strong>INV-000002</strong></div>
                                    <div class="preview-cell">Tech Solutions</div>
                                    <div class="preview-cell text-success"><strong>$850.00</strong></div>
                                    <div class="preview-cell"><span class="preview-badge bg-info">Sent</span></div>
                                </div>
                                <div class="preview-row">
                                    <div class="preview-cell"><strong>INV-000003</strong></div>
                                    <div class="preview-cell">Global Inc</div>
                                    <div class="preview-cell text-success"><strong>$2,400.00</strong></div>
                                    <div class="preview-cell"><span class="preview-badge bg-secondary">Draft</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </a>
                    <p class="text-center mt-3 feature-description">
                        Create professional invoices with client management, custom branding, item catalogs, and project tracking.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="app-preview-container">
                    <h4 class="fw-bold mb-3 text-center">
                        <a href="{{ route('landing.bookkeeper') }}" class="text-decoration-none brand-link-hover">BookKeeper</a>
                    </h4>
                    <a href="{{ route('landing.bookkeeper') }}" class="text-decoration-none">
                        <div class="browser-window" data-app="bookkeeper">
                        <div class="browser-header">
                            <div class="browser-controls">
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                            </div>
                            <div class="browser-url">drive.tridah.cloud/bookkeeper</div>
                        </div>
                        <div class="browser-content bookkeeper-preview">
                            <div class="preview-stats">
                                <div class="preview-stat-card">
                                    <div class="stat-label">Income</div>
                                    <div class="stat-value text-success">$5,420</div>
                                </div>
                                <div class="preview-stat-card">
                                    <div class="stat-label">Expenses</div>
                                    <div class="stat-value text-danger">$2,180</div>
                                </div>
                            </div>
                            <div class="preview-transactions">
                                <div class="preview-transaction">
                                    <div class="transaction-icon income"><i class="fas fa-arrow-down"></i></div>
                                    <div class="transaction-details">
                                        <strong>Payment Received</strong>
                                        <small>Checking Account</small>
                                    </div>
                                    <div class="transaction-amount text-success">+$1,250</div>
                                </div>
                                <div class="preview-transaction">
                                    <div class="transaction-icon expense"><i class="fas fa-arrow-up"></i></div>
                                    <div class="transaction-details">
                                        <strong>Office Supplies</strong>
                                        <small>Credit Card</small>
                                    </div>
                                    <div class="transaction-amount text-danger">-$85</div>
                                </div>
                                <div class="preview-transaction">
                                    <div class="transaction-icon income"><i class="fas fa-arrow-down"></i></div>
                                    <div class="transaction-details">
                                        <strong>Service Payment</strong>
                                        <small>Savings Account</small>
                                    </div>
                                    <div class="transaction-amount text-success">+$850</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </a>
                    <p class="text-center mt-3 feature-description">
                        Complete accounting solution with transaction management, accounts, categories, recurring transactions, and tax reports.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="app-preview-container">
                    <h4 class="fw-bold mb-3 text-center">
                        <a href="{{ route('landing.project-board') }}" class="text-decoration-none brand-link-hover">Project Board</a>
                    </h4>
                    <a href="{{ route('landing.project-board') }}" class="text-decoration-none">
                        <div class="browser-window" data-app="project">
                        <div class="browser-header">
                            <div class="browser-controls">
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                            </div>
                            <div class="browser-url">drive.tridah.cloud/projects</div>
                        </div>
                        <div class="browser-content project-preview">
                            <div class="kanban-board">
                                <div class="kanban-column">
                                    <div class="kanban-header">To Do</div>
                                    <div class="kanban-task">
                                        <div class="task-label bg-info"></div>
                                        <strong>Design Mockups</strong>
                                        <small>High Priority</small>
                                    </div>
                                    <div class="kanban-task">
                                        <div class="task-label bg-warning"></div>
                                        <strong>Update Docs</strong>
                                        <small>Medium Priority</small>
                                    </div>
                                </div>
                                <div class="kanban-column">
                                    <div class="kanban-header">In Progress</div>
                                    <div class="kanban-task">
                                        <div class="task-label bg-success"></div>
                                        <strong>API Integration</strong>
                                        <small>Assigned to John</small>
                                    </div>
                                </div>
                                <div class="kanban-column">
                                    <div class="kanban-header">Done</div>
                                    <div class="kanban-task">
                                        <div class="task-label bg-secondary"></div>
                                        <strong>User Testing</strong>
                                        <small>Completed</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </a>
                    <p class="text-center mt-3 feature-description">
                        Manage projects with kanban boards, tasks, labels, comments, and team collaboration.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            
            <!-- Supporting Features -->
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #31d8b2 0%, #ffa91e 100%);">
                        <i class="fas fa-folder"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Drive Management</h4>
                    <p class="feature-description">
                        Create personal or shared drives for your projects and teams with customizable settings. 
                        Organize all your work in one place.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #204e7e 0%, #ffa91e 100%);">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Team Collaboration</h4>
                    <p class="feature-description">
                        Share drives with team members, manage permissions, and collaborate seamlessly across all applications.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #ffa91e 0%, #204e7e 100%);">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Customizable Theming</h4>
                    <p class="feature-description">
                        Dark and light mode support with customizable accent colors and multi-currency support for international businesses.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #31d8b2 0%, #204e7e 100%);">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Recurring Transactions</h4>
                    <p class="feature-description">
                        Set up automated recurring income and expenses with flexible scheduling options. 
                        Advanced recurrence patterns for complex business needs.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #204e7e 0%, #31d8b2 100%);">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Financial Reports</h4>
                    <p class="feature-description">
                        Generate comprehensive tax reports, track income and expenses by category, 
                        and export data for CPA review. Perfect for tax filing.
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
                        Built by the community, for the community. Contribute and shape the platform to fit your needs.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section id="pricing" class="section pricing-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Transparent Pricing</h2>
            <p class="section-subtitle mb-4">All plans cost exactly $0 - because we believe open-source software should be accessible to everyone</p>
            <div class="price-disclaimer">
                <span class="badge bg-brand-teal px-3 py-2">
                    <i class="fas fa-heart me-2"></i>
                    No hidden fees, no credit card required, no catch
                </span>
            </div>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- Starter Plan -->
            <div class="col-lg-4 col-md-6">
                <div class="pricing-card">
                    <div class="pricing-header">
                        <div class="pricing-icon" style="background: linear-gradient(135deg, #31d8b2 0%, #204e7e 100%);">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h4 class="pricing-name">Starter</h4>
                        <div class="pricing-price">
                            <span class="price-amount">$0</span>
                        </div>
                        <p class="pricing-tagline">Perfect for exploring</p>
                    </div>
                    <div class="pricing-body">
                        <ul class="pricing-features">
                            <li><i class="fas fa-check-circle text-success"></i> Personal Drive</li>
                            <li><i class="fas fa-check-circle text-success"></i> Unlimited Invoices</li>
                            <li><i class="fas fa-check-circle text-success"></i> Transaction Tracking</li>
                            <li><i class="fas fa-check-circle text-success"></i> Basic Projects</li>
                            <li><i class="fas fa-check-circle text-success"></i> Community Support</li>
                        </ul>
                        <div class="pricing-cta">
                            <a href="{{ route('register') }}" class="btn btn-outline-teal w-100">
                                <i class="fas fa-rocket me-2"></i>
                                Get Started Free
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Professional Plan -->
            <div class="col-lg-4 col-md-6">
                <div class="pricing-card featured">
                    <div class="pricing-badge">Most Popular</div>
                    <div class="pricing-header">
                        <div class="pricing-icon" style="background: linear-gradient(135deg, #204e7e 0%, #ffa91e 100%);">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <h4 class="pricing-name">Professional</h4>
                        <div class="pricing-price">
                            <span class="price-amount">$0</span>
                        </div>
                        <p class="pricing-tagline">For growing businesses</p>
                    </div>
                    <div class="pricing-body">
                        <ul class="pricing-features">
                            <li><i class="fas fa-check-circle text-success"></i> Everything in Starter</li>
                            <li><i class="fas fa-check-circle text-success"></i> Shared Drives</li>
                            <li><i class="fas fa-check-circle text-success"></i> Team Collaboration</li>
                            <li><i class="fas fa-check-circle text-success"></i> Advanced Accounting</li>
                            <li><i class="fas fa-check-circle text-success"></i> Tax Reports</li>
                            <li><i class="fas fa-check-circle text-success"></i> Priority on GitHub</li>
                        </ul>
                        <div class="pricing-cta">
                            <a href="{{ route('register') }}" class="btn btn-primary w-100">
                                <i class="fas fa-rocket me-2"></i>
                                Get Started Free
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enterprise Plan -->
            <div class="col-lg-4 col-md-6">
                <div class="pricing-card">
                    <div class="pricing-header">
                        <div class="pricing-icon" style="background: linear-gradient(135deg, #ffa91e 0%, #204e7e 100%);">
                            <i class="fas fa-building"></i>
                        </div>
                        <h4 class="pricing-name">Enterprise</h4>
                        <div class="pricing-price">
                            <span class="price-amount">$0</span>
                        </div>
                        <p class="pricing-tagline">For organizations</p>
                    </div>
                    <div class="pricing-body">
                        <ul class="pricing-features">
                            <li><i class="fas fa-check-circle text-success"></i> Everything in Professional</li>
                            <li><i class="fas fa-check-circle text-success"></i> Unlimited Drives</li>
                            <li><i class="fas fa-check-circle text-success"></i> Multi-Currency Support</li>
                            <li><i class="fas fa-check-circle text-success"></i> Recurring Transactions</li>
                            <li><i class="fas fa-check-circle text-success"></i> Custom Branding</li>
                            <li><i class="fas fa-check-circle text-success"></i> Full API Access</li>
                        </ul>
                        <div class="pricing-cta">
                            <a href="{{ route('register') }}" class="btn btn-outline-teal w-100">
                                <i class="fas fa-rocket me-2"></i>
                                Get Started Free
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fun Disclaimer -->
        <div class="row mt-5">
            <div class="col-lg-8 offset-lg-2">
                <div class="pricing-note">
                    <div class="note-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="note-content">
                        <h5 class="fw-bold mb-2">Wait, really $0?</h5>
                        <p class="mb-0">
                            Yes, really! TridahDrive is 100% open source and free forever. We're a nonprofit organization 
                            dedicated to creating freely usable software. There are no paid tiers, no premium features behind a paywall, 
                            and no subscription fees. The only "cost" is your time - and we're grateful you're spending it with us.
                        </p>
                        <p class="mt-3 mb-0">
                            <strong>Curious how we do it?</strong> Check out our <a href="https://github.com/TridahCloud/TridahDrive" class="brand-link">GitHub repository</a> 
                            and see the code for yourself. If you find it useful, consider contributing back to the project!
                        </p>
                    </div>
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
                        TridahDrive is a comprehensive business management platform designed to help organizations manage 
                        their finances, projects, and operations all in one place. Built with Laravel and Bootstrap, it's 
                        designed to be both powerful and easy to use.
                    </p>
                    <p class="about-text mb-4">
                        Our platform includes three main applications: <strong>Invoicer</strong> for professional invoicing 
                        and client management, <strong>BookKeeper</strong> for complete accounting and financial tracking, 
                        and <strong>Project Board</strong> for task management and team collaboration. All integrated seamlessly 
                        within customizable drives.
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
                                <strong>Professional Invoicing</strong>
                                <p class="mb-0 small">Create branded invoices, manage clients, and track payments with Invoicer</p>
                            </div>
                        </li>
                        <li class="mission-item">
                            <div class="mission-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <strong>Complete Accounting</strong>
                                <p class="mb-0 small">Track income, expenses, generate tax reports, and manage finances with BookKeeper</p>
                            </div>
                        </li>
                        <li class="mission-item">
                            <div class="mission-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <strong>Project Management</strong>
                                <p class="mb-0 small">Organize tasks, collaborate with teams, and track progress with Project Board</p>
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

/* Pricing Section Styles */
.pricing-section {
    background: linear-gradient(135deg, #1a1a24 0%, #141420 100%);
}

.price-disclaimer {
    display: flex;
    justify-content: center;
    margin-top: 1rem;
}

.pricing-card {
    background-color: #2a2a3a;
    border-radius: 1rem;
    padding: 2.5rem 2rem;
    border: 2px solid rgba(49, 216, 178, 0.2);
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    position: relative;
}

.pricing-card:hover {
    border-color: #31d8b2;
    transform: translateY(-10px);
    box-shadow: 0 20px 60px rgba(49, 216, 178, 0.15);
}

.pricing-card.featured {
    border-color: #31d8b2;
    background: linear-gradient(135deg, #2a2a3a 0%, #1e1e28 100%);
}

.pricing-badge {
    position: absolute;
    top: -15px;
    right: 20px;
    background: linear-gradient(135deg, #31d8b2 0%, #204e7e 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(49, 216, 178, 0.3);
}

.pricing-header {
    text-align: center;
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid rgba(49, 216, 178, 0.2);
}

.pricing-icon {
    width: 70px;
    height: 70px;
    margin: 0 auto 1.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
}

.pricing-name {
    font-size: 1.75rem;
    font-weight: bold;
    color: #31d8b2;
    margin-bottom: 0.5rem;
}

.pricing-price {
    margin: 1rem 0;
}

.price-amount {
    font-size: 3rem;
    font-weight: bold;
    background: linear-gradient(135deg, #31d8b2 0%, #204e7e 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    line-height: 1;
}

.pricing-tagline {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.95rem;
    margin-top: 0.5rem;
}

.pricing-body {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.pricing-features {
    list-style: none;
    padding: 0;
    margin: 0 0 2rem;
    flex-grow: 1;
}

.pricing-features li {
    padding: 0.75rem 0;
    color: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    font-size: 0.95rem;
}

.pricing-features li i {
    margin-right: 0.75rem;
    font-size: 1.1rem;
}

.pricing-cta {
    margin-top: auto;
}

.pricing-note {
    background: rgba(49, 216, 178, 0.1);
    border-radius: 1rem;
    padding: 2rem;
    border: 2px solid rgba(49, 216, 178, 0.2);
    display: flex;
    gap: 1.5rem;
    align-items: flex-start;
    transition: all 0.3s ease;
}

.pricing-note:hover {
    background: rgba(49, 216, 178, 0.15);
    border-color: rgba(49, 216, 178, 0.3);
    transform: translateY(-5px);
}

.note-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #31d8b2 0%, #204e7e 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    flex-shrink: 0;
}

.note-content h5 {
    color: #31d8b2;
}

.note-content p {
    color: rgba(255, 255, 255, 0.85);
    line-height: 1.7;
}

/* Light theme overrides for pricing */
[data-theme="light"] .pricing-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

[data-theme="light"] .pricing-card {
    background-color: #ffffff;
    border-color: rgba(32, 78, 126, 0.2);
}

[data-theme="light"] .pricing-card.featured {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-color: #204e7e;
}

[data-theme="light"] .pricing-card:hover {
    border-color: #204e7e;
    box-shadow: 0 20px 60px rgba(32, 78, 126, 0.1);
}

[data-theme="light"] .pricing-header {
    border-bottom-color: rgba(32, 78, 126, 0.2);
}

[data-theme="light"] .price-amount {
    background: linear-gradient(135deg, #204e7e 0%, #31d8b2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

[data-theme="light"] .pricing-tagline {
    color: rgba(30, 30, 40, 0.7);
}

[data-theme="light"] .pricing-features li {
    color: rgba(30, 30, 40, 0.9);
}

[data-theme="light"] .pricing-note {
    background: rgba(32, 78, 126, 0.05);
    border-color: rgba(32, 78, 126, 0.2);
}

[data-theme="light"] .pricing-note:hover {
    background: rgba(32, 78, 126, 0.1);
    border-color: rgba(32, 78, 126, 0.3);
}

[data-theme="light"] .note-content h5 {
    color: #204e7e;
}

[data-theme="light"] .note-content p {
    color: rgba(30, 30, 40, 0.85);
}

/* Interactive App Preview Styles */
.app-preview-container {
    position: relative;
    perspective: 1200px;
    transform-style: preserve-3d;
}

.browser-window {
    background: #1e1e2e;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4),
                0 0 0 1px rgba(49, 216, 178, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.05);
    transform: translateZ(0) rotateX(2deg);
    transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
    position: relative;
    z-index: 1;
}

.app-preview-container:hover .browser-window {
    transform: translateY(-15px) translateZ(20px) rotateX(-5deg) rotateY(2deg);
    box-shadow: 0 30px 80px rgba(49, 216, 178, 0.2),
                0 0 0 1px rgba(49, 216, 178, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

.app-preview-container a {
    cursor: pointer;
}

.brand-link-hover {
    color: #31d8b2 !important;
    transition: color 0.3s ease;
}

.brand-link-hover:hover {
    color: #ffa91e !important;
}

.browser-header {
    background: linear-gradient(180deg, #2a2a3a 0%, #1e1e2e 100%);
    padding: 10px 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    border-bottom: 1px solid rgba(49, 216, 178, 0.1);
}

.browser-controls {
    display: flex;
    gap: 6px;
}

.browser-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: inline-block;
}

.browser-dot:nth-child(1) { background: #ff5f56; }
.browser-dot:nth-child(2) { background: #ffbd2e; }
.browser-dot:nth-child(3) { background: #27c93f; }

.browser-url {
    flex: 1;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 4px;
    padding: 4px 10px;
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.6);
    font-family: 'Courier New', monospace;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.browser-content {
    padding: 12px;
    background: #1a1a24;
    min-height: 280px;
    position: relative;
    overflow: hidden;
}

/* Invoice Preview Styles */
.invoice-preview {
    background: #1a1a24;
}

.preview-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    gap: 8px;
}

.preview-btn {
    background: linear-gradient(135deg, #31d8b2 0%, #204e7e 100%);
    border: none;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.75rem;
    cursor: pointer;
    transition: transform 0.2s;
}

.preview-btn:hover {
    transform: scale(1.05);
}

.preview-dropdown {
    background: rgba(255, 255, 255, 0.05);
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.7);
}

.preview-table {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.preview-row {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(49, 216, 178, 0.1);
    border-radius: 6px;
    padding: 10px;
    display: grid;
    grid-template-columns: 1fr 1fr 1fr auto;
    gap: 8px;
    font-size: 0.75rem;
    transition: all 0.3s;
    transform: translateZ(0);
}

.preview-row:hover {
    background: rgba(49, 216, 178, 0.1);
    border-color: rgba(49, 216, 178, 0.3);
    transform: translateX(5px) translateZ(5px);
    box-shadow: 0 4px 12px rgba(49, 216, 178, 0.2);
}

.preview-cell {
    color: rgba(255, 255, 255, 0.9);
    overflow: hidden;
    text-overflow: ellipsis;
}

.preview-badge {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.65rem;
    font-weight: 600;
}

/* BookKeeper Preview Styles */
.bookkeeper-preview {
    background: #1a1a24;
}

.preview-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 12px;
}

.preview-stat-card {
    background: rgba(49, 216, 178, 0.1);
    border: 1px solid rgba(49, 216, 178, 0.2);
    border-radius: 8px;
    padding: 12px;
    text-align: center;
    transition: all 0.3s;
}

.preview-stat-card:hover {
    transform: translateY(-3px) translateZ(5px);
    box-shadow: 0 6px 20px rgba(49, 216, 178, 0.2);
    border-color: rgba(49, 216, 178, 0.4);
}

.stat-label {
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.6);
    margin-bottom: 4px;
}

.stat-value {
    font-size: 1.2rem;
    font-weight: bold;
}

.preview-transactions {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.preview-transaction {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(49, 216, 178, 0.1);
    border-radius: 8px;
    padding: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s;
    transform: translateZ(0);
}

.preview-transaction:hover {
    background: rgba(49, 216, 178, 0.1);
    border-color: rgba(49, 216, 178, 0.3);
    transform: translateX(5px) translateZ(5px);
}

.transaction-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    flex-shrink: 0;
}

.transaction-icon.income {
    background: rgba(40, 167, 69, 0.2);
    color: #28a745;
}

.transaction-icon.expense {
    background: rgba(220, 53, 69, 0.2);
    color: #dc3545;
}

.transaction-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.transaction-details strong {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.9);
}

.transaction-details small {
    font-size: 0.65rem;
    color: rgba(255, 255, 255, 0.5);
}

.transaction-amount {
    font-weight: bold;
    font-size: 0.85rem;
}

/* Project Board Preview Styles */
.project-preview {
    background: #1a1a24;
}

.kanban-board {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}

.kanban-column {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(49, 216, 178, 0.1);
    border-radius: 8px;
    padding: 8px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-height: 200px;
}

.kanban-header {
    font-size: 0.7rem;
    font-weight: bold;
    color: rgba(255, 255, 255, 0.7);
    padding: 4px 0;
    border-bottom: 1px solid rgba(49, 216, 178, 0.1);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.kanban-task {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(49, 216, 178, 0.1);
    border-radius: 6px;
    padding: 8px;
    font-size: 0.7rem;
    transition: all 0.3s;
    transform: translateZ(0);
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.kanban-task:hover {
    background: rgba(49, 216, 178, 0.1);
    border-color: rgba(49, 216, 178, 0.3);
    transform: translateY(-2px) translateZ(5px);
    box-shadow: 0 4px 12px rgba(49, 216, 178, 0.2);
}

.task-label {
    position: absolute;
    top: 6px;
    right: 6px;
    width: 4px;
    height: 20px;
    border-radius: 2px;
}

.kanban-task strong {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.75rem;
}

.kanban-task small {
    color: rgba(255, 255, 255, 0.5);
    font-size: 0.65rem;
}

/* Light theme overrides for previews */
[data-theme="light"] .browser-window {
    background: #ffffff;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15),
                0 0 0 1px rgba(32, 78, 126, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
}

[data-theme="light"] .app-preview-container:hover .browser-window {
    box-shadow: 0 30px 80px rgba(32, 78, 126, 0.15),
                0 0 0 1px rgba(32, 78, 126, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.95);
}

[data-theme="light"] .browser-header {
    background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom-color: rgba(32, 78, 126, 0.1);
}

[data-theme="light"] .browser-url {
    background: rgba(0, 0, 0, 0.05);
    color: rgba(30, 30, 40, 0.6);
}

[data-theme="light"] .browser-content {
    background: #ffffff;
}

[data-theme="light"] .invoice-preview,
[data-theme="light"] .bookkeeper-preview,
[data-theme="light"] .project-preview {
    background: #ffffff;
}

[data-theme="light"] .preview-row,
[data-theme="light"] .preview-transaction,
[data-theme="light"] .kanban-task {
    background: rgba(32, 78, 126, 0.02);
    border-color: rgba(32, 78, 126, 0.1);
    color: rgba(30, 30, 40, 0.9);
}

[data-theme="light"] .preview-row:hover,
[data-theme="light"] .preview-transaction:hover,
[data-theme="light"] .kanban-task:hover {
    background: rgba(32, 78, 126, 0.08);
    border-color: rgba(32, 78, 126, 0.3);
}

[data-theme="light"] .preview-cell,
[data-theme="light"] .transaction-details strong,
[data-theme="light"] .kanban-task strong {
    color: rgba(30, 30, 40, 0.9);
}

[data-theme="light"] .preview-dropdown,
[data-theme="light"] .transaction-details small,
[data-theme="light"] .kanban-task small {
    color: rgba(30, 30, 40, 0.6);
}

[data-theme="light"] .preview-stat-card {
    background: rgba(32, 78, 126, 0.05);
    border-color: rgba(32, 78, 126, 0.2);
}

[data-theme="light"] .stat-label {
    color: rgba(30, 30, 40, 0.6);
}

[data-theme="light"] .kanban-column {
    background: rgba(32, 78, 126, 0.02);
    border-color: rgba(32, 78, 126, 0.1);
}

[data-theme="light"] .kanban-header {
    color: rgba(30, 30, 40, 0.7);
    border-bottom-color: rgba(32, 78, 126, 0.1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add interactive animations to browser windows
    const browserWindows = document.querySelectorAll('.browser-window');
    
    browserWindows.forEach(window => {
        const container = window.closest('.app-preview-container');
        
        // Add mouse move parallax effect
        container.addEventListener('mousemove', function(e) {
            const rect = container.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const rotateX = ((y - centerY) / centerY) * -5;
            const rotateY = ((x - centerX) / centerX) * 5;
            
            window.style.transform = `translateY(-15px) translateZ(20px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
        });
        
        container.addEventListener('mouseleave', function() {
            window.style.transform = 'translateZ(0) rotateX(2deg)';
        });
        
        // Add floating animation to elements inside
        const interactiveElements = window.querySelectorAll('.preview-row, .preview-transaction, .kanban-task');
        interactiveElements.forEach((el, index) => {
            el.style.animationDelay = `${index * 0.1}s`;
        });
    });
    
    // Animate stat cards on hover
    const statCards = document.querySelectorAll('.preview-stat-card');
    statCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.animation = 'pulse 0.6s ease-in-out';
        });
    });
    
    // Add subtle pulse animation to badges
    const badges = document.querySelectorAll('.preview-badge');
    badges.forEach(badge => {
        setInterval(() => {
            badge.style.transform = 'scale(1.05)';
            setTimeout(() => {
                badge.style.transform = 'scale(1)';
            }, 200);
        }, 3000 + Math.random() * 2000);
    });
});
</script>
@endsection
