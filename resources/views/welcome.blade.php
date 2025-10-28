@extends('layouts.homepage')

@section('title', 'Dashboard')

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="hero-title">Build the Future with Open Source</h1>
                    <p class="hero-subtitle">
                        Tridah is a nonprofit dedicated to creating freely usable and accessible open-source software. 
                        Join us in building solutions that benefit everyone.
                    </p>
                    <div class="hero-buttons">
                        <a href="#about" class="btn btn-lg btn-primary">
                            <i class="fas fa-info-circle me-2"></i>
                            Learn More
                        </a>
                        <a href="https://tridah.cloud" target="_blank" class="btn btn-lg btn-outline-light btn-outline-teal">
                            <i class="fas fa-globe me-2"></i>
                            Visit Website
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
            <h2 class="section-title">Why Choose Our Framework</h2>
            <p class="section-subtitle">Built for modern, scalable applications</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #31d8b2 0%, #204e7e 100%);">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Fast & Modern</h4>
                    <p class="feature-description">
                        Built on Laravel 12 with Bootstrap 5.3, ensuring the latest technologies and performance.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #204e7e 0%, #31d8b2 100%);">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Dark & Light Themes</h4>
                    <p class="feature-description">
                        Seamless theme switching with persistent user preferences.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #ffa91e 0%, #31d8b2 100%);">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Fully Responsive</h4>
                    <p class="feature-description">
                        Perfect on all devices - desktop, tablet, and mobile.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #204e7e 0%, #ffa91e 100%);">
                        <i class="fas fa-puzzle-piece"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Modular Architecture</h4>
                    <p class="feature-description">
                        Component-based design for easy maintenance and scalability.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #31d8b2 0%, #ffa91e 100%);">
                        <i class="fas fa-code"></i>
                    </div>
                    <h4 class="fw-bold mb-3">No Build Process</h4>
                    <p class="feature-description">
                        Zero configuration - just start coding. Bootstrap loads via CDN.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #ffa91e 0%, #204e7e 100%);">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Open Source</h4>
                    <p class="feature-description">
                        Freely usable and accessible for everyone.
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
                    <h2 class="fw-bold mb-4">About Tridah</h2>
                    <p class="lead mb-4 about-text">
                        We're a nonprofit organization committed to creating open-source software that's freely 
                        usable and accessible to everyone. Our framework template is designed to help you build 
                        modern, scalable applications quickly.
                    </p>
                    <p class="about-text mb-4">
                        Visit our website at <a href="https://tridah.cloud" target="_blank" class="brand-link">tridah.cloud</a> 
                        to learn more about our mission and other projects.
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
                                <strong>Create freely usable open-source software</strong>
                            </div>
                        </li>
                        <li class="mission-item">
                            <div class="mission-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <strong>Make technology accessible to everyone</strong>
                            </div>
                        </li>
                        <li class="mission-item">
                            <div class="mission-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <strong>Support the open-source community</strong>
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
                    <h2 class="fw-bold mb-3">Ready to Get Started?</h2>
                    <p class="cta-text mb-4">
                        Start building your next application with our modern, responsive framework template.
                    </p>
                    <div class="d-flex flex-wrap gap-3 justify-content-center">
                        <a href="{{ url('/dashboard') }}" class="btn btn-lg btn-success">
                            <i class="fas fa-rocket me-2"></i>
                            Launch Dashboard
                        </a>
                        <a href="{{ url('/example') }}" class="btn btn-lg btn-outline-teal">
                            <i class="fas fa-code me-2"></i>
                            View Examples
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
