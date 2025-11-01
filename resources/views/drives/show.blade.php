@extends('layouts.dashboard')

@section('title', $drive->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="display-6 mb-0 brand-teal">{{ $drive->name }}</h1>
                    <p class="text-muted">{{ $drive->description ?? 'No description' }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('drives.invoices.index', $drive) }}" class="btn btn-primary">
                        <i class="fas fa-file-invoice me-2"></i>Invoices
                    </a>
                    <a href="{{ route('drives.bookkeeper.transactions.index', $drive) }}" class="btn btn-primary">
                        <i class="fas fa-book me-2"></i>BookKeeper
                    </a>
                    <a href="{{ route('drives.projects.projects.index', $drive) }}" class="btn btn-primary">
                        <i class="fas fa-tasks me-2"></i>Project Board
                    </a>
                    @can('update', $drive)
                        <a href="{{ route('drives.edit', $drive) }}" class="btn btn-outline-primary">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                    @endcan
                    @if($drive->type === 'shared')
                        <span class="badge bg-brand-blue align-self-center px-3 py-2">
                            {{ ucfirst($drive->getUserRole(auth()->user())) }}
                        </span>
                        @if($drive->owner_id !== auth()->id())
                            <form action="{{ route('drives.members.leave', $drive) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to leave this drive?')">
                                    <i class="fas fa-sign-out-alt me-2"></i>Leave Drive
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Apps Overview -->
    <div class="row mb-4">
        <!-- Invoicer App Card -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="dashboard-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="mb-1">
                            <i class="fas fa-file-invoice me-2 brand-teal"></i>
                            Invoicer
                        </h5>
                        <p class="text-muted small mb-0">Create and manage invoices</p>
                    </div>
                    <a href="{{ route('drives.invoices.index', $drive) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="text-center p-2 stats-card rounded">
                            <h4 class="mb-0 brand-teal">{{ $invoiceStats['total'] ?? 0 }}</h4>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-2 stats-card rounded">
                            <h4 class="mb-0 text-success">{{ currency_for($invoiceStats['total_amount'] ?? 0, $drive) }}</h4>
                            <small class="text-muted">Revenue</small>
                        </div>
                    </div>
                </div>
                @if(isset($recentInvoices) && $recentInvoices->count() > 0)
                    <div class="mb-3">
                        <small class="text-muted d-block mb-2">Recent Invoices</small>
                        <div class="list-group list-group-flush">
                            @foreach($recentInvoices->take(3) as $invoice)
                                <a href="{{ route('drives.invoices.show', [$drive, $invoice]) }}" class="list-group-item list-group-item-action px-0 py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong class="small d-block">{{ $invoice->invoice_number }}</strong>
                                            <small class="text-muted">{{ $invoice->client->name ?? 'No Client' }}</small>
                                        </div>
                                        <span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'draft' ? 'secondary' : 'warning') }}">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
                <a href="{{ route('drives.invoices.create', $drive) }}" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-plus me-1"></i>New Invoice
                </a>
            </div>
        </div>

        <!-- BookKeeper App Card -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="dashboard-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="mb-1">
                            <i class="fas fa-book me-2 brand-teal"></i>
                            BookKeeper
                        </h5>
                        <p class="text-muted small mb-0">Track transactions and accounts</p>
                    </div>
                    <a href="{{ route('drives.bookkeeper.dashboard', $drive) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <div class="text-center p-2 stats-card rounded">
                            <h4 class="mb-0 brand-teal">{{ $bookkeeperStats['total_transactions'] ?? 0 }}</h4>
                            <small class="text-muted">Transactions</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-center p-2 stats-card rounded">
                            <h4 class="mb-0 text-info">{{ $bookkeeperStats['total_accounts'] ?? 0 }}</h4>
                            <small class="text-muted">Accounts</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-center p-2 stats-card rounded">
                            <h4 class="mb-0 text-secondary">{{ $bookkeeperStats['total_categories'] ?? 0 }}</h4>
                            <small class="text-muted">Categories</small>
                        </div>
                    </div>
                </div>
                @if(isset($recentTransactions) && $recentTransactions->count() > 0)
                    <div class="mb-3">
                        <small class="text-muted d-block mb-2">Recent Transactions</small>
                        <div class="list-group list-group-flush">
                            @foreach($recentTransactions->take(3) as $transaction)
                                <a href="{{ route('drives.bookkeeper.transactions.show', [$drive, $transaction]) }}" class="list-group-item list-group-item-action px-0 py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong class="small d-block">{{ $transaction->description ? Str::limit($transaction->description, 30) : 'Transaction #' . $transaction->id }}</strong>
                                            <small class="text-muted">{{ $transaction->account->name ?? 'No Account' }}</small>
                                        </div>
                                        <span class="badge bg-{{ $transaction->type === 'income' ? 'success' : 'danger' }}">
                                            {{ currency_for(abs($transaction->amount), $drive) }}
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
                <a href="{{ route('drives.bookkeeper.transactions.create', $drive) }}" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-plus me-1"></i>New Transaction
                </a>
            </div>
        </div>

        <!-- Project Board App Card -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="dashboard-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="mb-1">
                            <i class="fas fa-tasks me-2 brand-teal"></i>
                            Project Board
                        </h5>
                        <p class="text-muted small mb-0">Manage projects and tasks</p>
                    </div>
                    <a href="{{ route('drives.projects.projects.index', $drive) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <div class="text-center p-2 stats-card rounded">
                            <h4 class="mb-0 brand-teal">{{ $projectStats['total_projects'] ?? 0 }}</h4>
                            <small class="text-muted">Projects</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-center p-2 stats-card rounded">
                            <h4 class="mb-0 text-success">{{ $projectStats['active_projects'] ?? 0 }}</h4>
                            <small class="text-muted">Active</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-center p-2 stats-card rounded">
                            <h4 class="mb-0 text-info">{{ $projectStats['total_tasks'] ?? 0 }}</h4>
                            <small class="text-muted">Tasks</small>
                        </div>
                    </div>
                </div>
                @if(isset($recentProjects) && $recentProjects->count() > 0)
                    <div class="mb-3">
                        <small class="text-muted d-block mb-2">Recent Projects</small>
                        <div class="list-group list-group-flush">
                            @foreach($recentProjects->take(3) as $project)
                                <a href="{{ route('drives.projects.projects.show', [$drive, $project]) }}" class="list-group-item list-group-item-action px-0 py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong class="small d-block">{{ $project->name }}</strong>
                                            <small class="text-muted">{{ $project->tasks->count() }} tasks</small>
                                        </div>
                                        <span class="badge bg-{{ $project->status === 'active' ? 'success' : ($project->status === 'completed' ? 'info' : 'secondary') }}">
                                            {{ ucfirst($project->status) }}
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
                <a href="{{ route('drives.projects.projects.create', $drive) }}" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-plus me-1"></i>New Project
                </a>
            </div>
        </div>
    </div>

    <!-- Empty State (only show if all apps are empty) -->
    @if(($invoiceStats['total'] ?? 0) == 0 && ($bookkeeperStats['total_transactions'] ?? 0) == 0 && ($projectStats['total_projects'] ?? 0) == 0)
        <div class="row">
            <div class="col-12">
                <div class="dashboard-card text-center py-5">
                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                    <h4 class="mb-2">Get Started</h4>
                    <p class="text-muted">Create your first invoice, transaction, or project to get started</p>
                    <div class="mt-3 d-flex gap-2 justify-content-center">
                        <a href="{{ route('drives.invoices.create', $drive) }}" class="btn btn-primary">
                            <i class="fas fa-file-invoice me-2"></i>New Invoice
                        </a>
                        <a href="{{ route('drives.bookkeeper.transactions.create', $drive) }}" class="btn btn-primary">
                            <i class="fas fa-book me-2"></i>New Transaction
                        </a>
                        <a href="{{ route('drives.projects.projects.create', $drive) }}" class="btn btn-primary">
                            <i class="fas fa-tasks me-2"></i>New Project
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

