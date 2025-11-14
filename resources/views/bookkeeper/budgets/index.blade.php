@extends('layouts.dashboard')

@section('title', 'Budgets - ' . $drive->name)

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
                            <li class="breadcrumb-item active">Budgets</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">
                        <i class="fas fa-chart-pie me-2"></i>Budgets
                    </h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <div class="d-flex gap-2">
                    @if($drive->canEdit(auth()->user()))
                        <a href="{{ route('drives.bookkeeper.budgets.create', $drive) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>New Budget
                        </a>
                    @endif
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

    <!-- Budgets Grid -->
    <div class="row">
        @forelse($budgets as $budget)
            <div class="col-md-6 mb-4">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1">{{ $budget->name }}</h5>
                            @if($budget->category)
                                <span class="badge" style="background-color: {{ $budget->category->color }}">{{ $budget->category->name }}</span>
                            @endif
                        </div>
                        <div>
                            @if($budget->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </div>
                    </div>
                    
                    @if($budget->description)
                        <p class="text-muted small mb-3">{{ Str::limit($budget->description, 150) }}</p>
                    @endif

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Budget Amount:</span>
                            <strong>{{ currency_for($budget->amount, $drive) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Spent:</span>
                            <strong class="{{ $budget->is_over_budget ? 'text-danger' : 'text-success' }}">
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
                        <div class="progress" style="height: 20px;">
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

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted">
                            {{ ucfirst($budget->period_type) }} - 
                            {{ $budget->start_date->format('M d, Y') }}
                            @if($budget->end_date)
                                to {{ $budget->end_date->format('M d, Y') }}
                            @endif
                        </small>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('drives.bookkeeper.budgets.show', [$drive, $budget]) }}" class="btn btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($drive->canEdit(auth()->user()))
                                <a href="{{ route('drives.bookkeeper.budgets.edit', [$drive, $budget]) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('drives.bookkeeper.budgets.destroy', [$drive, $budget]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this budget?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="dashboard-card text-center py-5">
                    <i class="fas fa-chart-pie fa-3x mb-3 text-muted"></i>
                    <p class="text-muted">No budgets found. @if($drive->canEdit(auth()->user()))<a href="{{ route('drives.bookkeeper.budgets.create', $drive) }}">Create your first budget</a>@endif</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection

