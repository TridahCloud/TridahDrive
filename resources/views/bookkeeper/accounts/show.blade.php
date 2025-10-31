@extends('layouts.dashboard')

@section('title', $account->name . ' - ' . $drive->name)

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
                            <li class="breadcrumb-item"><a href="{{ route('drives.bookkeeper.accounts.index', $drive) }}">Accounts</a></li>
                            <li class="breadcrumb-item active">{{ $account->name }}</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">
                        <i class="fas fa-wallet me-2"></i>{{ $account->name }}
                    </h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('drives.bookkeeper.accounts.edit', [$drive, $account]) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit
                    </a>
                    <a href="{{ route('drives.bookkeeper.accounts.index', $drive) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="dashboard-card mb-4">
                <h5 class="mb-3">Account Details</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Account Code</label>
                            <p class="mb-0"><code>{{ $account->account_code }}</code></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Account Type</label>
                            <p class="mb-0">
                                <span class="badge bg-primary">{{ ucfirst($account->type) }}</span>
                            </p>
                        </div>
                    </div>
                    @if($account->parent)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small">Parent Account</label>
                                <p class="mb-0">
                                    <a href="{{ route('drives.bookkeeper.accounts.show', [$drive, $account->parent]) }}">
                                        {{ $account->parent->account_code }} - {{ $account->parent->name }}
                                    </a>
                                </p>
                            </div>
                        </div>
                    @endif
                    @if($account->subtype)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small">Subtype</label>
                                <p class="mb-0">{{ $account->subtype }}</p>
                            </div>
                        </div>
                    @endif
                    @if($account->description)
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="text-muted small">Description</label>
                                <p class="mb-0">{{ $account->description }}</p>
                            </div>
                        </div>
                    @endif
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Status</label>
                            <p class="mb-0">
                                @if($account->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Created</label>
                            <p class="mb-0">{{ $account->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($account->transactions->count() > 0)
                <div class="dashboard-card">
                    <h5 class="mb-3">Recent Transactions</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Number</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($account->transactions->take(10) as $transaction)
                                    <tr>
                                        <td>{{ $transaction->date->format('M d, Y') }}</td>
                                        <td><code>{{ $transaction->transaction_number }}</code></td>
                                        <td>{{ Str::limit($transaction->description, 40) }}</td>
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
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="dashboard-card mb-4">
                <h5 class="mb-3">Quick Actions</h5>
                <div class="d-grid gap-2">
                    <a href="{{ route('drives.bookkeeper.transactions.create', $drive) }}?account_id={{ $account->id }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>New Transaction
                    </a>
                    <a href="{{ route('drives.bookkeeper.transactions.index', [$drive, 'account_id' => $account->id]) }}" class="btn btn-outline-primary">
                        <i class="fas fa-list me-2"></i>View All Transactions
                    </a>
                </div>
            </div>

            @if($account->children->count() > 0)
                <div class="dashboard-card">
                    <h5 class="mb-3">Child Accounts</h5>
                    <ul class="list-unstyled">
                        @foreach($account->children as $child)
                            <li class="mb-2">
                                <a href="{{ route('drives.bookkeeper.accounts.show', [$drive, $child]) }}" class="text-decoration-none">
                                    <code>{{ $child->account_code }}</code> - {{ $child->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

