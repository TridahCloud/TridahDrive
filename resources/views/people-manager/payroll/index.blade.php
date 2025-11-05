@extends('layouts.dashboard')

@section('title', 'Payroll - ' . $drive->name)

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
                            <li class="breadcrumb-item"><a href="{{ route('drives.people-manager.dashboard', $drive) }}">People Manager</a></li>
                            <li class="breadcrumb-item active">Payroll</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal"><i class="fas fa-money-check-alt me-2"></i>Payroll</h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <div>
                    @if($drive->canEdit(auth()->user()))
                        <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#generateFromTimeLogsModal">
                            <i class="fas fa-magic me-2"></i>Generate from Time Logs
                        </button>
                        <a href="{{ route('drives.people-manager.payroll.create', $drive) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>New Payroll Entry
                        </a>
                    @endif
                    <a href="{{ route('drives.people-manager.dashboard', $drive) }}" class="btn btn-outline-secondary">
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('errors'))
        <div class="alert alert-warning alert-dismissible fade show">
            <strong>Some issues occurred:</strong>
            <ul class="mb-0 mt-2">
                @foreach(session('errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @forelse($payrollEntries as $entry)
        <div class="dashboard-card mb-3">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <h5 class="mb-2">{{ $entry->person->full_name ?? 'Unknown' }}</h5>
                    <p class="text-muted mb-1 small">
                        <i class="fas fa-calendar me-1"></i>{{ $entry->pay_date->format('M d, Y') }}
                        @if($entry->payroll_period)
                            <span class="ms-2">{{ $entry->payroll_period }}</span>
                        @endif
                    </p>
                    @if($entry->net_pay)
                        <p class="mb-0"><strong>{{ currency_for($entry->net_pay, $drive) }}</strong></p>
                    @endif
                    @if(!$entry->synced_to_bookkeeper)
                        <span class="badge bg-warning">Not Synced</span>
                    @else
                        <span class="badge bg-success">Synced</span>
                    @endif
                    @if($entry->status === 'paid')
                        <span class="badge bg-success ms-1">Paid</span>
                    @else
                        <span class="badge bg-secondary ms-1">{{ ucfirst($entry->status) }}</span>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    @if($drive->canEdit(auth()->user()))
                        @if($entry->status !== 'paid')
                            <form action="{{ route('drives.people-manager.payroll.mark-paid', [$drive, $entry]) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" title="Mark as Paid">
                                    <i class="fas fa-check-circle"></i> Mark Paid
                                </button>
                            </form>
                            @if($entry->net_pay && $entry->net_pay > 0)
                                <form action="{{ route('drives.people-manager.payroll.mark-paid-and-sync', [$drive, $entry]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" title="Mark as Paid and Sync to BookKeeper">
                                        <i class="fas fa-check-circle"></i> <i class="fas fa-sync"></i> Paid & Sync
                                    </button>
                                </form>
                            @endif
                        @else
                            <form action="{{ route('drives.people-manager.payroll.mark-unpaid', [$drive, $entry]) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Mark as Unpaid">
                                    <i class="fas fa-times-circle"></i> Unmark Paid
                                </button>
                            </form>
                            @if(!$entry->synced_to_bookkeeper)
                                <form action="{{ route('drives.people-manager.payroll.sync', [$drive, $entry]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-info" title="Sync to BookKeeper">
                                        <i class="fas fa-sync"></i> Sync
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('drives.people-manager.payroll.sync', [$drive, $entry]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning" title="Re-sync to update BookKeeper transaction">
                                        <i class="fas fa-sync-alt"></i> Re-sync
                                    </button>
                                </form>
                            @endif
                        @endif
                    @endif
                    @if($drive->canEdit(auth()->user()))
                        <a href="{{ route('drives.people-manager.payroll.edit', [$drive, $entry]) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('drives.people-manager.payroll.destroy', [$drive, $entry]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this payroll entry? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Payroll Entry">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="dashboard-card text-center py-5">
            <i class="fas fa-money-check-alt fa-3x text-muted mb-3"></i>
            <h5>No Payroll Entries Yet</h5>
            <p class="text-muted">Create payroll entries to track payments</p>
            @if($drive->canEdit(auth()->user()))
                <a href="{{ route('drives.people-manager.payroll.create', $drive) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Create Payroll Entry
                </a>
            @endif
        </div>
    @endforelse
</div>

<!-- Generate from Time Logs Modal -->
@if($drive->canEdit(auth()->user()))
    <div class="modal fade" id="generateFromTimeLogsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('drives.people-manager.payroll.generate-from-time-logs', $drive) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Generate Payroll from Time Logs</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">
                            This will create payroll entries from approved time logs in the selected period.
                        </p>
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date *</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="end_date" class="form-label">End Date *</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="{{ \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="person_id" class="form-label">Person (Optional)</label>
                            <select class="form-select" id="person_id" name="person_id">
                                <option value="">All People</option>
                                @foreach($drive->people()->where('status', 'active')->orderBy('last_name')->orderBy('first_name')->get() as $person)
                                    <option value="{{ $person->id }}">{{ $person->full_name }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Leave blank to generate for all people</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-magic me-2"></i>Generate Payroll
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection

