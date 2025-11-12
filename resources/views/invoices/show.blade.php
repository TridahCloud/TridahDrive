@extends('layouts.dashboard')

@section('title', 'Invoice ' . $invoice->invoice_number . ' - ' . $drive->name)

@php
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
        .alert {
            display: none !important;
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
        .invoice-container [data-field="totals"] span[style*="color"] {
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
                    <li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('drives.invoices.index', $drive) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
            @if($drive->canEdit(auth()->user()))
                <a href="{{ route('drives.invoices.edit', [$drive, $invoice]) }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-edit me-1"></i>Edit
                </a>
            @endif
            <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
                <i class="fas fa-print me-1"></i>Print
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Invoice Display -->
    <div class="invoice-container rounded shadow-sm p-5" style="max-width: 900px; margin: 0 auto; background-color: var(--bg-secondary, #f8f9fa);">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start mb-5">
            <div>
                @php
                    $logoUrl = null;
                    if ($invoiceProfile?->logo_path) {
                        $logoUrl = Storage::url($invoiceProfile->logo_path);
                    } elseif ($invoiceProfile?->logo_url) {
                        $logoUrl = $invoiceProfile->logo_url;
                    }
                @endphp
                @if($logoUrl && $isEnabled('show_company_logo'))
                    <div class="mb-3" data-field="company-logo">
                        <img src="{{ $logoUrl }}" alt="Company Logo" style="max-height: 80px; max-width: 200px; object-fit: contain;">
                    </div>
                @endif
                @if($isEnabled('show_company_name'))
                <h2 class="mb-2" style="color: {{ $accentColor }} !important;" data-field="company-name">{{ $invoiceProfile?->company_name ?? 'Company Name' }}</h2>
                @endif
                @if($isEnabled('show_company_address'))
                <p class="text-muted mb-1" data-field="company-address">{{ $invoiceProfile?->company_address ?? 'Address' }}</p>
                @endif
                @if($isEnabled('show_company_phone'))
                <p class="text-muted mb-1" data-field="company-phone">{{ $invoiceProfile?->company_phone ?? 'Phone' }}</p>
                @endif
                @if($isEnabled('show_company_email'))
                <p class="text-muted mb-0" data-field="company-email">{{ $invoiceProfile?->company_email ?? 'Email' }}</p>
                @endif
            </div>
            <div class="text-end">
                @if($isEnabled('show_invoice_title'))
                <h1 class="text-primary mb-3" style="color: {{ $accentColor }} !important;" data-field="invoice-title">INVOICE</h1>
                @endif
                <div class="text-muted small">
                    @if($isEnabled('show_invoice_number'))
                    <div class="mb-1" data-field="invoice-number">Invoice # <span class="text-dark">{{ $invoice->invoice_number }}</span></div>
                    @endif
                    @if($isEnabled('show_invoice_date'))
                    <div class="mb-1" data-field="invoice-date">Date: <strong>{{ $drive->formatForUser(\Carbon\Carbon::parse($invoice->issue_date), 'M d, Y', auth()->user()) }}</strong></div>
                    @endif
                    @if($isEnabled('show_invoice_due_date'))
                    <div class="mb-1" data-field="invoice-due-date">Due: <strong>{{ $drive->formatForUser(\Carbon\Carbon::parse($invoice->due_date), 'M d, Y', auth()->user()) }}</strong></div>
                    @endif
                    @if($isEnabled('show_invoice_status'))
                    <div data-field="invoice-status">
                        Status: 
                        <span class="badge 
                            {{ $invoice->status === 'paid' ? 'bg-success' : '' }}
                            {{ $invoice->status === 'sent' ? 'bg-info' : '' }}
                            {{ $invoice->status === 'draft' ? 'bg-secondary' : '' }}
                        ">
                            {{ ucfirst($invoice->status) }}
                        </span>
                        @if($invoice->status === 'paid' && $invoice->bookTransaction())
                            <br>
                            <small class="text-muted">
                                <a href="{{ route('drives.bookkeeper.transactions.show', [$drive, $invoice->bookTransaction()]) }}" class="text-decoration-none">
                                    <i class="fas fa-book me-1"></i>View in BookKeeper
                                </a>
                            </small>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Bill To -->
        @if($isEnabled('show_client_name') || $isEnabled('show_client_address') || $isEnabled('show_client_email') || $isEnabled('show_project'))
        <div class="border-top pt-4 pb-4 mb-4">
            <h6 class="text-uppercase text-muted mb-3">Bill To:</h6>
            <div>
                @if($isEnabled('show_client_name'))
                <strong data-field="client-name">{{ $invoice->client_name }}</strong><br>
                @endif
                @if($isEnabled('show_client_address') && $invoice->client_address)
                <span data-field="client-address">{{ $invoice->client_address }}</span><br>
                @endif
                @if($isEnabled('show_client_email') && $invoice->client_email)
                <span data-field="client-email">{{ $invoice->client_email }}</span>
                @endif
                @if($isEnabled('show_project') && $invoice->project)
                <div class="mt-2" data-field="project">
                    <small class="text-muted">Project:</small> {{ $invoice->project }}
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Items Table -->
        <div class="invoice-section mb-4" id="items-table" data-section="items-table" style="display: {{ $isEnabled('show_items_table') ? '' : 'none' }};">
            <table class="table table-bordered">
                <thead class="bg-primary text-white" style="background-color: {{ $accentColor }} !important;">
                    <tr>
                        <th style="width: 40%; background-color: {{ $accentColor }} !important;">DESCRIPTION</th>
                        <th style="width: 12%; background-color: {{ $accentColor }} !important;">QTY</th>
                        <th style="width: 18%; background-color: {{ $accentColor }} !important;">UNIT</th>
                        <th style="width: 15%; background-color: {{ $accentColor }} !important;">RATE</th>
                        <th style="width: 15%; background-color: {{ $accentColor }} !important;">AMOUNT</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->unit }}</td>
                        <td>{{ currency_for($item->unit_price, $drive) }}</td>
                        <td>{{ currency_for($item->amount, $drive) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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
                                <div class="d-block">{{ $invoiceProfile?->bank_name ?? 'Your Bank' }}</div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Account Name:</small>
                                <div class="d-block">{{ $invoiceProfile?->bank_account_name ?? 'Your Account' }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-2">
                                <small class="text-muted">Routing Number:</small>
                                <div class="d-block">{{ $invoiceProfile?->bank_routing_number ?? '123456' }}</div>
                            </div>
                            <div>
                                <small class="text-muted">Account Number:</small>
                                <div class="d-block">{{ $invoiceProfile?->bank_account_number ?? '123456789' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 invoice-section" id="totals" data-section="totals" style="display: {{ $isEnabled('show_totals') ? '' : 'none' }};">
                <div class="text-end">
                    <div class="mb-3">
                        <strong style="color: {{ $accentColor }} !important;">Subtotal: {{ currency_for($invoice->subtotal, $drive) }}</strong>
                    </div>
                    @if($invoice->tax_rate > 0)
                    <div class="mb-3">
                        <strong style="color: {{ $accentColor }} !important;">Tax ({{ number_format($invoice->tax_rate, 2) }}%): {{ currency_for($invoice->tax_amount, $drive) }}</strong>
                    </div>
                    @endif
                    <div class="h3 mb-0">
                        <strong>Total: <span style="color: {{ $accentColor }} !important;">{{ currency_for($invoice->total, $drive) }}</span></strong>
                    </div>
                </div>
            </div>
        </div>

        @if($invoice->notes)
        <div class="mt-5 border-top pt-4">
            <h6 class="text-uppercase text-muted mb-2">Notes:</h6>
            <p>{{ $invoice->notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection

