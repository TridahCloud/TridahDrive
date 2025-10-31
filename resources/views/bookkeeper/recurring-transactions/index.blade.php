@extends('layouts.dashboard')

@section('title', 'Recurring Transactions - ' . $drive->name)

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
                            <li class="breadcrumb-item"><a href="{{ route('drives.bookkeeper.dashboard', $drive) }}">BookKeeper</a></li>
                            <li class="breadcrumb-item active">Recurring Transactions</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">
                        <i class="fas fa-sync-alt me-2"></i>Recurring Transactions
                    </h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('drives.bookkeeper.recurring-transactions.upcoming', $drive) }}" class="btn btn-info">
                        <i class="fas fa-calendar-alt me-2"></i>Upcoming
                    </a>
                    <a href="{{ route('drives.bookkeeper.recurring-transactions.create', $drive) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>New Recurring Transaction
                    </a>
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

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="dashboard-card">
                <form method="GET" action="{{ route('drives.bookkeeper.recurring-transactions.index', $drive) }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="income" {{ request('type') === 'income' ? 'selected' : '' }}>Income</option>
                            <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>Expense</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="">All</option>
                            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Frequency</label>
                        <select name="frequency" class="form-select">
                            <option value="">All Frequencies</option>
                            <option value="daily" {{ request('frequency') === 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="weekly" {{ request('frequency') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ request('frequency') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="yearly" {{ request('frequency') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-filter me-2"></i>Apply Filters
                        </button>
                        <a href="{{ route('drives.bookkeeper.recurring-transactions.index', $drive) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Recurring Transactions Table -->
    <div class="row">
        <div class="col-12">
            <div class="dashboard-card">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Frequency</th>
                                <th>Amount</th>
                                <th>Next Due Date</th>
                                <th>Account</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recurringTransactions as $recurringTransaction)
                                <tr class="{{ $recurringTransaction->isOverdue() ? 'table-danger' : ($recurringTransaction->isDue() ? 'table-warning' : '') }}">
                                    <td>
                                        <strong>{{ $recurringTransaction->name }}</strong>
                                        @if($recurringTransaction->description)
                                            <br><small class="text-muted">{{ Str::limit($recurringTransaction->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $recurringTransaction->type === 'income' ? 'success' : 'danger' }}">
                                            {{ ucfirst($recurringTransaction->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($recurringTransaction->frequency) }}</span>
                                    </td>
                                    <td class="{{ $recurringTransaction->type === 'income' ? 'text-success' : 'text-danger' }}">
                                        <strong>${{ number_format($recurringTransaction->amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        @if($recurringTransaction->isOverdue())
                                            <span class="text-danger">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                {{ $recurringTransaction->next_due_date->format('M d, Y') }}
                                            </span>
                                        @elseif($recurringTransaction->isDue())
                                            <span class="text-warning">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ $recurringTransaction->next_due_date->format('M d, Y') }}
                                            </span>
                                        @else
                                            {{ $recurringTransaction->next_due_date->format('M d, Y') }}
                                        @endif
                                    </td>
                                    <td>{{ $recurringTransaction->account->name }}</td>
                                    <td>
                                        @if($recurringTransaction->category)
                                            <span class="badge" style="background-color: {{ $recurringTransaction->category->color }}">
                                                {{ $recurringTransaction->category->name }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($recurringTransaction->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('drives.bookkeeper.recurring-transactions.show', [$drive, $recurringTransaction]) }}" class="btn btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('drives.bookkeeper.recurring-transactions.edit', [$drive, $recurringTransaction]) }}" class="btn btn-outline-secondary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($recurringTransaction->is_active)
                                                <form action="{{ route('drives.bookkeeper.recurring-transactions.generate', [$drive, $recurringTransaction]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success" title="Generate Transaction">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="fas fa-sync-alt fa-3x mb-3"></i>
                                        <p>No recurring transactions found. <a href="{{ route('drives.bookkeeper.recurring-transactions.create', $drive) }}">Create your first recurring transaction</a></p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

