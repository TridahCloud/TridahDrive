@extends('layouts.dashboard')

@section('title', 'Edit Invoice ' . $invoice->invoice_number . ' - ' . $drive->name)

@php
    // Get customizations, ensuring it's an array
    $customizations = is_array($invoice->customizations) ? $invoice->customizations : [];
    
    // Helper function to check if a customization option is enabled
    // Handles boolean true/false, string "true"/"false", and defaults to true if not set
    $isEnabled = function($key) use ($customizations) {
        if (!isset($customizations[$key])) {
            return true; // Default to enabled if not set
        }
        $value = $customizations[$key];
        // Handle boolean, string boolean, and numeric values
        if (is_bool($value)) {
            return $value;
        }
        if (is_string($value)) {
            return strtolower($value) === 'true' || $value === '1';
        }
        return (bool) $value;
    };
@endphp

@push('styles')
<style>
    :root {
        --invoice-accent-color: {{ $accentColor }};
    }
    
    @media print {
        /* Hide navigation and toolbar when printing */
        .breadcrumb,
        .dashboard-card,
        .btn,
        .alert,
        #customizePanel,
        #customizePanelOverlay,
        .customize-panel,
        .quick-add-item,
        #addItemBtn,
        #importClientBtn,
        .remove-item,
        .dropdown-menu {
            display: none !important;
        }
        
        /* Hide form inputs and show values instead */
        input[type="text"],
        input[type="email"],
        input[type="number"],
        input[type="date"],
        select,
        textarea {
            border: none !important;
            background: transparent !important;
            padding: 0 !important;
            margin: 0 !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            box-shadow: none !important;
            outline: none !important;
            color: #000000 !important;
            font-size: inherit !important;
            font-family: inherit !important;
            font-weight: inherit !important;
            width: auto !important;
            min-width: 0 !important;
        }
        
        /* Remove contenteditable styling when printing */
        [contenteditable="true"] {
            border: none !important;
            background: transparent !important;
            outline: none !important;
        }
        
        /* Remove padding and margins for print */
        body {
            padding: 0 !important;
            margin: 0 !important;
        }
        
        .container-fluid {
            padding: 0 !important;
        }
        
        /* Ensure invoice container is full width and has white background */
        .invoice-container {
            max-width: 100% !important;
            margin: 0 !important;
            padding: 20px !important;
            background-color: #ffffff !important;
            box-shadow: none !important;
        }
        
        /* Make all text dark for good contrast on white paper */
        .text-muted,
        .text-muted *,
        small.text-muted,
        .text-muted small {
            color: #333333 !important;
        }
        
        /* Make text-muted elements dark grey instead of light grey */
        .invoice-container .text-muted {
            color: #333333 !important;
        }
        
        /* Ensure regular text elements are dark */
        .invoice-container p:not([style*="color"]),
        .invoice-container div:not([style*="color"]):not([data-field="totals"]),
        .invoice-container span:not([style*="color"]),
        .invoice-container td,
        .invoice-container strong:not([style*="color"]),
        .invoice-container h1:not([style*="color"]),
        .invoice-container h2:not([style*="color"]),
        .invoice-container h3:not([style*="color"]),
        .invoice-container h4:not([style*="color"]),
        .invoice-container h5:not([style*="color"]),
        .invoice-container h6:not([style*="color"]) {
            color: #000000 !important;
        }
        
        /* Preserve accent color for customized elements - use very specific selectors */
        .invoice-container h2[data-field="company-name"][style*="color"],
        .invoice-container h1[data-field="invoice-title"][style*="color"],
        .invoice-container [data-field="totals"] .mb-3 strong[style*="color"],
        .invoice-container [data-field="totals"] .h3 strong span[style*="color"],
        .invoice-container [data-field="totals"] strong[style*="color"],
        .invoice-container [data-field="totals"] span[style*="color"],
        .invoice-container .invoice-heading,
        .invoice-container .brand-teal,
        .invoice-container #subtotal,
        .invoice-container #taxAmount,
        .invoice-container #total {
            color: var(--invoice-accent-color) !important;
        }
        
        /* Ensure table header uses accent color */
        .invoice-container thead,
        .invoice-container thead th {
            background-color: var(--invoice-accent-color) !important;
            color: #ffffff !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        /* Ensure borders print */
        .table-bordered,
        .table-bordered th,
        .table-bordered td,
        .border-top {
            border-color: #000000 !important;
        }
        
        /* Remove input borders when printing */
        .form-control,
        .invoice-field,
        .invoice-field-small {
            border: none !important;
            border-bottom: 1px solid transparent !important;
        }
        
        /* Ensure background colors print */
        .bg-light {
            background-color: #f5f5f5 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        /* Badge colors for status */
        .badge {
            border: 1px solid #000000 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        /* Links should be dark, not blue */
        a {
            color: #000000 !important;
            text-decoration: underline !important;
        }
        
        /* Remove any background images or gradients */
        * {
            background-image: none !important;
        }
        
        /* Page break settings */
        .invoice-container {
            page-break-inside: avoid;
        }
        
        .table {
            page-break-inside: avoid;
        }
        
        /* Hide input group styling */
        .input-group {
            display: block !important;
        }
        
        .input-group .form-control {
            display: block !important;
            width: 100% !important;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Toolbar -->
    <div class="dashboard-card mb-3 p-3 d-flex justify-content-between align-items-center border-0">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('drives.index') }}">Drives</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('drives.show', $drive) }}">{{ $drive->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('drives.invoices.index', $drive) }}">Invoices</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('drives.invoices.show', [$drive, $invoice]) }}">{{ $invoice->invoice_number }}</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
            <h6 class="mb-0 text-muted">Editing Invoice {{ $invoice->invoice_number }}</h6>
        </div>
        <div>
            <a href="{{ route('drives.invoices.show', [$drive, $invoice]) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="customizeBtn">
                <i class="fas fa-cog me-1"></i>Customize
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" id="saveBtn">
                <i class="fas fa-save me-1"></i>Update
            </button>
        </div>
    </div>

    <!-- Customize Panel -->
    <div id="customizePanel" class="customize-panel">
        <div class="customize-panel-header">
            <h5 class="mb-0">
                <i class="fas fa-sliders-h me-2"></i>Customize Invoice
            </h5>
            <button type="button" class="btn btn-link text-reset p-0" id="closeCustomizeBtn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="customize-panel-body">
            <div class="customize-section">
                <h6 class="section-title">Company Information</h6>
                            <div class="customize-item">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="show-company-name" {{ old('customizations.show_company_name', $invoice->customizations['show_company_name'] ?? true) ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Company Name</span>
                            </div>
                            <div class="customize-item">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="show-company-address" {{ old('customizations.show_company_address', $invoice->customizations['show_company_address'] ?? true) ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Company Address</span>
                            </div>
                            <div class="customize-item">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="show-company-phone" {{ old('customizations.show_company_phone', $invoice->customizations['show_company_phone'] ?? true) ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Company Phone</span>
                            </div>
                            <div class="customize-item">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="show-company-email" {{ old('customizations.show_company_email', $invoice->customizations['show_company_email'] ?? true) ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Company Email</span>
                            </div>
                            <div class="customize-item">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="show-company-logo" {{ old('customizations.show_company_logo', $invoice->customizations['show_company_logo'] ?? true) ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Company Logo</span>
                            </div>
            </div>

            <div class="customize-section">
                <h6 class="section-title">Invoice Details</h6>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-invoice-number" {{ old('customizations.show_invoice_number', $invoice->customizations['show_invoice_number'] ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Invoice Number</span>
                </div>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-invoice-date" {{ old('customizations.show_invoice_date', $invoice->customizations['show_invoice_date'] ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Invoice Date</span>
                </div>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-invoice-due-date" {{ old('customizations.show_invoice_due_date', $invoice->customizations['show_invoice_due_date'] ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Due Date</span>
                </div>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-invoice-status" {{ old('customizations.show_invoice_status', $invoice->customizations['show_invoice_status'] ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Invoice Status</span>
                </div>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-invoice-title" {{ old('customizations.show_invoice_title', $invoice->customizations['show_invoice_title'] ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Invoice Title</span>
                </div>
            </div>

            <div class="customize-section">
                <h6 class="section-title">Customization</h6>
                <div class="customize-item">
                    <label for="invoice-color" class="form-label">Invoice Accent Color:</label>
                    <input type="color" class="form-control form-control-color" id="invoice-color" value="{{ old('customizations.accent_color', $invoice->customizations['accent_color'] ?? $invoiceProfile?->accent_color ?? '#31d8b2') }}" title="Choose color">
                </div>
            </div>

            <div class="customize-section">
                <h6 class="section-title">Client Information</h6>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-client-name" {{ old('customizations.show_client_name', $invoice->customizations['show_client_name'] ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Client Name</span>
                </div>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-client-address" {{ old('customizations.show_client_address', $invoice->customizations['show_client_address'] ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Client Address</span>
                </div>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-client-email" {{ old('customizations.show_client_email', $invoice->customizations['show_client_email'] ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Client Email</span>
                </div>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-project" {{ old('customizations.show_project', $invoice->customizations['show_project'] ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Project Name</span>
                </div>
            </div>

            <div class="customize-section">
                <h6 class="section-title">Other Sections</h6>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-items-table" {{ old('customizations.show_items_table', $invoice->customizations['show_items_table'] ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Items Table</span>
                </div>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-payment-details" {{ old('customizations.show_payment_details', $invoice->customizations['show_payment_details'] ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Payment Details</span>
                </div>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-totals" {{ old('customizations.show_totals', $invoice->customizations['show_totals'] ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Totals</span>
                </div>
            </div>
        </div>
    </div>
    <div id="customizePanelOverlay" class="customize-panel-overlay"></div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('drives.invoices.update', [$drive, $invoice]) }}" method="POST" id="invoiceForm">
        @csrf
        @method('PATCH')
        
        <!-- Invoice Container - Looks like actual invoice -->
        <div class="invoice-container rounded shadow-sm p-5" style="max-width: 900px; margin: 0 auto; background-color: var(--bg-secondary, #f8f9fa);">
            <!-- Top Section: Logo & Invoice Info -->
            <div class="invoice-section d-flex justify-content-between align-items-start mb-5" id="company-info" data-section="company-info">
                <div class="flex-grow-1">
                    @php
                        $logoUrl = null;
                        if ($invoiceProfile?->logo_path) {
                            $logoUrl = Storage::url($invoiceProfile->logo_path);
                        } elseif ($invoiceProfile?->logo_url) {
                            $logoUrl = $invoiceProfile->logo_url;
                        }
                    @endphp
                    @if($logoUrl)
                        <div class="mb-3" data-field="company-logo" style="display: {{ $isEnabled('show_company_logo') ? '' : 'none' }};">
                            <img src="{{ $logoUrl }}" alt="Company Logo" style="max-height: 80px; max-width: 200px; object-fit: contain;">
                        </div>
                    @endif
                    <h2 class="mb-2" data-field="company-name" style="color: {{ $accentColor }} !important;">
                        <span contenteditable="true" class="brand-teal" style="color: {{ $accentColor }} !important;">{{ $invoiceProfile?->company_name ?? 'Your Company' }}</span>
                    </h2>
                    <p class="text-muted mb-1" data-field="company-address">
                        <span contenteditable="true">{{ $invoiceProfile?->company_address ?? 'Your Address' }}</span>
                    </p>
                    <p class="text-muted mb-1" data-field="company-phone">
                        <span contenteditable="true">{{ $invoiceProfile?->company_phone ?? 'Your Phone' }}</span>
                    </p>
                    <p class="text-muted mb-0" data-field="company-email">
                        <span contenteditable="true">{{ $invoiceProfile?->company_email ?? 'your@email.com' }}</span>
                    </p>
                </div>
                <div class="text-end invoice-section" id="invoice-header" data-section="invoice-details" style="text-align: right;">
                    <h1 class="text-primary mb-3 invoice-heading" data-field="invoice-title" style="color: {{ $accentColor }} !important; text-align: right;">INVOICE</h1>
                    <div class="text-muted small" style="text-align: right;">
                        <div class="mb-1" data-field="invoice-number">
                            Invoice # <span class="invoice-number text-dark">{{ $invoice->invoice_number }}</span>
                        </div>
                        <div class="mb-1" data-field="invoice-date" style="text-align: right;">
                            Date: <input type="date" class="invoice-field" id="issue_date" name="issue_date" value="{{ old('issue_date', $invoice->issue_date->format('Y-m-d')) }}" required style="text-align: right;">
                        </div>
                        <div class="mb-1" data-field="invoice-due-date" style="text-align: right;">
                            Due: <input type="date" class="invoice-field" id="due_date" name="due_date" value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}" style="text-align: right;">
                        </div>
                        <div data-field="invoice-status" style="text-align: right;">
                            Status: 
                            <select class="invoice-field" name="status" id="status" required style="text-align: right;">
                                <option value="draft" {{ old('status', $invoice->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="sent" {{ old('status', $invoice->status) === 'sent' ? 'selected' : '' }}>Sent</option>
                                <option value="paid" {{ old('status', $invoice->status) === 'paid' ? 'selected' : '' }}>Paid</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bill To Section -->
            <div class="invoice-section border-top pt-4 pb-4 mb-4" id="bill-to" data-section="bill-to">
                <div class="d-flex justify-content-between">
                        <div class="flex-grow-1">
                            <h6 class="text-uppercase text-muted mb-3">Bill To:</h6>
                            <div class="input-group mb-2" data-field="client-name">
                                <input type="text" class="form-control border-0 border-bottom" name="client_name" id="client_name" value="{{ old('client_name', $invoice->client_name) }}" placeholder="Client Name" required>
                            </div>
                            <div class="input-group mb-2" data-field="client-address">
                                <input type="text" class="form-control border-0 border-bottom" name="client_address" id="client_address" value="{{ old('client_address', $invoice->client_address) }}" placeholder="Client Address">
                            </div>
                            <div class="input-group mb-2" data-field="client-email">
                                <input type="email" class="form-control border-0 border-bottom" name="client_email" id="client_email" value="{{ old('client_email', $invoice->client_email) }}" placeholder="Client Email">
                            </div>
                            <div class="input-group mb-2" data-field="project">
                                <input type="text" class="form-control border-0 border-bottom" name="project" id="project" value="{{ old('project', $invoice->project) }}" placeholder="Project Name">
                            </div>
                        </div>
                    <div>
                        <button type="button" class="btn btn-primary btn-sm" id="importClientBtn" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>Import Client
                        </button>
                        <ul class="dropdown-menu" id="clientDropdown">
                            @foreach($clients as $client)
                                <li>
                                    <a class="dropdown-item import-client" href="#" 
                                       data-name="{{ $client->name }}"
                                       data-email="{{ $client->email ?? '' }}"
                                       data-address="{{ $client->full_address ?? '' }}">
                                        {{ $client->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="invoice-section mb-4" id="items-table" data-section="items-table" style="display: {{ $isEnabled('show_items_table') ? '' : 'none' }};">
                <!-- Quick Add Items -->
                @if($userItems->count() > 0)
                <div class="mb-3">
                    <label class="form-label"><small class="text-muted">Quick Add Items:</small></label>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($userItems as $item)
                            <button type="button" class="btn btn-sm btn-outline-secondary quick-add-item" 
                                data-name="{{ $item->name }}"
                                data-description="{{ $item->description ?? '' }}"
                                data-unit="{{ $item->unit ?? 'items' }}"
                                data-price="{{ $item->default_price ?? 0 }}">
                                <i class="fas fa-plus me-1"></i>{{ $item->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <table class="table table-bordered" id="itemsTable">
                    <thead class="bg-primary text-white" style="background-color: {{ $accentColor }} !important;">
                        <tr>
                            <th style="width: 40%; background-color: {{ $accentColor }} !important;">DESCRIPTION</th>
                            <th style="width: 12%; background-color: {{ $accentColor }} !important;">QTY</th>
                            <th style="width: 18%; background-color: {{ $accentColor }} !important;">UNIT</th>
                            <th style="width: 15%; background-color: {{ $accentColor }} !important;">RATE</th>
                            <th style="width: 15%; background-color: {{ $accentColor }} !important;">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        @foreach($invoice->items as $index => $item)
                        <tr class="item-row">
                            <td>
                                <input type="text" class="form-control border-0 item-description" name="items[{{ $index }}][description]" value="{{ $item->description }}" required>
                            </td>
                            <td>
                                <input type="number" class="form-control border-0 item-qty" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}" step="1" min="0" required>
                            </td>
                            <td>
                                <input type="text" class="form-control border-0" name="items[{{ $index }}][unit]" value="{{ $item->unit }}">
                            </td>
                            <td>
                                <input type="number" class="form-control border-0 item-price" name="items[{{ $index }}][unit_price]" value="{{ $item->unit_price }}" step="1" min="0" required>
                            </td>
                            <td>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="item-total">{{ currency_for($item->quantity * $item->unit_price, $drive) }}</span>
                                    <button type="button" class="btn btn-link btn-sm text-danger remove-item">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="button" class="btn btn-outline-primary btn-sm" id="addItemBtn">
                    <i class="fas fa-plus me-1"></i>Add Line Item
                </button>
            </div>

            <!-- Totals -->
            <div class="row" style="display: {{ ($isEnabled('show_payment_details') || $isEnabled('show_totals')) ? '' : 'none' }};">
                <div class="col-md-6 invoice-section" id="payment-details" data-section="payment-details" style="display: {{ $isEnabled('show_payment_details') ? '' : 'none' }};">
                    <h6 class="text-uppercase text-muted mb-3">Payment Details:</h6>
                    <div class="bg-light p-3 rounded">
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-2">
                                    <small class="text-muted">Bank:</small>
                                    <span contenteditable="true" class="d-block">{{ $invoiceProfile?->bank_name ?? 'Your Bank' }}</span>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Account Name:</small>
                                    <span contenteditable="true" class="d-block">{{ $invoiceProfile?->bank_account_name ?? 'Your Account' }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-2">
                                    <small class="text-muted">Routing Number:</small>
                                    <span contenteditable="true" class="d-block">{{ $invoiceProfile?->bank_routing_number ?? '123456' }}</span>
                                </div>
                                <div>
                                    <small class="text-muted">Account Number:</small>
                                    <span contenteditable="true" class="d-block">{{ $invoiceProfile?->bank_account_number ?? '123456789' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 invoice-section" id="totals" data-section="totals" style="display: {{ $isEnabled('show_totals') ? '' : 'none' }};">
                    <div class="text-end">
                        <div class="mb-3">
                            <strong style="color: {{ $accentColor }} !important;">Subtotal: <span id="subtotal" style="color: {{ $accentColor }} !important;">{{ currency_for($invoice->subtotal, $drive) }}</span></strong>
                        </div>
                        <div class="mb-3 d-flex align-items-center gap-2">
                            <label class="mb-0">Tax:</label>
                            <input type="number" class="form-control invoice-field-small" id="tax_rate" name="tax_rate" value="{{ old('tax_rate', $invoice->tax_rate ?? 0) }}" step="0.01" min="0" max="100" style="width: 80px;">
                            <span>% (<span id="taxAmount" style="color: {{ $accentColor }} !important;">{{ currency_for($invoice->tax_amount ?? 0, $drive) }}</span>)</span>
                        </div>
                        <div class="h3 mb-0">
                            <strong>Total: <span class="brand-teal"><span id="total" style="color: {{ $accentColor }} !important;">{{ currency_for($invoice->total, $drive) }}</span></span></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.invoice-container [contenteditable="true"] {
    border-bottom: 1px dashed rgba(0, 0, 0, 0.2);
    min-height: 1.5em;
    outline: none;
    display: inline-block;
    padding: 0 2px;
}

.invoice-container [contenteditable="true"]:focus {
    border-bottom: 1px dashed #31d8b2;
    background-color: rgba(49, 216, 178, 0.05);
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemCounter = {{ count($invoice->items) }};
    
    // Get initial accent color from profile
    const initialAccentColor = '{{ $accentColor }}';
    
    // Get currency symbol for this drive
    const currencySymbol = '{{ currency_symbol(currency_code_for($drive)) }}';
    const currencyPosition = '{{ \App\Helpers\CurrencyHelper::getCurrency(currency_code_for($drive))['position'] }}';
    
    // Initialize accent color on page load
    function initializeAccentColor(color) {
        document.querySelectorAll('.invoice-heading').forEach(element => {
            element.style.color = color;
        });
        
        document.querySelectorAll('.brand-teal').forEach(element => {
            element.style.color = color;
        });
        
        const subtotalEl = document.getElementById('subtotal');
        const taxAmountEl = document.getElementById('taxAmount');
        const totalEl = document.getElementById('total');
        
        if (subtotalEl) subtotalEl.style.color = color;
        if (taxAmountEl) taxAmountEl.style.color = color;
        if (totalEl) totalEl.style.color = color;
        
        document.querySelectorAll('.table thead, .table thead th').forEach(element => {
            element.style.backgroundColor = color;
        });
        
        // Store in CSS variable
        document.documentElement.style.setProperty('--invoice-accent-color', color);
    }
    
    // Initialize with profile's accent color
    initializeAccentColor(initialAccentColor);
    
    // Get saved customizations or use defaults
    const savedCustomizations = @json($invoice->customizations ?? []);
    const savedAccentColor = savedCustomizations?.accent_color || initialAccentColor;
    
    // Initialize with saved accent color if available
    if (savedAccentColor && savedAccentColor !== initialAccentColor) {
        initializeAccentColor(savedAccentColor);
        const invoiceColorInput = document.getElementById('invoice-color');
        if (invoiceColorInput) {
            invoiceColorInput.value = savedAccentColor;
        }
    }
    
    // Apply saved customizations on page load
    const fieldMappings = {
        'show-company-name': 'company-name',
        'show-company-address': 'company-address',
        'show-company-phone': 'company-phone',
        'show-company-email': 'company-email',
        'show-company-logo': 'company-logo',
        'show-invoice-title': 'invoice-title',
        'show-invoice-number': 'invoice-number',
        'show-invoice-date': 'invoice-date',
        'show-invoice-due-date': 'invoice-due-date',
        'show-invoice-status': 'invoice-status',
        'show-client-name': 'client-name',
        'show-client-address': 'client-address',
        'show-client-email': 'client-email',
        'show-project': 'project',
        'show-items-table': 'items-table',
        'show-payment-details': 'payment-details',
        'show-totals': 'totals',
    };
    
    // Section mappings for larger sections that use data-section attribute
    const sectionMappings = {
        'show-items-table': 'items-table',
        'show-payment-details': 'payment-details',
        'show-totals': 'totals',
    };
    
    // Apply saved customizations
    Object.keys(fieldMappings).forEach(checkboxId => {
        const checkbox = document.getElementById(checkboxId);
        const fieldName = fieldMappings[checkboxId];
        const sectionName = sectionMappings[checkboxId];
        const customizationKey = checkboxId.replace('show-', 'show_').replace(/-/g, '_');
        const isChecked = savedCustomizations?.[customizationKey] ?? true;
        
        if (checkbox) {
            // Handle regular fields with data-field attribute
            if (fieldName && !sectionName) {
                const targetElements = document.querySelectorAll(`[data-field="${fieldName}"]`);
                targetElements.forEach(element => {
                    if (isChecked) {
                        element.style.display = '';
                    } else {
                        element.style.display = 'none';
                    }
                });
            }
            
            // Handle sections with data-section attribute
            if (sectionName) {
                // Try to find the section - use ID first (most reliable), then data-section
                const targetSection = document.getElementById(sectionName)
                    || document.querySelector(`[data-section="${sectionName}"]`)
                    || document.querySelector(`.invoice-container [data-section="${sectionName}"]`);
                if (targetSection) {
                    if (isChecked) {
                        targetSection.style.display = '';
                        // For totals row, also show the parent row if either payment-details or totals is visible
                        if (sectionName === 'payment-details' || sectionName === 'totals') {
                            const totalsRow = targetSection.closest('.row');
                            if (totalsRow) {
                                const paymentDetailsCheckbox = document.getElementById('show-payment-details');
                                const totalsCheckbox = document.getElementById('show-totals');
                                const paymentDetailsKey = 'show_payment_details';
                                const totalsKey = 'show_totals';
                                const paymentDetailsVisible = paymentDetailsCheckbox ? (savedCustomizations?.[paymentDetailsKey] ?? true) : true;
                                const totalsVisible = totalsCheckbox ? (savedCustomizations?.[totalsKey] ?? true) : true;
                                if (paymentDetailsVisible || totalsVisible) {
                                    totalsRow.style.display = '';
                                }
                            }
                        }
                    } else {
                        targetSection.style.display = 'none';
                        // For totals row, hide the parent row if both payment-details and totals are hidden
                        if (sectionName === 'payment-details' || sectionName === 'totals') {
                            const totalsRow = targetSection.closest('.row');
                            if (totalsRow) {
                                const paymentDetailsCheckbox = document.getElementById('show-payment-details');
                                const totalsCheckbox = document.getElementById('show-totals');
                                const paymentDetailsKey = 'show_payment_details';
                                const totalsKey = 'show_totals';
                                const paymentDetailsVisible = paymentDetailsCheckbox ? (savedCustomizations?.[paymentDetailsKey] ?? true) : true;
                                const totalsVisible = totalsCheckbox ? (savedCustomizations?.[totalsKey] ?? true) : true;
                                if (!paymentDetailsVisible && !totalsVisible) {
                                    totalsRow.style.display = 'none';
                                } else {
                                    // Make sure row is visible if at least one section is visible
                                    totalsRow.style.display = '';
                                }
                            }
                        }
                    }
                }
            }
        }
    });
    
    // Set default dates
    const today = new Date().toISOString().split('T')[0];
    const dueDate = new Date();
    dueDate.setDate(dueDate.getDate() + 30);
    const dueDateStr = dueDate.toISOString().split('T')[0];
    
    // Don't overwrite the existing date in edit mode
    // document.getElementById('issue_date').value = today;
    
    // Add item button
    const addItemBtn = document.getElementById('addItemBtn');
    if (addItemBtn) {
        addItemBtn.addEventListener('click', function() {
            const tbody = document.getElementById('itemsBody');
            if (tbody) {
                const newRow = document.createElement('tr');
                newRow.className = 'item-row';
                newRow.innerHTML = `
                    <td>
                        <input type="text" class="form-control border-0 item-description" name="items[${itemCounter}][description]" placeholder="Enter description" required>
                    </td>
                    <td>
                        <input type="number" class="form-control border-0 item-qty" name="items[${itemCounter}][quantity]" value="1" step="1" min="0" required>
                    </td>
                    <td>
                        <input type="text" class="form-control border-0" name="items[${itemCounter}][unit]" value="items">
                    </td>
                    <td>
                        <input type="number" class="form-control border-0 item-price" name="items[${itemCounter}][unit_price]" value="0" step="1" min="0" required>
                    </td>
                    <td>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="item-total">{{ currency_for(0, $drive) }}</span>
                            <button type="button" class="btn btn-link btn-sm text-danger remove-item">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(newRow);
                attachItemListeners(newRow);
                itemCounter++;
            }
        });
    }
    
    // Remove item
    const itemsTable = document.getElementById('itemsTable');
    if (itemsTable) {
        itemsTable.addEventListener('click', function(e) {
            if (e.target.closest('.remove-item')) {
                const row = e.target.closest('.item-row');
                const tbody = document.getElementById('itemsBody');
                if (tbody && tbody.children.length > 1) {
                    row.remove();
                    calculateTotals();
                }
            }
        });
    }
    
    // Quick Add Items
    document.querySelectorAll('.quick-add-item').forEach(button => {
        button.addEventListener('click', function() {
            const name = this.dataset.name || '';
            const description = this.dataset.description || '';
            const unit = this.dataset.unit || 'items';
            const price = parseFloat(this.dataset.price) || 0;
            
            console.log('Adding item:', { name, unit, price });
            
            // Always create a new row instead of editing the first one
            const tbody = document.getElementById('itemsBody');
            const newRow = document.createElement('tr');
            newRow.className = 'item-row';
            newRow.innerHTML = `
                <td>
                    <input type="text" class="form-control border-0 item-description" name="items[${itemCounter}][description]" placeholder="Enter description" value="${name}" required>
                </td>
                <td>
                    <input type="number" class="form-control border-0 item-qty" name="items[${itemCounter}][quantity]" value="1" step="1" min="0" required>
                </td>
                <td>
                    <input type="text" class="form-control border-0" name="items[${itemCounter}][unit]" value="${unit}">
                </td>
                <td>
                    <input type="number" class="form-control border-0 item-price" name="items[${itemCounter}][unit_price]" value="${price.toFixed(2)}" step="1" min="0" required>
                </td>
                <td>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="item-total">${formatCurrency(price)}</span>
                        <button type="button" class="btn btn-link btn-sm text-danger remove-item">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(newRow);
            attachItemListeners(newRow);
            itemCounter++;
            calculateTotals();
        });
    });
    
    // Client dropdown
    document.querySelectorAll('.import-client').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const clientName = this.dataset.name || '';
            const clientEmail = this.dataset.email || '';
            const clientAddress = this.dataset.address || '';
            
            console.log('Importing client:', { clientName, clientEmail, clientAddress });
            
            if (clientName) {
                const nameField = document.getElementById('client_name');
                if (nameField) {
                    nameField.value = clientName;
                    console.log('Set client name to:', clientName);
                }
            }
            
            if (clientEmail) {
                const emailField = document.getElementById('client_email');
                if (emailField) {
                    emailField.value = clientEmail;
                    console.log('Set client email to:', clientEmail);
                }
            }
            
            if (clientAddress) {
                const addressField = document.getElementById('client_address');
                if (addressField) {
                    addressField.value = clientAddress;
                    console.log('Set client address to:', clientAddress);
                }
            }
            
            // Close dropdown
            try {
                const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('importClientBtn'));
                if (dropdown) {
                    dropdown.hide();
                }
            } catch (err) {
                console.error('Error closing dropdown:', err);
            }
        });
    });
    
    // Format currency amount
    function formatCurrency(amount) {
        const formatted = parseFloat(amount).toFixed(2);
        if (currencyPosition === 'before') {
            return currencySymbol + formatted;
        } else {
            return formatted + ' ' + currencySymbol;
        }
    }
    
    // Calculate totals
    function calculateTotals() {
        let subtotal = 0;
        
        document.querySelectorAll('.item-row').forEach(row => {
            const qtyInput = row.querySelector('.item-qty');
            const priceInput = row.querySelector('.item-price');
            const totalSpan = row.querySelector('.item-total');
            
            if (qtyInput && priceInput && totalSpan) {
                const qty = parseFloat(qtyInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                const total = qty * price;
                
                totalSpan.textContent = formatCurrency(total);
                subtotal += total;
            }
        });
        
        const taxRateInput = document.getElementById('tax_rate');
        const taxRate = taxRateInput ? (parseFloat(taxRateInput.value) || 0) : 0;
        const taxAmount = subtotal * (taxRate / 100);
        const grandTotal = subtotal + taxAmount;
        
        const subtotalEl = document.getElementById('subtotal');
        const taxAmountEl = document.getElementById('taxAmount');
        const totalEl = document.getElementById('total');
        
        if (subtotalEl) subtotalEl.textContent = formatCurrency(subtotal);
        if (taxAmountEl) taxAmountEl.textContent = formatCurrency(taxAmount);
        if (totalEl) totalEl.textContent = formatCurrency(grandTotal);
    }
    
    // Attach listeners to item row
    function attachItemListeners(row) {
        const qtyInput = row.querySelector('.item-qty');
        const priceInput = row.querySelector('.item-price');
        if (qtyInput) qtyInput.addEventListener('input', calculateTotals);
        if (priceInput) priceInput.addEventListener('input', calculateTotals);
    }
    
    // Attach to existing items
    document.querySelectorAll('.item-row').forEach(row => {
        attachItemListeners(row);
    });
    
    // Tax rate listener
    const taxRateInput = document.getElementById('tax_rate');
    if (taxRateInput) {
        taxRateInput.addEventListener('input', calculateTotals);
    }
    
    // Save button
    const saveBtn = document.getElementById('saveBtn');
    const invoiceForm = document.getElementById('invoiceForm');
    
    if (saveBtn && invoiceForm) {
        saveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Re-index items array before submission
            const itemRows = document.querySelectorAll('.item-row');
            itemRows.forEach((row, index) => {
                const inputs = row.querySelectorAll('input[name^="items["]');
                inputs.forEach(input => {
                    const name = input.name;
                    const newName = name.replace(/items\[\d+\]/, `items[${index}]`);
                    input.name = newName;
                });
            });
            
            // Collect all customization states before submitting
            const customizations = {
                show_company_name: document.getElementById('show-company-name')?.checked ?? true,
                show_company_address: document.getElementById('show-company-address')?.checked ?? true,
                show_company_phone: document.getElementById('show-company-phone')?.checked ?? true,
                show_company_email: document.getElementById('show-company-email')?.checked ?? true,
                show_company_logo: document.getElementById('show-company-logo')?.checked ?? true,
                show_invoice_title: document.getElementById('show-invoice-title')?.checked ?? true,
                show_invoice_number: document.getElementById('show-invoice-number')?.checked ?? true,
                show_invoice_date: document.getElementById('show-invoice-date')?.checked ?? true,
                show_invoice_due_date: document.getElementById('show-invoice-due-date')?.checked ?? true,
                show_invoice_status: document.getElementById('show-invoice-status')?.checked ?? true,
                show_client_name: document.getElementById('show-client-name')?.checked ?? true,
                show_client_address: document.getElementById('show-client-address')?.checked ?? true,
                show_client_email: document.getElementById('show-client-email')?.checked ?? true,
                show_project: document.getElementById('show-project')?.checked ?? true,
                show_items_table: document.getElementById('show-items-table')?.checked ?? true,
                show_payment_details: document.getElementById('show-payment-details')?.checked ?? true,
                show_totals: document.getElementById('show-totals')?.checked ?? true,
                accent_color: document.getElementById('invoice-color')?.value ?? initialAccentColor,
            };
            
            // Add hidden input for customizations
            let customizationsInput = document.getElementById('customizations-input');
            if (!customizationsInput) {
                customizationsInput = document.createElement('input');
                customizationsInput.type = 'hidden';
                customizationsInput.id = 'customizations-input';
                customizationsInput.name = 'customizations';
                invoiceForm.appendChild(customizationsInput);
            }
            customizationsInput.value = JSON.stringify(customizations);
            
            invoiceForm.submit();
        });
    }
    
    // Initial calculation
    calculateTotals();

    // Customize Panel Toggle
    const customizeBtn = document.getElementById('customizeBtn');
    if (customizeBtn) {
        customizeBtn.addEventListener('click', function() {
            const customizePanel = document.getElementById('customizePanel');
            const customizePanelOverlay = document.getElementById('customizePanelOverlay');
            if (customizePanel) customizePanel.classList.add('open');
            if (customizePanelOverlay) customizePanelOverlay.classList.add('active');
        });
    }

    const closeCustomizeBtn = document.getElementById('closeCustomizeBtn');
    if (closeCustomizeBtn) {
        closeCustomizeBtn.addEventListener('click', function() {
            const customizePanel = document.getElementById('customizePanel');
            const customizePanelOverlay = document.getElementById('customizePanelOverlay');
            if (customizePanel) customizePanel.classList.remove('open');
            if (customizePanelOverlay) customizePanelOverlay.classList.remove('active');
        });
    }

    const customizePanelOverlay = document.getElementById('customizePanelOverlay');
    if (customizePanelOverlay) {
        customizePanelOverlay.addEventListener('click', function() {
            const customizePanel = document.getElementById('customizePanel');
            if (customizePanel) customizePanel.classList.remove('open');
            this.classList.remove('active');
        });
    }

    // Invoice color picker
    const invoiceColorInput = document.getElementById('invoice-color');
    if (invoiceColorInput) {
        invoiceColorInput.addEventListener('change', function() {
            const color = this.value;
            
            // Update invoice heading
            document.querySelectorAll('.invoice-heading').forEach(element => {
                element.style.color = color;
            });
            
            // Update company name - target the brand-teal class on the contenteditable span
            document.querySelectorAll('.brand-teal').forEach(element => {
                element.style.color = color;
            });
            
            // Update total amount
            const totalElement = document.getElementById('total');
            if (totalElement) {
                totalElement.style.color = color;
            }
        
        // Update subtotal number
        const subtotalElement = document.getElementById('subtotal');
        if (subtotalElement) {
            subtotalElement.style.color = color;
        }
        
        // Update tax amount
        const taxAmountElement = document.getElementById('taxAmount');
        if (taxAmountElement) {
            taxAmountElement.style.color = color;
        }
        
        // Update table header background - apply to the thead element
        document.querySelectorAll('.table thead').forEach(element => {
            element.style.backgroundColor = color;
        });
        
        // Also apply to th elements inside thead
        document.querySelectorAll('.table thead th').forEach(element => {
            element.style.backgroundColor = color;
        });
        
        // Store color for print (CSS variable)
        document.documentElement.style.setProperty('--invoice-accent-color', color);
        });
    }

    // Field toggles - reuse the fieldMappings declared earlier
    document.querySelectorAll('#customizePanel input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const fieldKey = this.id;
            const fieldName = fieldMappings[fieldKey];
            const sectionName = sectionMappings[fieldKey];
            
            // Handle sections with data-section attribute FIRST (before regular fields)
            if (sectionName) {
                // Try to find the section - use ID first (most reliable), then data-section
                const targetSection = document.getElementById(sectionName)
                    || document.querySelector(`[data-section="${sectionName}"]`)
                    || document.querySelector(`.invoice-container [data-section="${sectionName}"]`);
                if (targetSection) {
                    if (this.checked) {
                        targetSection.style.display = '';
                        // For totals row, also show the parent row if either payment-details or totals is visible
                        if (sectionName === 'payment-details' || sectionName === 'totals') {
                            const totalsRow = targetSection.closest('.row');
                            if (totalsRow) {
                                const paymentDetailsCheckbox = document.getElementById('show-payment-details');
                                const totalsCheckbox = document.getElementById('show-totals');
                                const paymentDetailsVisible = paymentDetailsCheckbox ? paymentDetailsCheckbox.checked : true;
                                const totalsVisible = totalsCheckbox ? totalsCheckbox.checked : true;
                                if (paymentDetailsVisible || totalsVisible) {
                                    totalsRow.style.display = '';
                                }
                            }
                        }
                    } else {
                        targetSection.style.display = 'none';
                        // For totals row, hide the parent row if both payment-details and totals are hidden
                        if (sectionName === 'payment-details' || sectionName === 'totals') {
                            const totalsRow = targetSection.closest('.row');
                            if (totalsRow) {
                                const paymentDetailsCheckbox = document.getElementById('show-payment-details');
                                const totalsCheckbox = document.getElementById('show-totals');
                                const paymentDetailsVisible = paymentDetailsCheckbox ? paymentDetailsCheckbox.checked : true;
                                const totalsVisible = totalsCheckbox ? totalsCheckbox.checked : true;
                                if (!paymentDetailsVisible && !totalsVisible) {
                                    totalsRow.style.display = 'none';
                                } else {
                                    // Make sure row is visible if at least one section is visible
                                    totalsRow.style.display = '';
                                }
                            }
                        }
                    }
                }
            }
            // Handle regular fields with data-field attribute (only if not a section)
            else if (fieldName) {
                const targetElements = document.querySelectorAll(`[data-field="${fieldName}"]`);
                targetElements.forEach(element => {
                    if (this.checked) {
                        element.style.display = '';
                    } else {
                        element.style.display = 'none';
                    }
                });
            }
        });
    });
});
</script>
@endpush
