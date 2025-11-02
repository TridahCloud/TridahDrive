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
        
        <div class="row g-4">
            <!-- Main Applications -->
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #204e7e 0%, #31d8b2 100%);">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Invoicer</h4>
                    <p class="feature-description">
                        Create professional invoices with client management, custom branding, item catalogs, and project tracking. 
                        Multi-currency support with customizable invoice profiles.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #31d8b2 0%, #204e7e 100%);">
                        <i class="fas fa-book"></i>
                    </div>
                    <h4 class="fw-bold mb-3">BookKeeper</h4>
                    <p class="feature-description">
                        Complete accounting solution with transaction management, accounts, categories, recurring transactions, 
                        tax reports, and multi-currency support. Perfect for tracking income and expenses.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #ffa91e 0%, #31d8b2 100%);">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Project Board</h4>
                    <p class="feature-description">
                        Manage projects with kanban boards, tasks, labels, comments, and team collaboration. 
                        Track progress, assign tasks, and keep your team organized.
                    </p>
                </div>
            </div>
            
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
</style>
@endsection
