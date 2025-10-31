@extends('layouts.dashboard')

@section('title', $category->name . ' - ' . $drive->name)

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
                            <li class="breadcrumb-item"><a href="{{ route('drives.bookkeeper.categories.index', $drive) }}">Categories</a></li>
                            <li class="breadcrumb-item active">{{ $category->name }}</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">
                        <i class="fas fa-tag me-2"></i>{{ $category->name }}
                    </h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('drives.bookkeeper.categories.edit', [$drive, $category]) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit
                    </a>
                    <a href="{{ route('drives.bookkeeper.categories.index', $drive) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="dashboard-card mb-4">
                <h5 class="mb-3">Category Details</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Name</label>
                            <div class="d-flex align-items-center">
                                <div style="width: 20px; height: 20px; background-color: {{ $category->color }}; border-radius: 4px; margin-right: 10px;"></div>
                                <p class="mb-0">{{ $category->name }}</p>
                            </div>
                        </div>
                    </div>
                    @if($category->parent)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small">Parent Category</label>
                                <p class="mb-0">
                                    <a href="{{ route('drives.bookkeeper.categories.show', [$drive, $category->parent]) }}">
                                        {{ $category->parent->name }}
                                    </a>
                                </p>
                            </div>
                        </div>
                    @endif
                    @if($category->description)
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="text-muted small">Description</label>
                                <p class="mb-0">{{ $category->description }}</p>
                            </div>
                        </div>
                    @endif
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Status</label>
                            <p class="mb-0">
                                @if($category->is_active)
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
                            <p class="mb-0">{{ $category->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($category->transactions->count() > 0)
                <div class="dashboard-card">
                    <h5 class="mb-3">Recent Transactions</h5>
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
                                @foreach($category->transactions->take(10) as $transaction)
                                    <tr>
                                        <td>{{ $transaction->date->format('M d, Y') }}</td>
                                        <td><code>{{ $transaction->transaction_number }}</code></td>
                                        <td>{{ Str::limit($transaction->description, 40) }}</td>
                                        <td>{{ $transaction->account->name }}</td>
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
                    <a href="{{ route('drives.bookkeeper.transactions.create', $drive) }}?category_id={{ $category->id }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>New Transaction
                    </a>
                    <a href="{{ route('drives.bookkeeper.transactions.index', [$drive, 'category_id' => $category->id]) }}" class="btn btn-outline-primary">
                        <i class="fas fa-list me-2"></i>View All Transactions
                    </a>
                </div>
            </div>

            @if($category->children->count() > 0)
                <div class="dashboard-card">
                    <h5 class="mb-3">Child Categories</h5>
                    <ul class="list-unstyled">
                        @foreach($category->children as $child)
                            <li class="mb-2">
                                <a href="{{ route('drives.bookkeeper.categories.show', [$drive, $child]) }}" class="text-decoration-none">
                                    <div style="width: 15px; height: 15px; background-color: {{ $child->color }}; border-radius: 3px; display: inline-block; margin-right: 8px;"></div>
                                    {{ $child->name }}
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

