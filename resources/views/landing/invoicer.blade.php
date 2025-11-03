@extends('layouts.homepage')

@section('title', 'Invoicer')
@section('description', 'Create professional invoices with client management, custom branding, item catalogs, and project tracking. Multi-currency support with customizable invoice profiles. Get paid faster with TridahDrive Invoicer.')
@section('keywords', 'invoice software, invoicing, professional invoices, client management, invoice generation, billing software, invoice templates')

@section('content')
<!-- Hero Section -->
<section class="hero-section landing-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="hero-title">Professional Invoicing Made Simple</h1>
                    <p class="hero-subtitle">
                        Create beautiful, branded invoices in seconds. Manage clients, track payments, and get paid faster with TridahDrive Invoicer.
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
                    <div class="browser-window hero-browser" data-app="invoicer">
                        <div class="browser-header">
                            <div class="browser-controls">
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                            </div>
                            <div class="browser-url">drive.tridah.cloud/invoices/new</div>
                        </div>
                        <div class="browser-content invoice-preview-large">
                            <div class="invoice-form-preview">
                                <div class="form-header">
                                    <h4 class="mb-0">New Invoice</h4>
                                    <span class="badge bg-info">Draft</span>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Client</label>
                                        <select class="form-select form-select-sm">
                                            <option>Acme Corporation</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Invoice Date</label>
                                        <input type="date" class="form-control form-control-sm" value="2025-01-15">
                                    </div>
                                </div>
                                <div class="invoice-items">
                                    <div class="item-row">
                                        <span class="item-name">Web Design Services</span>
                                        <span class="item-qty">1</span>
                                        <span class="item-price">$1,500.00</span>
                                        <span class="item-total">$1,500.00</span>
                                    </div>
                                    <div class="item-row">
                                        <span class="item-name">Development Hours</span>
                                        <span class="item-qty">20</span>
                                        <span class="item-price">$125.00</span>
                                        <span class="item-total">$2,500.00</span>
                                    </div>
                                </div>
                                <div class="invoice-totals">
                                    <div class="total-row">
                                        <span>Subtotal</span>
                                        <strong>$4,000.00</strong>
                                    </div>
                                    <div class="total-row">
                                        <span>Tax (10%)</span>
                                        <strong>$400.00</strong>
                                    </div>
                                    <div class="total-row grand-total">
                                        <span>Total</span>
                                        <strong>$4,400.00</strong>
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
<section id="features" class="section features-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Everything You Need to Invoice Professionally</h2>
            <p class="section-subtitle">Streamline your invoicing workflow with powerful features</p>
        </div>
        
        <div class="row g-5 mb-5">
            <!-- Feature 1: Invoice Management -->
            <div class="col-lg-6">
                <div class="feature-content">
                    <div class="feature-icon-large">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <h3 class="feature-heading">Easy Invoice Management</h3>
                    <p class="feature-text">
                        Create, send, and track invoices effortlessly. Manage all your invoices in one place with status tracking, payment reminders, and detailed history.
                    </p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check text-success"></i> Create invoices in seconds</li>
                        <li><i class="fas fa-check text-success"></i> Track payment status</li>
                        <li><i class="fas fa-check text-success"></i> Automatic numbering</li>
                        <li><i class="fas fa-check text-success"></i> Email invoices directly</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="app-preview-container feature-preview">
                    <div class="browser-window" data-app="invoicer-list">
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
                                    <div class="preview-cell"><span class="preview-badge bg-warning">Overdue</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-5 mb-5">
            <!-- Feature 2: Client Management -->
            <div class="col-lg-6 order-lg-2">
                <div class="feature-content">
                    <div class="feature-icon-large">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="feature-heading">Client Management</h3>
                    <p class="feature-text">
                        Organize all your client information in one place. Store contact details, payment terms, and invoice history for each client.
                    </p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check text-success"></i> Centralized client database</li>
                        <li><i class="fas fa-check text-success"></i> Quick client selection</li>
                        <li><i class="fas fa-check text-success"></i> Payment history tracking</li>
                        <li><i class="fas fa-check text-success"></i> Client notes and details</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6 order-lg-1">
                <div class="app-preview-container feature-preview">
                    <div class="browser-window" data-app="invoicer-clients">
                        <div class="browser-header">
                            <div class="browser-controls">
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                            </div>
                            <div class="browser-url">drive.tridah.cloud/clients</div>
                        </div>
                        <div class="browser-content invoice-preview">
                            <div class="preview-toolbar">
                                <button class="preview-btn btn-sm"><i class="fas fa-plus"></i> Add Client</button>
                                <input type="search" class="preview-search" placeholder="Search clients...">
                            </div>
                            <div class="preview-table">
                                <div class="preview-row">
                                    <div class="preview-cell"><strong>Acme Corporation</strong></div>
                                    <div class="preview-cell">contact@acme.com</div>
                                    <div class="preview-cell">12 Invoices</div>
                                    <div class="preview-cell text-success">$24,500</div>
                                </div>
                                <div class="preview-row">
                                    <div class="preview-cell"><strong>Tech Solutions Inc</strong></div>
                                    <div class="preview-cell">info@techsolutions.com</div>
                                    <div class="preview-cell">8 Invoices</div>
                                    <div class="preview-cell text-success">$18,200</div>
                                </div>
                                <div class="preview-row">
                                    <div class="preview-cell"><strong>Global Industries</strong></div>
                                    <div class="preview-cell">hello@global.com</div>
                                    <div class="preview-cell">5 Invoices</div>
                                    <div class="preview-cell text-success">$9,800</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-5">
            <!-- Feature 3: Custom Branding -->
            <div class="col-lg-6">
                <div class="feature-content">
                    <div class="feature-icon-large">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h3 class="feature-heading">Custom Branding</h3>
                    <p class="feature-text">
                        Make every invoice represent your brand. Add your logo, customize colors, and set up invoice profiles for different business needs.
                    </p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check text-success"></i> Upload company logo</li>
                        <li><i class="fas fa-check text-success"></i> Customize colors and styling</li>
                        <li><i class="fas fa-check text-success"></i> Multiple invoice profiles</li>
                        <li><i class="fas fa-check text-success"></i> Professional templates</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="app-preview-container feature-preview">
                    <div class="browser-window" data-app="invoicer-branding">
                        <div class="browser-header">
                            <div class="browser-controls">
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                            </div>
                            <div class="browser-url">drive.tridah.cloud/invoice-profiles</div>
                        </div>
                        <div class="browser-content invoice-preview">
                            <div class="branding-preview">
                                <div class="invoice-sample">
                                    <div class="invoice-sample-header">
                                        <div class="company-logo-placeholder">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <div class="company-info">
                                            <strong>Your Company Name</strong>
                                            <small>123 Business St, City, State 12345</small>
                                        </div>
                                    </div>
                                    <div class="invoice-sample-body">
                                        <div class="sample-item">Sample Invoice Content</div>
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

<!-- Benefits Section -->
<section class="section benefits-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4 mb-lg-0">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h4>Save Time</h4>
                    <p>Create professional invoices in seconds instead of hours. Automate repetitive tasks and focus on what matters.</p>
                </div>
            </div>
            <div class="col-lg-4 mb-4 mb-lg-0">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h4>Get Paid Faster</h4>
                    <p>Professional invoices with clear payment terms help you get paid on time. Track payments and send reminders automatically.</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4>Stay Organized</h4>
                    <p>Keep all your invoices, clients, and payments organized in one place. Never lose track of what's due or overdue.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section cta-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="cta-card">
                    <div class="cta-icon mb-4">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <h2 class="fw-bold mb-3">Ready to Transform Your Invoicing?</h2>
                    <p class="cta-text mb-4">
                        Join thousands of businesses using TridahDrive Invoicer to create professional invoices and get paid faster.
                    </p>
                    <div class="d-flex flex-wrap gap-3 justify-content-center">
                        <a href="{{ route('register') }}" class="btn btn-lg btn-primary">
                            <i class="fas fa-rocket me-2"></i>
                            Start Free Today
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-lg btn-outline-teal">
                            <i class="fas fa-arrow-left me-2"></i>
                            Learn More
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
/* Base Browser Window Styles */
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

/* Preview Styles */
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

.preview-search {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(49, 216, 178, 0.1);
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.7);
    width: 200px;
}

.landing-hero {
    min-height: 80vh;
    display: flex;
    align-items: center;
    padding: 4rem 0;
}

.hero-preview {
    position: relative;
    perspective: 1500px;
}

.hero-browser {
    transform: translateZ(0) rotateX(2deg) rotateY(-2deg);
    box-shadow: 0 30px 100px rgba(49, 216, 178, 0.2);
}

.invoice-preview-large {
    min-height: 400px;
    padding: 20px;
}

.invoice-form-preview {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 8px;
    padding: 20px;
}

.form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(49, 216, 178, 0.2);
}

.form-row {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 15px;
    margin-bottom: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.form-group label {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.7);
}

.invoice-items {
    margin: 20px 0;
}

.item-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 10px;
    padding: 10px;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 6px;
    margin-bottom: 8px;
    font-size: 0.8rem;
}

.invoice-totals {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 2px solid rgba(49, 216, 178, 0.2);
}

.total-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 0.85rem;
}

.grand-total {
    font-size: 1.1rem;
    font-weight: bold;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid rgba(49, 216, 178, 0.2);
}

.features-section {
    background: linear-gradient(135deg, #1a1a24 0%, #141420 100%);
    padding: 5rem 0;
}

.feature-content {
    padding: 2rem 0;
}

.feature-icon-large {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #31d8b2 0%, #204e7e 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    margin-bottom: 2rem;
}

.feature-heading {
    font-size: 2rem;
    font-weight: bold;
    color: #31d8b2;
    margin-bottom: 1rem;
}

.feature-text {
    color: rgba(255, 255, 255, 0.8);
    font-size: 1.1rem;
    line-height: 1.8;
    margin-bottom: 1.5rem;
}

.feature-list {
    list-style: none;
    padding: 0;
}

.feature-list li {
    padding: 0.75rem 0;
    color: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.feature-preview {
    max-width: 100%;
}

.preview-search {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(49, 216, 178, 0.1);
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.7);
    width: 200px;
}

.branding-preview {
    padding: 20px;
}

.invoice-sample {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    padding: 20px;
    border: 1px solid rgba(49, 216, 178, 0.2);
}

.invoice-sample-header {
    display: flex;
    align-items: center;
    gap: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(49, 216, 178, 0.2);
    margin-bottom: 15px;
}

.company-logo-placeholder {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #31d8b2 0%, #204e7e 100%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.company-info {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.company-info strong {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1rem;
}

.company-info small {
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.75rem;
}

.sample-item {
    padding: 15px;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 6px;
    text-align: center;
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.85rem;
}

.benefits-section {
    padding: 5rem 0;
    background: #1e1e2e;
}

.benefit-card {
    text-align: center;
    padding: 2rem;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 12px;
    border: 1px solid rgba(49, 216, 178, 0.1);
    transition: all 0.3s;
    height: 100%;
}

.benefit-card:hover {
    transform: translateY(-10px);
    border-color: rgba(49, 216, 178, 0.3);
    box-shadow: 0 20px 60px rgba(49, 216, 178, 0.1);
}

.benefit-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #31d8b2 0%, #204e7e 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    margin: 0 auto 1.5rem;
}

.benefit-card h4 {
    color: #31d8b2;
    font-weight: bold;
    margin-bottom: 1rem;
}

.benefit-card p {
    color: rgba(255, 255, 255, 0.8);
    line-height: 1.8;
}

.cta-section {
    padding: 5rem 0;
    background: linear-gradient(135deg, #141420 0%, #1a1a24 100%);
}

[data-theme="light"] .feature-text,
[data-theme="light"] .feature-list li {
    color: rgba(30, 30, 40, 0.9);
}

[data-theme="light"] .benefit-card {
    background: rgba(32, 78, 126, 0.02);
    border-color: rgba(32, 78, 126, 0.1);
}

[data-theme="light"] .benefit-card p {
    color: rgba(30, 30, 40, 0.8);
}

[data-theme="light"] .invoice-form-preview,
[data-theme="light"] .invoice-sample {
    background: rgba(32, 78, 126, 0.02);
    border-color: rgba(32, 78, 126, 0.1);
}

[data-theme="light"] .form-group label {
    color: rgba(30, 30, 40, 0.7);
}

[data-theme="light"] .item-row {
    background: rgba(32, 78, 126, 0.02);
}

/* Light theme overrides for browser windows */
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

[data-theme="light"] .invoice-preview {
    background: #ffffff;
}

[data-theme="light"] .preview-row {
    background: rgba(32, 78, 126, 0.02);
    border-color: rgba(32, 78, 126, 0.1);
    color: rgba(30, 30, 40, 0.9);
}

[data-theme="light"] .preview-row:hover {
    background: rgba(32, 78, 126, 0.08);
    border-color: rgba(32, 78, 126, 0.3);
}

[data-theme="light"] .preview-cell {
    color: rgba(30, 30, 40, 0.9);
}

[data-theme="light"] .preview-dropdown,
[data-theme="light"] .preview-search {
    background: rgba(0, 0, 0, 0.05);
    border-color: rgba(32, 78, 126, 0.1);
    color: rgba(30, 30, 40, 0.7);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced 3D browser window interactions
    const browserWindows = document.querySelectorAll('.browser-window');
    
    browserWindows.forEach(window => {
        const container = window.closest('.app-preview-container, .hero-preview');
        
        if (container) {
            container.addEventListener('mousemove', function(e) {
                const rect = container.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                
                const rotateX = ((y - centerY) / centerY) * -5;
                const rotateY = ((x - centerX) / centerX) * 5;
                
                if (window.classList.contains('hero-browser')) {
                    window.style.transform = `translateZ(0) rotateX(${rotateX + 2}deg) rotateY(${rotateY - 2}deg)`;
                } else {
                    window.style.transform = `translateY(-15px) translateZ(20px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
                }
            });
            
            container.addEventListener('mouseleave', function() {
                if (window.classList.contains('hero-browser')) {
                    window.style.transform = 'translateZ(0) rotateX(2deg) rotateY(-2deg)';
                } else {
                    window.style.transform = 'translateZ(0) rotateX(2deg)';
                }
            });
        }
    });
});
</script>
@endpush
@endsection

