@extends('layouts.dashboard')

@section('title', 'Line Items - ' . $drive->name)

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
                            <li class="breadcrumb-item"><a href="{{ route('drives.invoices.index', $drive) }}">Invoices</a></li>
                            <li class="breadcrumb-item active">Line Items</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">
                        <i class="fas fa-boxes me-2"></i>Line Items
                    </h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <div>
                    @if($drive->canEdit(auth()->user()))
                        <a href="{{ route('drives.user-items.create', $drive) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>New Item
                        </a>
                    @endif
                    <a href="{{ route('drives.invoices.index', $drive) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
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

    @forelse($userItems as $item)
        <div class="dashboard-card mb-3">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <h5 class="mb-2">{{ $item->name }}</h5>
                    @if($item->description)
                        <p class="text-muted mb-1 small">{{ $item->description }}</p>
                    @endif
                    <p class="text-muted mb-0 small">
                        Unit: <strong>{{ $item->unit }}</strong> &bull; 
                        Default Price: <strong>{{ currency_for($item->default_price, $drive) }}</strong>
                    </p>
                </div>
                @if($drive->canEdit(auth()->user()))
                    <div class="d-flex gap-2">
                        <a href="{{ route('drives.user-items.edit', [$drive, $item]) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('drives.user-items.destroy', [$drive, $item]) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="dashboard-card text-center py-5">
            <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
            <h5>No Line Items Yet</h5>
            <p class="text-muted">Create reusable line items for faster invoice creation</p>
            @if($drive->canEdit(auth()->user()))
                <a href="{{ route('drives.user-items.create', $drive) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add Item
                </a>
            @endif
        </div>
    @endforelse
</div>
@endsection

