@extends('layouts.dashboard')

@section('title', 'Clients - ' . $drive->name)

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
                            <li class="breadcrumb-item active">Clients</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">
                        <i class="fas fa-users me-2"></i>Clients
                    </h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <div>
                    @if($drive->canEdit(auth()->user()))
                        <a href="{{ route('drives.clients.create', $drive) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>New Client
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

    @forelse($clients as $client)
        <div class="dashboard-card mb-3">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <h5 class="mb-2">{{ $client->name }}</h5>
                    <p class="text-muted mb-1 small">
                        <i class="fas fa-envelope me-1"></i>{{ $client->email }}
                    </p>
                    @if($client->phone)
                        <p class="text-muted mb-1 small">
                            <i class="fas fa-phone me-1"></i>{{ $client->phone }}
                        </p>
                    @endif
                    @if($client->full_address)
                        <p class="text-muted mb-0 small">
                            <i class="fas fa-map-marker-alt me-1"></i>{{ $client->full_address }}
                        </p>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    @if($drive->canEdit(auth()->user()))
                        <a href="{{ route('drives.clients.edit', [$drive, $client]) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('drives.clients.destroy', [$drive, $client]) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="dashboard-card text-center py-5">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <h5>No Clients Yet</h5>
            <p class="text-muted">Add clients for faster invoice creation</p>
            @if($drive->canEdit(auth()->user()))
                <a href="{{ route('drives.clients.create', $drive) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add Client
                </a>
            @endif
        </div>
    @endforelse
</div>
@endsection

