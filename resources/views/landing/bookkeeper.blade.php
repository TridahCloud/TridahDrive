@extends('layouts.homepage')

@section('title', 'BookKeeper - Complete Accounting Solution')

@section('content')
<!-- Hero Section -->
<section class="hero-section landing-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="hero-title">Complete Accounting Solution</h1>
                    <p class="hero-subtitle">
                        Track income, manage expenses, and generate tax reports all in one place. Perfect accounting software for small businesses and freelancers.
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
                    <div class="browser-window hero-browser" data-app="bookkeeper">
                        <div class="browser-header">
                            <div class="browser-controls">
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                            </div>
                            <div class="browser-url">drive.tridah.cloud/bookkeeper/dashboard</div>
                        </div>
                        <div class="browser-content bookkeeper-preview-large">
                            <div class="dashboard-preview">
                                <div class="preview-stats-large">
                                    <div class="preview-stat-card-large">
                                        <div class="stat-icon income"><i class="fas fa-arrow-down"></i></div>
                                        <div class="stat-info">
                                            <div class="stat-label">Total Income</div>
                                            <div class="stat-value text-success">$12,450</div>
                                        </div>
                                    </div>
                                    <div class="preview-stat-card-large">
                                        <div class="stat-icon expense"><i class="fas fa-arrow-up"></i></div>
                                        <div class="stat-info">
                                            <div class="stat-label">Total Expenses</div>
                                            <div class="stat-value text-danger">$4,280</div>
                                        </div>
                                    </div>
                                    <div class="preview-stat-card-large">
                                        <div class="stat-icon net"><i class="fas fa-chart-line"></i></div>
                                        <div class="stat-info">
                                            <div class="stat-label">Net Income</div>
                                            <div class="stat-value text-success">$8,170</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="recent-transactions-large">
                                    <h5 class="section-title-small">Recent Transactions</h5>
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
            <h2 class="section-title">Powerful Accounting Features</h2>
            <p class="section-subtitle">Everything you need to manage your finances</p>
        </div>
        
        <div class="row g-5 mb-5">
            <!-- Feature 1: Transaction Management -->
            <div class="col-lg-6">
                <div class="feature-content">
                    <div class="feature-icon-large">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <h3 class="feature-heading">Transaction Management</h3>
                    <p class="feature-text">
                        Record and track all your income and expenses with ease. Categorize transactions, attach receipts, and stay organized.
                    </p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check text-success"></i> Track income and expenses</li>
                        <li><i class="fas fa-check text-success"></i> Attach receipts and documents</li>
                        <li><i class="fas fa-check text-success"></i> Multiple accounts support</li>
                        <li><i class="fas fa-check text-success"></i> Status tracking (pending, cleared, reconciled)</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="app-preview-container feature-preview">
                    <div class="browser-window" data-app="bookkeeper-transactions">
                        <div class="browser-header">
                            <div class="browser-controls">
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                            </div>
                            <div class="browser-url">drive.tridah.cloud/bookkeeper/transactions</div>
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
                </div>
            </div>
        </div>
        
        <div class="row g-5 mb-5">
            <!-- Feature 2: Recurring Transactions -->
            <div class="col-lg-6 order-lg-2">
                <div class="feature-content">
                    <div class="feature-icon-large">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <h3 class="feature-heading">Recurring Transactions</h3>
                    <p class="feature-text">
                        Automate recurring income and expenses. Set up subscriptions, salaries, and regular payments with flexible scheduling.
                    </p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check text-success"></i> Daily, weekly, monthly, yearly schedules</li>
                        <li><i class="fas fa-check text-success"></i> Advanced recurrence patterns</li>
                        <li><i class="fas fa-check text-success"></i> Upcoming transaction reminders</li>
                        <li><i class="fas fa-check text-success"></i> Easy skip or edit before submission</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6 order-lg-1">
                <div class="app-preview-container feature-preview">
                    <div class="browser-window" data-app="bookkeeper-recurring">
                        <div class="browser-header">
                            <div class="browser-controls">
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                            </div>
                            <div class="browser-url">drive.tridah.cloud/bookkeeper/recurring</div>
                        </div>
                        <div class="browser-content bookkeeper-preview">
                            <div class="recurring-preview">
                                <div class="recurring-header">
                                    <h5>Upcoming Recurring Transactions</h5>
                                    <span class="badge bg-warning">2 Due Today</span>
                                </div>
                                <div class="recurring-list">
                                    <div class="recurring-item">
                                        <div class="recurring-info">
                                            <strong>Monthly Subscription</strong>
                                            <small>Due: Today</small>
                                        </div>
                                        <div class="recurring-amount text-danger">-$49.99</div>
                                    </div>
                                    <div class="recurring-item">
                                        <div class="recurring-info">
                                            <strong>Client Payment</strong>
                                            <small>Due: Tomorrow</small>
                                        </div>
                                        <div class="recurring-amount text-success">+$1,200</div>
                                    </div>
                                    <div class="recurring-item">
                                        <div class="recurring-info">
                                            <strong>Office Rent</strong>
                                            <small>Due: In 3 days</small>
                                        </div>
                                        <div class="recurring-amount text-danger">-$800</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-5">
            <!-- Feature 3: Tax Reports -->
            <div class="col-lg-6">
                <div class="feature-content">
                    <div class="feature-icon-large">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3 class="feature-heading">Tax Reports</h3>
                    <p class="feature-text">
                        Generate comprehensive tax reports that your CPA will love. Export data by category, date range, and account type.
                    </p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check text-success"></i> CPA-friendly report format</li>
                        <li><i class="fas fa-check text-success"></i> Income and expense by category</li>
                        <li><i class="fas fa-check text-success"></i> Date range filtering</li>
                        <li><i class="fas fa-check text-success"></i> Export for tax filing</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="app-preview-container feature-preview">
                    <div class="browser-window" data-app="bookkeeper-tax">
                        <div class="browser-header">
                            <div class="browser-controls">
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                            </div>
                            <div class="browser-url">drive.tridah.cloud/bookkeeper/tax-report</div>
                        </div>
                        <div class="browser-content bookkeeper-preview">
                            <div class="tax-report-preview">
                                <div class="report-header">
                                    <h5>Tax Report - 2024</h5>
                                    <button class="preview-btn btn-sm"><i class="fas fa-download"></i> Export</button>
                                </div>
                                <div class="report-summary">
                                    <div class="report-row">
                                        <span>Total Income</span>
                                        <strong class="text-success">$45,200</strong>
                                    </div>
                                    <div class="report-row">
                                        <span>Total Expenses</span>
                                        <strong class="text-danger">$18,450</strong>
                                    </div>
                                    <div class="report-row total">
                                        <span>Net Income</span>
                                        <strong class="text-success">$26,750</strong>
                                    </div>
                                </div>
                                <div class="report-categories">
                                    <h6>Income by Category</h6>
                                    <div class="category-row">
                                        <span>Services</span>
                                        <strong>$32,000</strong>
                                    </div>
                                    <div class="category-row">
                                        <span>Products</span>
                                        <strong>$13,200</strong>
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
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h4>Complete Overview</h4>
                    <p>Get a complete view of your finances with income, expenses, and net profit tracking all in one dashboard.</p>
                </div>
            </div>
            <div class="col-lg-4 mb-4 mb-lg-0">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <h4>Tax Ready</h4>
                    <p>Generate professional tax reports that make filing taxes easy. Your CPA will thank you.</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h4>Automated & Smart</h4>
                    <p>Set up recurring transactions and let the system remind you of upcoming payments. Save time and never miss a transaction.</p>
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
                        <i class="fas fa-book"></i>
                    </div>
                    <h2 class="fw-bold mb-3">Ready to Master Your Finances?</h2>
                    <p class="cta-text mb-4">
                        Start tracking your income and expenses today. Get organized, stay on top of your finances, and make tax season easy.
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

.bookkeeper-preview-large {
    min-height: 400px;
    padding: 20px;
}

.dashboard-preview {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 8px;
    padding: 20px;
}

.preview-stats-large {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.preview-stat-card-large {
    background: rgba(49, 216, 178, 0.1);
    border: 1px solid rgba(49, 216, 178, 0.2);
    border-radius: 8px;
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.stat-icon {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.stat-icon.income {
    background: rgba(40, 167, 69, 0.2);
    color: #28a745;
}

.stat-icon.expense {
    background: rgba(220, 53, 69, 0.2);
    color: #dc3545;
}

.stat-icon.net {
    background: rgba(49, 216, 178, 0.2);
    color: #31d8b2;
}

.stat-info {
    flex: 1;
}

.section-title-small {
    font-size: 0.9rem;
    font-weight: bold;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 15px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.recent-transactions-large {
    margin-top: 20px;
}

.recurring-preview {
    padding: 15px;
}

.recurring-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid rgba(49, 216, 178, 0.2);
}

.recurring-header h5 {
    margin: 0;
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.9);
}

.recurring-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.recurring-item {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(49, 216, 178, 0.1);
    border-radius: 6px;
    padding: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.recurring-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.recurring-info strong {
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.9);
}

.recurring-info small {
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.6);
}

.recurring-amount {
    font-weight: bold;
    font-size: 0.9rem;
}

.tax-report-preview {
    padding: 15px;
}

.report-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(49, 216, 178, 0.2);
}

.report-header h5 {
    margin: 0;
    font-size: 1rem;
    color: rgba(255, 255, 255, 0.9);
}

.report-summary {
    margin-bottom: 20px;
}

.report-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid rgba(49, 216, 178, 0.1);
    font-size: 0.85rem;
}

.report-row.total {
    border-top: 2px solid rgba(49, 216, 178, 0.2);
    border-bottom: none;
    margin-top: 10px;
    padding-top: 15px;
    font-size: 1rem;
    font-weight: bold;
}

.report-categories {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid rgba(49, 216, 178, 0.2);
}

.report-categories h6 {
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.category-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 0.8rem;
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

[data-theme="light"] .dashboard-preview,
[data-theme="light"] .recurring-preview,
[data-theme="light"] .tax-report-preview {
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

[data-theme="light"] .bookkeeper-preview {
    background: #ffffff;
}

[data-theme="light"] .preview-transaction {
    background: rgba(32, 78, 126, 0.02);
    border-color: rgba(32, 78, 126, 0.1);
}

[data-theme="light"] .preview-transaction:hover {
    background: rgba(32, 78, 126, 0.08);
    border-color: rgba(32, 78, 126, 0.3);
}

[data-theme="light"] .transaction-details strong {
    color: rgba(30, 30, 40, 0.9);
}

[data-theme="light"] .transaction-details small {
    color: rgba(30, 30, 40, 0.6);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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

