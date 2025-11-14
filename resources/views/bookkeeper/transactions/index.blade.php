@extends('layouts.dashboard')

@section('title', 'BookKeeper - ' . $drive->name)

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
                        <i class="fas fa-book me-2"></i>BookKeeper
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
                            <li><a class="dropdown-item" href="{{ route('drives.bookkeeper.budgets.index', $drive) }}">
                                <i class="fas fa-chart-pie me-2"></i>Budgets
                            </a></li>
                        </ul>
                    </div>
                    <a href="{{ route('drives.bookkeeper.tax-report', $drive) }}" class="btn btn-success">
                        <i class="fas fa-file-invoice-dollar me-2"></i>Tax Report
                    </a>
                    @if($drive->canEdit(auth()->user()))
                        <a href="{{ route('drives.bookkeeper.transactions.create', $drive) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>New Transaction
                        </a>
                    @endif
                    <a href="{{ route('drives.bookkeeper.dashboard', $drive) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
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

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="dashboard-card text-center">
                <h3 class="mb-0 text-success">{{ currency_for($stats['total_income'] ?? 0, $drive) }}</h3>
                <p class="text-muted mb-0">Total Income</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card text-center">
                <h3 class="mb-0 text-danger">{{ currency_for($stats['total_expense'] ?? 0, $drive) }}</h3>
                <p class="text-muted mb-0">Total Expenses</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card text-center">
                <h3 class="mb-0 brand-teal">{{ $stats['total_pending'] ?? 0 }}</h3>
                <p class="text-muted mb-0">Pending Transactions</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="dashboard-card">
                <form method="GET" action="{{ route('drives.bookkeeper.transactions.index', $drive) }}" class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="income" {{ request('type') === 'income' ? 'selected' : '' }}>Income</option>
                            <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>Expense</option>
                            <option value="transfer" {{ request('type') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                            <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="cleared" {{ request('status') === 'cleared' ? 'selected' : '' }}>Cleared</option>
                            <option value="reconciled" {{ request('status') === 'reconciled' ? 'selected' : '' }}>Reconciled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Account</label>
                        <select name="account_id" class="form-select">
                            <option value="">All Accounts</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Budget</label>
                        <select name="budget_id" class="form-select">
                            <option value="">All Budgets</option>
                            @foreach($drive->budgets()->where('is_active', true)->orderBy('name')->get() as $budget)
                                <option value="{{ $budget->id }}" {{ request('budget_id') == $budget->id ? 'selected' : '' }}>{{ $budget->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-2"></i>Apply Filters
                        </button>
                        <a href="{{ route('drives.bookkeeper.transactions.index', $drive) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Clear Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="row">
        <div class="col-12">
            <div class="dashboard-card">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Number</th>
                                <th>Description</th>
                                <th>Account</th>
                                <th>Category</th>
                                <th>Budget</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                                <tr>
                                    <td>{{ $drive->formatForUser(\Carbon\Carbon::parse($transaction->date), 'M d, Y', auth()->user()) }}</td>
                                    <td>
                                        <code>{{ $transaction->transaction_number }}</code>
                                        @if($transaction->drive && $transaction->drive->id !== $drive->id)
                                            <br><small class="badge bg-info">From: {{ $transaction->drive->name }}</small>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($transaction->description, 50) }}</td>
                                    <td>{{ $transaction->account->name }}</td>
                                    <td>
                                        @if($transaction->category)
                                            @php
                                                // Use the transaction's drive for the category route (handles sub-drives correctly)
                                                $categoryDrive = $transaction->drive ?? $drive;
                                            @endphp
                                            <a href="{{ route('drives.bookkeeper.categories.show', [$categoryDrive, $transaction->category]) }}" 
                                               class="badge text-decoration-none" 
                                               style="background-color: {{ $transaction->category->color }}; color: white; cursor: pointer;"
                                               title="View category details">
                                                {{ $transaction->category->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($transaction->budget)
                                            @php
                                                // Use the transaction's drive for the budget route (handles sub-drives correctly)
                                                $budgetDrive = $transaction->drive ?? $drive;
                                            @endphp
                                            <a href="{{ route('drives.bookkeeper.budgets.show', [$budgetDrive, $transaction->budget]) }}" 
                                               class="text-decoration-none"
                                               title="View budget details">
                                                {{ $transaction->budget->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->type === 'income' ? 'success' : ($transaction->type === 'expense' ? 'danger' : 'secondary') }}">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </td>
                                    <td class="{{ $transaction->type === 'income' ? 'text-success' : 'text-danger' }}">
                                        <strong>{{ currency_for($transaction->amount, $drive) }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->status === 'reconciled' ? 'success' : ($transaction->status === 'cleared' ? 'info' : 'warning') }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('drives.bookkeeper.transactions.show', [$drive, $transaction]) }}" class="btn btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($drive->canEdit(auth()->user()))
                                                <a href="{{ route('drives.bookkeeper.transactions.edit', [$drive, $transaction]) }}" class="btn btn-outline-secondary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-5">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>No transactions found. @if($drive->canEdit(auth()->user()))<a href="{{ route('drives.bookkeeper.transactions.create', $drive) }}">Create your first transaction</a>@endif</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

