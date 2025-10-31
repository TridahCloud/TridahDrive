@extends('layouts.dashboard')

@section('title', 'Invoices - ' . $drive->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('drives.index') }}">Drives</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('drives.show', $drive) }}">{{ $drive->name }}</a></li>
                            <li class="breadcrumb-item active">Invoices</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">
                        <i class="fas fa-file-invoice me-2"></i>Invoices
                    </h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <div class="d-flex gap-2">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-2"></i>Manage
                        </button>
                        <ul class="dropdown-menu">
                            <li><h6 class="dropdown-header">Setup & Configuration</h6></li>
                            <li><a class="dropdown-item" href="{{ route('drives.invoice-profiles.index', $drive) }}">
                                <i class="fas fa-building me-2"></i>Invoice Profiles
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('drives.clients.index', $drive) }}">
                                <i class="fas fa-users me-2"></i>Clients
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('drives.user-items.index', $drive) }}">
                                <i class="fas fa-boxes me-2"></i>Line Items
                            </a></li>
                        </ul>
                    </div>
                    <a href="{{ route('drives.invoices.create', $drive) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>New Invoice
                    </a>
                    <a href="{{ route('drives.show', $drive) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Drive
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Setup Alerts -->
    <div class="row mb-4">
        <div class="col-12">
            @if(!$hasProfile)
                <div class="alert alert-warning alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Setup Required:</strong> Create an invoice profile to set up your company information and defaults.
                    <a href="{{ route('drives.invoice-profiles.create', $drive) }}" class="btn btn-sm btn-warning ms-2">Create Profile</a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @elseif(!$hasClients || !$hasItems)
                <div class="alert alert-info alert-dismissible fade show">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Quick Tip:</strong> 
                    @if(!$hasClients)
                        Add clients for faster invoice creation.
                        <a href="{{ route('drives.clients.create', $drive) }}" class="btn btn-sm btn-info ms-2">Add Clients</a>
                    @endif
                    @if(!$hasItems)
                        Add reusable line items for quicker invoicing.
                        <a href="{{ route('drives.user-items.create', $drive) }}" class="btn btn-sm btn-info ms-2">Add Items</a>
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>
    </div>

    <!-- Stats Cards -->
    @if(isset($stats) && $stats['total'] > 0)
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="dashboard-card text-center">
                    <h3 class="mb-0 brand-teal">{{ $stats['total'] }}</h3>
                    <p class="text-muted mb-0">Total Invoices</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="dashboard-card text-center">
                    <h3 class="mb-0 text-secondary">{{ $stats['draft'] }}</h3>
                    <p class="text-muted mb-0">Draft</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="dashboard-card text-center">
                    <h3 class="mb-0 text-info">{{ $stats['sent'] }}</h3>
                    <p class="text-muted mb-0">Sent</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="dashboard-card text-center">
                    <h3 class="mb-0 text-success">${{ number_format($stats['total_revenue'], 2) }}</h3>
                    <p class="text-muted mb-0">Revenue</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Invoices List -->
    <div class="dashboard-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">All Invoices</h5>
            <div class="btn-group">
                <button class="btn btn-sm btn-outline-primary">All</button>
                <button class="btn btn-sm btn-outline-primary">Draft</button>
                <button class="btn btn-sm btn-outline-primary">Sent</button>
                <button class="btn btn-sm btn-outline-primary">Paid</button>
            </div>
        </div>

        @forelse($invoices as $invoice)
            <div class="border-bottom p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="mb-0">
                                <a href="{{ route('drives.invoices.show', [$drive, $invoice]) }}" class="text-decoration-none">
                                    {{ $invoice->invoice_number }}
                                </a>
                            </h6>
                            <span class="badge 
                                {{ $invoice->status === 'paid' ? 'bg-success' : '' }}
                                {{ $invoice->status === 'sent' ? 'bg-info' : '' }}
                                {{ $invoice->status === 'draft' ? 'bg-secondary' : '' }}
                                {{ $invoice->status === 'cancelled' ? 'bg-danger' : '' }}
                            ">
                                {{ ucfirst($invoice->status) }}
                            </span>
                            @if($invoice->status === 'paid' && isset($syncedTransactions) && in_array($invoice->invoice_number, $syncedTransactions))
                                <span class="badge bg-primary" title="Synced with BookKeeper">
                                    <i class="fas fa-book me-1"></i>Synced
                                </span>
                            @endif
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-user me-1"></i>{{ $invoice->client_name }} 
                            @if($invoice->project)
                                &bull; <i class="fas fa-briefcase me-1"></i>{{ $invoice->project }}
                            @endif
                            &bull; 
                            <i class="fas fa-calendar me-1"></i>{{ $invoice->issue_date->format('M d, Y') }}
                            @if($invoice->due_date)
                                &bull; <i class="fas fa-calendar-check me-1"></i>
                                <span class="{{ $invoice->due_date < now() && $invoice->status !== 'paid' && $invoice->status !== 'cancelled' ? 'text-danger fw-bold' : '' }}">
                                    Due: {{ $invoice->due_date->format('M d, Y') }}
                                </span>
                            @endif
                            &bull; <i class="fas fa-dollar-sign me-1"></i>{{ number_format($invoice->total, 2) }}
                        </small>
                    </div>
                    <div class="d-flex gap-2">
                        <form action="{{ route('drives.invoices.update', [$drive, $invoice]) }}" method="POST" class="d-inline" id="statusForm-{{ $invoice->id }}">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="form-select form-select-sm" style="width: auto;" onchange="document.getElementById('statusForm-{{ $invoice->id }}').submit();">
                                <option value="draft" {{ $invoice->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="sent" {{ $invoice->status === 'sent' ? 'selected' : '' }}>Sent</option>
                                <option value="paid" {{ $invoice->status === 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="overdue" {{ $invoice->status === 'overdue' ? 'selected' : '' }}>Overdue</option>
                                <option value="cancelled" {{ $invoice->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </form>
                        <a href="{{ route('drives.invoices.show', [$drive, $invoice]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('drives.invoices.edit', [$drive, $invoice]) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('drives.invoices.destroy', [$drive, $invoice]) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                <h5>No Invoices Yet</h5>
                <p class="text-muted">Create your first invoice to get started!</p>
                <a href="{{ route('drives.invoices.create', $drive) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Create Invoice
                </a>
            </div>
        @endforelse

        @if($invoices->hasPages())
            <div class="mt-3">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

