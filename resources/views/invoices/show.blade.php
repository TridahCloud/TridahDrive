@extends('layouts.dashboard')

@section('title', 'Invoice ' . $invoice->invoice_number . ' - ' . $drive->name)

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
                    $customizations = $invoice->customizations ?? [];
                    $accentColor = $customizations['accent_color'] ?? $invoice->invoiceProfile?->accent_color ?? '#31d8b2';
                @endphp
                @if($customizations['show_company_name'] ?? true)
                <h2 class="mb-2 brand-teal" style="color: {{ $accentColor }};" data-field="company-name">{{ $invoice->invoiceProfile?->company_name ?? 'Company Name' }}</h2>
                @endif
                @if($customizations['show_company_address'] ?? true)
                <p class="text-muted mb-1" data-field="company-address">{{ $invoice->invoiceProfile?->company_address ?? 'Address' }}</p>
                @endif
                @if($customizations['show_company_phone'] ?? true)
                <p class="text-muted mb-1" data-field="company-phone">{{ $invoice->invoiceProfile?->company_phone ?? 'Phone' }}</p>
                @endif
                @if($customizations['show_company_email'] ?? true)
                <p class="text-muted mb-0" data-field="company-email">{{ $invoice->invoiceProfile?->company_email ?? 'Email' }}</p>
                @endif
            </div>
            <div class="text-end">
                @if($customizations['show_invoice_title'] ?? true)
                <h1 class="text-primary mb-3" style="color: {{ $accentColor }};" data-field="invoice-title">INVOICE</h1>
                @endif
                <div class="text-muted small">
                    @if($customizations['show_invoice_number'] ?? true)
                    <div class="mb-1" data-field="invoice-number">Invoice # <span class="text-dark">{{ $invoice->invoice_number }}</span></div>
                    @endif
                    @if($customizations['show_invoice_date'] ?? true)
                    <div class="mb-1" data-field="invoice-date">Date: <strong>{{ $invoice->issue_date->format('M d, Y') }}</strong></div>
                    @endif
                    @if($customizations['show_invoice_due_date'] ?? true)
                    <div class="mb-1" data-field="invoice-due-date">Due: <strong>{{ $invoice->due_date->format('M d, Y') }}</strong></div>
                    @endif
                    @if($customizations['show_invoice_status'] ?? true)
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
        @if(($customizations['show_client_name'] ?? true) || ($customizations['show_client_address'] ?? true) || ($customizations['show_client_email'] ?? true))
        <div class="border-top pt-4 pb-4 mb-4">
            <h6 class="text-uppercase text-muted mb-3">Bill To:</h6>
            <div>
                @if($customizations['show_client_name'] ?? true)
                <strong data-field="client-name">{{ $invoice->client_name }}</strong><br>
                @endif
                @if($customizations['show_client_address'] ?? true && $invoice->client_address)
                <span data-field="client-address">{{ $invoice->client_address }}</span><br>
                @endif
                @if($customizations['show_client_email'] ?? true && $invoice->client_email)
                <span data-field="client-email">{{ $invoice->client_email }}</span>
                @endif
                @if($customizations['show_project'] ?? true && $invoice->project)
                <div class="mt-2" data-field="project">
                    <small class="text-muted">Project:</small> {{ $invoice->project }}
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Invoice Items -->
        @if($customizations['show_items_table'] ?? true)
        <div class="mb-5" data-field="items-table">
            <table class="table table-bordered">
                <thead class="bg-primary text-white" style="background-color: {{ $accentColor }};">
                    <tr>
                        <th style="width: 40%;">DESCRIPTION</th>
                        <th style="width: 12%;">QTY</th>
                        <th style="width: 18%;">UNIT</th>
                        <th style="width: 15%;">RATE</th>
                        <th style="width: 15%;" class="text-end">AMOUNT</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->unit }}</td>
                        <td>{{ currency_for($item->unit_price, $drive) }}</td>
                        <td class="text-end">{{ currency_for($item->amount, $drive) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Totals -->
        @if(($customizations['show_payment_details'] ?? true) || ($customizations['show_totals'] ?? true))
        <div class="row">
            @if($customizations['show_payment_details'] ?? true)
            <div class="col-md-6" data-field="payment-details">
                @if($invoice->invoiceProfile?->bank_name)
                <h6 class="text-uppercase text-muted mb-3">Payment Details:</h6>
                <div class="bg-light p-3 rounded">
                    <div class="mb-2">
                        <small class="text-muted">Bank:</small>
                        <div class="d-block">{{ $invoice->invoiceProfile->bank_name }}</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Routing Number:</small>
                        <div class="d-block">{{ $invoice->invoiceProfile->bank_routing_number }}</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Account Name:</small>
                        <div class="d-block">{{ $invoice->invoiceProfile->bank_account_name }}</div>
                    </div>
                    <div>
                        <small class="text-muted">Account Number:</small>
                        <div class="d-block">{{ $invoice->invoiceProfile->bank_account_number }}</div>
                    </div>
                </div>
                @endif
            </div>
            @endif
            @if($customizations['show_totals'] ?? true)
            <div class="col-md-6" data-field="totals">
                <div class="text-end">
                    <div class="mb-3">
                        <strong style="color: {{ $accentColor }};">Subtotal: {{ currency_for($invoice->subtotal, $drive) }}</strong>
                    </div>
                    @if($invoice->tax_rate > 0)
                    <div class="mb-3">
                        <strong style="color: {{ $accentColor }};">Tax ({{ number_format($invoice->tax_rate, 2) }}%): {{ currency_for($invoice->tax_amount, $drive) }}</strong>
                    </div>
                    @endif
                    <div class="h3 mb-0">
                        <strong>Total: <span style="color: {{ $accentColor }};">{{ currency_for($invoice->total, $drive) }}</span></strong>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif

        @if($invoice->notes)
        <div class="mt-5 border-top pt-4">
            <h6 class="text-uppercase text-muted mb-2">Notes:</h6>
            <p>{{ $invoice->notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection

