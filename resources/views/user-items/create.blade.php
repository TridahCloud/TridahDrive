@extends('layouts.dashboard')

@section('title', 'Create Line Item - ' . $drive->name)

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
                            <li class="breadcrumb-item"><a href="{{ route('drives.user-items.index', $drive) }}">Line Items</a></li>
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">Create Line Item</h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <a href="{{ route('drives.user-items.index', $drive) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
            </div>
        </div>
    </div>

    <form action="{{ route('drives.user-items.store', $drive) }}" method="POST">
        @csrf
        
        <div class="row">
            <div class="col-lg-8">
                <div class="dashboard-card mb-4">
                    <h4 class="mb-3 brand-teal">Item Information</h4>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Item Name *</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g., Web Design, Consultation, Product">
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Detailed description of the item...">{{ old('description') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="unit" class="form-label">Unit</label>
                            <input type="text" class="form-control" id="unit" name="unit" value="{{ old('unit', 'items') }}" placeholder="e.g., hours, items, words">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="default_price" class="form-label">Default Price</label>
                            <input type="number" class="form-control" id="default_price" name="default_price" value="{{ old('default_price', 0) }}" step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('drives.user-items.index', $drive) }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create Item
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

