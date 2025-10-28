@extends('layouts.dashboard')

@section('title', 'Create Client - ' . $drive->name)

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
                            <li class="breadcrumb-item"><a href="{{ route('drives.clients.index', $drive) }}">Clients</a></li>
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">Create Client</h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <a href="{{ route('drives.clients.index', $drive) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
            </div>
        </div>
    </div>

    <form action="{{ route('drives.clients.store', $drive) }}" method="POST">
        @csrf
        
        <div class="row">
            <div class="col-lg-8">
                <div class="dashboard-card mb-4">
                    <h4 class="mb-3 brand-teal">Client Information</h4>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Client Name *</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Street Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2">{{ old('address') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" value="{{ old('city') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="state" class="form-label">State</label>
                            <input type="text" class="form-control" id="state" name="state" value="{{ old('state') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="zip" class="form-label">ZIP</label>
                            <input type="text" class="form-control" id="zip" name="zip" value="{{ old('zip') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="country" class="form-label">Country</label>
                        <input type="text" class="form-control" id="country" name="country" value="{{ old('country', 'United States') }}">
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Additional notes about this client...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('drives.clients.index', $drive) }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create Client
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

