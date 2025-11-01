@extends('layouts.dashboard')

@section('title', 'Transaction ' . $transaction->transaction_number . ' - ' . $drive->name)

@push('styles')
<style>
    .card-img-top {
        cursor: pointer;
    }
</style>
@endpush

@php
    use Illuminate\Support\Facades\Storage;
@endphp

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
                            <li class="breadcrumb-item active">{{ $transaction->transaction_number }}</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">
                        <i class="fas fa-receipt me-2"></i>Transaction {{ $transaction->transaction_number }}
                    </h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('drives.bookkeeper.transactions.edit', [$drive, $transaction]) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit
                    </a>
                    <a href="{{ route('drives.bookkeeper.transactions.index', $drive) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="dashboard-card mb-4">
                <h5 class="mb-3">Transaction Details</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Transaction Number</label>
                            <p class="mb-0"><code>{{ $transaction->transaction_number }}</code></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Date</label>
                            <p class="mb-0">{{ $transaction->date->format('F d, Y') }}</p>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="text-muted small">Description</label>
                            <p class="mb-0">{{ $transaction->description }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Type</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ $transaction->type === 'income' ? 'success' : ($transaction->type === 'expense' ? 'danger' : 'secondary') }}">
                                    {{ ucfirst($transaction->type) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Amount</label>
                            <p class="mb-0">
                                <strong class="{{ $transaction->type === 'income' ? 'text-success' : 'text-danger' }}" style="font-size: 1.25rem;">
                                    {{ currency_for($transaction->amount, $drive) }}
                                </strong>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Account</label>
                            <p class="mb-0">
                                <a href="{{ route('drives.bookkeeper.accounts.show', [$drive, $transaction->account]) }}">
                                    {{ $transaction->account->account_code }} - {{ $transaction->account->name }}
                                </a>
                            </p>
                        </div>
                    </div>
                    @if($transaction->category)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small">Category</label>
                                <p class="mb-0">
                                    <a href="{{ route('drives.bookkeeper.categories.show', [$drive, $transaction->category]) }}">
                                        <span class="badge" style="background-color: {{ $transaction->category->color }}">{{ $transaction->category->name }}</span>
                                    </a>
                                </p>
                            </div>
                        </div>
                    @endif
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Status</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ $transaction->status === 'reconciled' ? 'success' : ($transaction->status === 'cleared' ? 'info' : 'warning') }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    @if($transaction->payee)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small">Payee</label>
                                <p class="mb-0">{{ $transaction->payee }}</p>
                            </div>
                        </div>
                    @endif
                    @if($transaction->payment_method)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small">Payment Method</label>
                                <p class="mb-0">{{ ucfirst(str_replace('_', ' ', $transaction->payment_method)) }}</p>
                            </div>
                        </div>
                    @endif
                    @if($transaction->reference)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small">Reference</label>
                                <p class="mb-0"><code>{{ $transaction->reference }}</code></p>
                            </div>
                        </div>
                    @endif
                    @if($transaction->notes)
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="text-muted small">Notes</label>
                                <p class="mb-0">{{ $transaction->notes }}</p>
                            </div>
                        </div>
                    @endif
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Created By</label>
                            <p class="mb-0">{{ $transaction->creator->name ?? 'Unknown' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Created At</label>
                            <p class="mb-0">{{ $transaction->created_at->format('M d, Y g:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($transaction->attachments->count() > 0)
                <div class="dashboard-card">
                    <h5 class="mb-3">Attachments</h5>
                    <div class="row">
                        @foreach($transaction->attachments as $attachment)
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    @if(str_starts_with($attachment->mime_type, 'image/'))
                                        <a href="{{ route('drives.bookkeeper.transactions.attachments.show', [$drive, $transaction, $attachment]) }}" target="_blank" class="text-decoration-none">
                                            <img src="{{ route('drives.bookkeeper.transactions.attachments.show', [$drive, $transaction, $attachment]) }}" alt="{{ $attachment->original_filename }}" class="card-img-top" style="max-height: 200px; object-fit: cover;">
                                        </a>
                                    @endif
                                    <div class="card-body">
                                        @if(!str_starts_with($attachment->mime_type, 'image/'))
                                            <div class="text-center mb-2">
                                                <i class="fas fa-file fa-3x text-muted"></i>
                                            </div>
                                        @endif
                                        <h6 class="card-title text-truncate" title="{{ $attachment->original_filename }}">
                                            {{ $attachment->original_filename }}
                                        </h6>
                                        <p class="text-muted small mb-2">{{ $attachment->human_readable_size }}</p>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('drives.bookkeeper.transactions.attachments.show', [$drive, $transaction, $attachment]) }}" target="_blank" class="btn btn-sm btn-outline-primary flex-fill">
                                                <i class="fas fa-download me-1"></i>View
                                            </a>
                                            <form action="{{ route('drives.bookkeeper.transactions.attachments.destroy', [$drive, $transaction, $attachment]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this attachment?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="dashboard-card mb-4">
                <h5 class="mb-3">Quick Actions</h5>
                <div class="d-grid gap-2">
                    <a href="{{ route('drives.bookkeeper.transactions.edit', [$drive, $transaction]) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit Transaction
                    </a>
                    <form action="{{ route('drives.bookkeeper.transactions.destroy', [$drive, $transaction]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this transaction?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-2"></i>Delete Transaction
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

