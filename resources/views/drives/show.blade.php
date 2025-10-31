@extends('layouts.dashboard')

@section('title', $drive->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="display-6 mb-0 brand-teal">{{ $drive->name }}</h1>
                    <p class="text-muted">{{ $drive->description ?? 'No description' }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('drives.invoices.index', $drive) }}" class="btn btn-primary">
                        <i class="fas fa-file-invoice me-2"></i>Invoices
                    </a>
                    <a href="{{ route('drives.bookkeeper.transactions.index', $drive) }}" class="btn btn-primary">
                        <i class="fas fa-book me-2"></i>BookKeeper
                    </a>
                    @can('update', $drive)
                        <a href="{{ route('drives.edit', $drive) }}" class="btn btn-outline-primary">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                    @endcan
                    @if($drive->type === 'shared')
                        <span class="badge bg-brand-blue align-self-center px-3 py-2">
                            {{ ucfirst($drive->getUserRole(auth()->user())) }}
                        </span>
                        @if($drive->owner_id !== auth()->id())
                            <form action="{{ route('drives.members.leave', $drive) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to leave this drive?')">
                                    <i class="fas fa-sign-out-alt me-2"></i>Leave Drive
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Invoice Stats -->
    @if(isset($invoiceStats) && $invoiceStats['total'] > 0)
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <h3 class="mb-0 brand-teal">{{ $invoiceStats['total'] }}</h3>
                    <p class="text-muted mb-0">Total Invoices</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <h3 class="mb-0 text-secondary">{{ $invoiceStats['draft'] }}</h3>
                    <p class="text-muted mb-0">Draft</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <h3 class="mb-0 text-success">{{ $invoiceStats['paid'] }}</h3>
                    <p class="text-muted mb-0">Paid</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <h3 class="mb-0 brand-teal">${{ number_format($invoiceStats['total_amount'], 2) }}</h3>
                    <p class="text-muted mb-0">Total Revenue</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Drive Items by Tool Type -->
    <div class="row">
        @forelse($itemsByType as $toolType => $items)
            <div class="col-12 mb-4">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">
                            <i class="fas fa-{{ $toolType === 'invoice' ? 'file-invoice' : ($toolType === 'bookkeeper' ? 'book' : 'file') }} me-2 brand-teal"></i>
                            {{ ucfirst($toolType) }} Files
                        </h4>
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-brand-teal">{{ $items->count() }} items</span>
                            @if($toolType === 'invoice')
                                <a href="{{ route('drives.invoices.create', $drive) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus me-1"></i>New Invoice
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Created</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                    <tr>
                                        <td>
                                            <i class="fas fa-file me-2 text-muted"></i>
                                            {{ $item->name }}
                                        </td>
                                        <td>{{ $item->created_at->diffForHumans() }}</td>
                                        <td>{{ $item->creator->name }}</td>
                                        <td>
                                            @can('update', $item)
                                                <button class="btn btn-sm btn-outline-primary">Edit</button>
                                            @endcan
                                            @can('delete', $item)
                                                <form action="{{ route('drives.items.destroy', [$drive, $item]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                                        Delete
                                                    </button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="dashboard-card text-center py-5">
                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                    <h4 class="mb-2">No items yet</h4>
                    <p class="text-muted">Start by creating your first invoice or using the bookkeeper tool</p>
                    <div class="mt-3">
                        <a href="{{ route('drives.invoices.create', $drive) }}" class="btn btn-primary me-2">
                            <i class="fas fa-file-invoice me-2"></i>New Invoice
                        </a>
                        <a href="{{ route('drives.bookkeeper.transactions.index', $drive) }}" class="btn btn-primary">
                            <i class="fas fa-book me-2"></i>Open BookKeeper
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection

