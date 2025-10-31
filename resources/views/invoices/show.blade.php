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
            <a href="{{ route('drives.invoices.edit', [$drive, $invoice]) }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
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
                <h2 class="mb-2 brand-teal">{{ $invoice->invoiceProfile?->company_name ?? 'Company Name' }}</h2>
                <p class="text-muted mb-1">{{ $invoice->invoiceProfile?->company_address ?? 'Address' }}</p>
                <p class="text-muted mb-1">{{ $invoice->invoiceProfile?->company_phone ?? 'Phone' }}</p>
                <p class="text-muted mb-0">{{ $invoice->invoiceProfile?->company_email ?? 'Email' }}</p>
            </div>
            <div class="text-end">
                <h1 class="text-primary mb-3">INVOICE</h1>
                <div class="text-muted small">
                    <div class="mb-1">Invoice # <span class="text-dark">{{ $invoice->invoice_number }}</span></div>
                    <div class="mb-1">Date: <strong>{{ $invoice->issue_date->format('M d, Y') }}</strong></div>
                    <div class="mb-1">Due: <strong>{{ $invoice->due_date->format('M d, Y') }}</strong></div>
                    <div>
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
                </div>
            </div>
        </div>

        <!-- Bill To -->
        <div class="border-top pt-4 pb-4 mb-4">
            <h6 class="text-uppercase text-muted mb-3">Bill To:</h6>
            <div>
                <strong>{{ $invoice->client_name }}</strong><br>
                {{ $invoice->client_address }}<br>
                {{ $invoice->client_email }}
            </div>
        </div>

        <!-- Invoice Items -->
        <div class="mb-5">
            <table class="table table-bordered">
                <thead class="bg-primary text-white">
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
                        <td>${{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end">${{ number_format($item->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="row">
            <div class="col-md-6">
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
            <div class="col-md-6">
                <div class="text-end">
                    <div class="mb-3">
                        <strong>Subtotal: $<span id="subtotal">{{ number_format($invoice->subtotal, 2) }}</span></strong>
                    </div>
                    @if($invoice->tax_rate > 0)
                    <div class="mb-3">
                        <strong>Tax ({{ number_format($invoice->tax_rate, 2) }}%): $<span>{{ number_format($invoice->tax_amount, 2) }}</span></strong>
                    </div>
                    @endif
                    <div class="h3 mb-0">
                        <strong>Total: <span class="brand-teal">${{ number_format($invoice->total, 2) }}</span></strong>
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

