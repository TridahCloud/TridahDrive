@extends('layouts.dashboard')

@section('title', $budget->name . ' - ' . $drive->name)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('drives.index') }}">Drives</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('drives.show', $drive) }}">{{ $drive->name }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('drives.bookkeeper.transactions.index', $drive) }}">BookKeeper</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('drives.bookkeeper.budgets.index', $drive) }}">Budgets</a></li>
                            <li class="breadcrumb-item active">{{ $budget->name }}</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">
                        <i class="fas fa-chart-pie me-2"></i>{{ $budget->name }}
                    </h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <div class="d-flex gap-2">
                    @if($drive->canEdit(auth()->user()))
                        <a href="{{ route('drives.bookkeeper.budgets.edit', [$drive, $budget]) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Edit
                        </a>
                    @endif
                    <a href="{{ route('drives.bookkeeper.budgets.index', $drive) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="dashboard-card mb-4">
                <h5 class="mb-3">Budget Details</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Budget Name</label>
                            <p class="mb-0">{{ $budget->name }}</p>
                        </div>
                    </div>
                    @if($budget->category)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small">Category</label>
                                <p class="mb-0">
                                    <a href="{{ route('drives.bookkeeper.categories.show', [$drive, $budget->category]) }}">
                                        <span class="badge" style="background-color: {{ $budget->category->color }}">{{ $budget->category->name }}</span>
                                    </a>
                                </p>
                            </div>
                        </div>
                    @endif
                    @if($budget->description)
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="text-muted small">Description</label>
                                <p class="mb-0">{{ $budget->description }}</p>
                            </div>
                        </div>
                    @endif
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Budget Amount</label>
                            <p class="mb-0"><strong>{{ currency_for($budget->amount, $drive) }}</strong></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Period Type</label>
                            <p class="mb-0">{{ ucfirst($budget->period_type) }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Start Date</label>
                            <p class="mb-0">{{ $budget->start_date->format('M d, Y') }}</p>
                        </div>
                    </div>
                    @if($budget->end_date)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small">End Date</label>
                                <p class="mb-0">{{ $budget->end_date->format('M d, Y') }}</p>
                            </div>
                        </div>
                    @endif
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Status</label>
                            <p class="mb-0">
                                @if($budget->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Budget Progress -->
            <div class="dashboard-card mb-4">
                <h5 class="mb-3">Budget Progress</h5>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Total Spent:</span>
                        <strong class="{{ $budget->total_spent > $budget->amount ? 'text-danger' : 'text-success' }}">
                            {{ currency_for($budget->total_spent, $drive) }}
                        </strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Remaining:</span>
                        <strong class="{{ $budget->remaining < 0 ? 'text-danger' : 'text-success' }}">
                            {{ currency_for($budget->remaining, $drive) }}
                        </strong>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="progress" style="height: 25px;">
                        @php
                            $percentage = min(100, ($budget->total_spent / $budget->amount) * 100);
                            $progressClass = $percentage > 100 ? 'bg-danger' : ($percentage > 80 ? 'bg-warning' : 'bg-success');
                        @endphp
                        <div class="progress-bar {{ $progressClass }}" role="progressbar" 
                             style="width: {{ $percentage }}%" 
                             aria-valuenow="{{ $percentage }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            {{ number_format($percentage, 1) }}%
                        </div>
                    </div>
                </div>
            </div>

            @if($budget->transactions->count() > 0)
                <div class="dashboard-card">
                    <h5 class="mb-3">Budget Transactions</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Number</th>
                                    <th>Description</th>
                                    <th>Account</th>
                                    <th>Category</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($budget->transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->date->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('drives.bookkeeper.transactions.show', [$drive, $transaction]) }}">
                                                <code>{{ $transaction->transaction_number }}</code>
                                            </a>
                                        </td>
                                        <td>{{ Str::limit($transaction->description, 40) }}</td>
                                        <td>{{ $transaction->account->name }}</td>
                                        <td>
                                            @if($transaction->category)
                                                <span class="badge" style="background-color: {{ $transaction->category->color }}">{{ $transaction->category->name }}</span>
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
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="dashboard-card text-center py-4">
                    <i class="fas fa-inbox fa-3x mb-3 text-muted"></i>
                    <p class="text-muted">No transactions assigned to this budget yet.</p>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="dashboard-card mb-4">
                <h5 class="mb-3">Quick Actions</h5>
                <div class="d-grid gap-2">
                    @if($drive->canEdit(auth()->user()))
                        <a href="{{ route('drives.bookkeeper.transactions.create', $drive) }}?budget_id={{ $budget->id }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>New Transaction
                        </a>
                    @endif
                    <a href="{{ route('drives.bookkeeper.transactions.index', [$drive, 'budget_id' => $budget->id]) }}" class="btn btn-outline-primary">
                        <i class="fas fa-list me-2"></i>View All Transactions
                    </a>
                </div>
            </div>

            <div class="dashboard-card">
                <h5 class="mb-3">Budget Summary</h5>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Budgeted:</span>
                    <strong>{{ currency_for($budget->amount, $drive) }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Spent:</span>
                    <strong class="{{ $budget->total_spent > $budget->amount ? 'text-danger' : 'text-success' }}">
                        {{ currency_for($budget->total_spent, $drive) }}
                    </strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Remaining:</span>
                    <strong class="{{ $budget->remaining < 0 ? 'text-danger' : 'text-success' }}">
                        {{ currency_for($budget->remaining, $drive) }}
                    </strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <span class="text-muted">Usage:</span>
                    <strong>{{ number_format($budget->percentage_used, 1) }}%</strong>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

