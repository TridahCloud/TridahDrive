@extends('layouts.dashboard')

@section('title', 'BookKeeper Dashboard - ' . $drive->name)

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
                            <li class="breadcrumb-item active">BookKeeper</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">
                        <i class="fas fa-book me-2"></i>BookKeeper Dashboard
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
                            <li><a class="dropdown-item" href="{{ route('drives.bookkeeper.accounts.index', $drive) }}">
                                <i class="fas fa-wallet me-2"></i>Accounts
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('drives.bookkeeper.categories.index', $drive) }}">
                                <i class="fas fa-tags me-2"></i>Categories
                            </a></li>
                        </ul>
                    </div>
                    <a href="{{ route('drives.bookkeeper.transactions.index', $drive) }}" class="btn btn-primary">
                        <i class="fas fa-list me-2"></i>View All Transactions
                    </a>
                    <a href="{{ route('drives.bookkeeper.transactions.create', $drive) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>New Transaction
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

    <!-- Financial Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="dashboard-card text-center">
                <h3 class="mb-0 text-success">${{ number_format($stats['total_income'], 2) }}</h3>
                <p class="text-muted mb-0">Income This Month</p>
                <small class="text-muted">{{ $dateFrom->format('M d') }} - {{ $dateTo->format('M d, Y') }}</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card text-center">
                <h3 class="mb-0 text-danger">${{ number_format($stats['total_expense'], 2) }}</h3>
                <p class="text-muted mb-0">Expenses This Month</p>
                <small class="text-muted">{{ $dateFrom->format('M d') }} - {{ $dateTo->format('M d, Y') }}</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card text-center">
                <h3 class="mb-0 {{ $stats['net_income'] >= 0 ? 'text-success' : 'text-danger' }}">
                    ${{ number_format($stats['net_income'], 2) }}
                </h3>
                <p class="text-muted mb-0">Net Income</p>
                <small class="text-muted">This Month</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card text-center">
                <h3 class="mb-0 brand-teal">{{ $stats['pending_transactions'] }}</h3>
                <p class="text-muted mb-0">Pending Transactions</p>
                <small class="text-muted">Requires attention</small>
            </div>
        </div>
    </div>

    <!-- Transaction Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="dashboard-card text-center">
                <h4 class="mb-0">{{ $stats['total_transactions'] }}</h4>
                <p class="text-muted mb-0">Total Transactions</p>
                <small class="text-muted">This Month</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card text-center">
                <h4 class="mb-0 text-info">{{ $stats['cleared_transactions'] }}</h4>
                <p class="text-muted mb-0">Cleared</p>
                <small class="text-muted">This Month</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card text-center">
                <h4 class="mb-0 text-success">{{ $stats['reconciled_transactions'] }}</h4>
                <p class="text-muted mb-0">Reconciled</p>
                <small class="text-muted">This Month</small>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Transactions -->
        <div class="col-md-8">
            <div class="dashboard-card mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Recent Transactions</h5>
                    <a href="{{ route('drives.bookkeeper.transactions.index', $drive) }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                @if($recentTransactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Number</th>
                                    <th>Description</th>
                                    <th>Account</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTransactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->date->format('M d') }}</td>
                                        <td><code>{{ $transaction->transaction_number }}</code></td>
                                        <td>{{ Str::limit($transaction->description, 40) }}</td>
                                        <td>{{ Str::limit($transaction->account->name, 20) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $transaction->type === 'income' ? 'success' : ($transaction->type === 'expense' ? 'danger' : 'secondary') }}">
                                                {{ ucfirst($transaction->type) }}
                                            </span>
                                        </td>
                                        <td class="{{ $transaction->type === 'income' ? 'text-success' : 'text-danger' }}">
                                            <strong>${{ number_format($transaction->amount, 2) }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $transaction->status === 'reconciled' ? 'success' : ($transaction->status === 'cleared' ? 'info' : 'warning') }}">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No transactions yet. <a href="{{ route('drives.bookkeeper.transactions.create', $drive) }}">Create your first transaction</a></p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Stats Sidebar -->
        <div class="col-md-4">
            @if($topAccounts->count() > 0)
                <div class="dashboard-card mb-4">
                    <h5 class="mb-3">Most Used Accounts</h5>
                    <ul class="list-unstyled">
                        @foreach($topAccounts as $account)
                            <li class="mb-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <code>{{ $account->account_code }}</code>
                                    <span class="ms-2">{{ Str::limit($account->name, 25) }}</span>
                                </div>
                                <span class="badge bg-brand-teal">{{ $account->transactions_count }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($topCategories->count() > 0)
                <div class="dashboard-card mb-4">
                    <h5 class="mb-3">Most Used Categories</h5>
                    <ul class="list-unstyled">
                        @foreach($topCategories as $category)
                            <li class="mb-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <div style="width: 15px; height: 15px; background-color: {{ $category->color }}; border-radius: 3px; display: inline-block; margin-right: 8px;"></div>
                                    <span>{{ Str::limit($category->name, 20) }}</span>
                                </div>
                                <span class="badge bg-brand-teal">{{ $category->transactions_count }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="dashboard-card">
                <h5 class="mb-3">Quick Actions</h5>
                <div class="d-grid gap-2">
                    <a href="{{ route('drives.bookkeeper.transactions.create', $drive) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>New Transaction
                    </a>
                    <a href="{{ route('drives.bookkeeper.accounts.index', $drive) }}" class="btn btn-outline-primary">
                        <i class="fas fa-wallet me-2"></i>Manage Accounts
                    </a>
                    <a href="{{ route('drives.bookkeeper.categories.index', $drive) }}" class="btn btn-outline-primary">
                        <i class="fas fa-tags me-2"></i>Manage Categories
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

