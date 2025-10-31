@extends('layouts.dashboard')

@section('title', 'Accounts - ' . $drive->name)

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
                            <li class="breadcrumb-item"><a href="{{ route('drives.bookkeeper.transactions.index', $drive) }}">BookKeeper</a></li>
                            <li class="breadcrumb-item active">Accounts</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">
                        <i class="fas fa-wallet me-2"></i>Chart of Accounts
                    </h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('drives.bookkeeper.accounts.create', $drive) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>New Account
                    </a>
                    <a href="{{ route('drives.bookkeeper.transactions.index', $drive) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to BookKeeper
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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Accounts by Type -->
    @foreach(['asset', 'liability', 'equity', 'revenue', 'expense'] as $accountType)
        @php
            $typeAccounts = $accounts->where('type', $accountType);
        @endphp
        
        @if($typeAccounts->count() > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="dashboard-card">
                        <h5 class="mb-3">
                            <i class="fas fa-{{ $accountType === 'asset' ? 'building' : ($accountType === 'liability' ? 'file-invoice' : ($accountType === 'equity' ? 'balance-scale' : ($accountType === 'revenue' ? 'arrow-up' : 'arrow-down'))) }} me-2"></i>
                            {{ ucfirst($accountType) }}s
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Subtype</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($typeAccounts as $account)
                                        <tr>
                                            <td><code>{{ $account->account_code }}</code></td>
                                            <td>{{ $account->name }}</td>
                                            <td>{{ $account->subtype ?? '-' }}</td>
                                            <td>{{ Str::limit($account->description, 50) }}</td>
                                            <td>
                                                @if($account->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('drives.bookkeeper.accounts.show', [$drive, $account]) }}" class="btn btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('drives.bookkeeper.accounts.edit', [$drive, $account]) }}" class="btn btn-outline-secondary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('drives.bookkeeper.accounts.destroy', [$drive, $account]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this account?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    @if($accounts->isEmpty())
        <div class="row">
            <div class="col-12">
                <div class="dashboard-card text-center py-5">
                    <i class="fas fa-wallet fa-3x mb-3 text-muted"></i>
                    <p class="text-muted">No accounts found. <a href="{{ route('drives.bookkeeper.accounts.create', $drive) }}">Create your first account</a></p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

