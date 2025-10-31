@extends('layouts.dashboard')

@section('title', 'Upcoming Recurring Transactions - ' . $drive->name)

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
                            <li class="breadcrumb-item"><a href="{{ route('drives.bookkeeper.dashboard', $drive) }}">BookKeeper</a></li>
                            <li class="breadcrumb-item active">Upcoming Transactions</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">
                        <i class="fas fa-calendar-alt me-2"></i>Upcoming Recurring Transactions
                    </h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('drives.bookkeeper.recurring-transactions.create', $drive) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>New Recurring Transaction
                    </a>
                    <a href="{{ route('drives.bookkeeper.recurring-transactions.index', $drive) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list me-2"></i>All Recurring Transactions
                    </a>
                    <a href="{{ route('drives.bookkeeper.dashboard', $drive) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
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

    <!-- Due/Overdue Transactions -->
    @if($due->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card border-danger">
                    <h4 class="text-danger mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>Due / Overdue Transactions
                    </h4>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Frequency</th>
                                    <th>Due Date</th>
                                    <th>Amount</th>
                                    <th>Account</th>
                                    <th>Category</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($due as $recurringTransaction)
                                    <tr class="table-danger">
                                        <td>
                                            <strong>{{ $recurringTransaction->name }}</strong>
                                            @if($recurringTransaction->description)
                                                <br><small class="text-muted">{{ Str::limit($recurringTransaction->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $recurringTransaction->type === 'income' ? 'success' : 'danger' }}">
                                                {{ ucfirst($recurringTransaction->type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ ucfirst($recurringTransaction->frequency) }}</span>
                                        </td>
                                        <td class="text-danger">
                                            <strong>{{ $recurringTransaction->next_due_date->format('M d, Y') }}</strong>
                                            @if($recurringTransaction->isOverdue())
                                                <br><small>{{ $recurringTransaction->next_due_date->diffForHumans() }}</small>
                                            @endif
                                        </td>
                                        <td class="{{ $recurringTransaction->type === 'income' ? 'text-success' : 'text-danger' }}">
                                            <strong>${{ number_format($recurringTransaction->amount, 2) }}</strong>
                                        </td>
                                        <td>{{ $recurringTransaction->account->name }}</td>
                                        <td>
                                            @if($recurringTransaction->category)
                                                <span class="badge" style="background-color: {{ $recurringTransaction->category->color }}">
                                                    {{ $recurringTransaction->category->name }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateModal{{ $recurringTransaction->id }}">
                                                    <i class="fas fa-check me-1"></i>Generate
                                                </button>
                                                <form action="{{ route('drives.bookkeeper.recurring-transactions.skip', [$drive, $recurringTransaction]) }}" method="POST" class="d-inline" onsubmit="return confirm('Skip this occurrence? The next due date will be updated.');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-warning" title="Skip this occurrence">
                                                        <i class="fas fa-forward"></i>
                                                    </button>
                                                </form>
                                                <a href="{{ route('drives.bookkeeper.recurring-transactions.show', [$drive, $recurringTransaction]) }}" class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
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

    <!-- Upcoming Transactions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="dashboard-card">
                <h4 class="mb-3">Upcoming Transactions (Next {{ $days }} Days)</h4>
                @if($upcoming->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Frequency</th>
                                    <th>Due Date</th>
                                    <th>Amount</th>
                                    <th>Account</th>
                                    <th>Category</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($upcoming as $recurringTransaction)
                                    <tr class="{{ $recurringTransaction->isDue() ? 'table-warning' : '' }}">
                                        <td>
                                            <strong>{{ $recurringTransaction->name }}</strong>
                                            @if($recurringTransaction->description)
                                                <br><small class="text-muted">{{ Str::limit($recurringTransaction->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $recurringTransaction->type === 'income' ? 'success' : 'danger' }}">
                                                {{ ucfirst($recurringTransaction->type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ ucfirst($recurringTransaction->frequency) }}</span>
                                        </td>
                                        <td>
                                            {{ $recurringTransaction->next_due_date->format('M d, Y') }}
                                            @if($recurringTransaction->next_due_date->isToday())
                                                <br><small class="text-warning">Today!</small>
                                            @elseif($recurringTransaction->next_due_date->isTomorrow())
                                                <br><small class="text-info">Tomorrow</small>
                                            @endif
                                        </td>
                                        <td class="{{ $recurringTransaction->type === 'income' ? 'text-success' : 'text-danger' }}">
                                            <strong>${{ number_format($recurringTransaction->amount, 2) }}</strong>
                                        </td>
                                        <td>{{ $recurringTransaction->account->name }}</td>
                                        <td>
                                            @if($recurringTransaction->category)
                                                <span class="badge" style="background-color: {{ $recurringTransaction->category->color }}">
                                                    {{ $recurringTransaction->category->name }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateModal{{ $recurringTransaction->id }}">
                                                    <i class="fas fa-check me-1"></i>Generate
                                                </button>
                                                <form action="{{ route('drives.bookkeeper.recurring-transactions.skip', [$drive, $recurringTransaction]) }}" method="POST" class="d-inline" onsubmit="return confirm('Skip this occurrence? The next due date will be updated.');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-warning" title="Skip this occurrence">
                                                        <i class="fas fa-forward"></i>
                                                    </button>
                                                </form>
                                                <a href="{{ route('drives.bookkeeper.recurring-transactions.show', [$drive, $recurringTransaction]) }}" class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No upcoming transactions in the next {{ $days }} days.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Generate Transaction Modals -->
@foreach($upcoming->merge($due) as $recurringTransaction)
    <div class="modal fade" id="generateModal{{ $recurringTransaction->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('drives.bookkeeper.recurring-transactions.generate', [$drive, $recurringTransaction]) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p><strong>{{ $recurringTransaction->name }}</strong></p>
                        <p class="text-muted">{{ $recurringTransaction->description }}</p>
                        
                        <div class="mb-3">
                            <label class="form-label">Transaction Date</label>
                            <input type="date" name="transaction_date" class="form-control" value="{{ $recurringTransaction->next_due_date->format('Y-m-d') }}">
                            <small class="text-muted">Default: {{ $recurringTransaction->next_due_date->format('M d, Y') }}</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Amount <small class="text-muted">(Default: ${{ number_format($recurringTransaction->amount, 2) }})</small></label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0" value="{{ $recurringTransaction->amount }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description <small class="text-muted">(Optional override)</small></label>
                            <textarea name="description" class="form-control" rows="2" placeholder="{{ $recurringTransaction->description ?? $recurringTransaction->name }}">{{ $recurringTransaction->description ?? $recurringTransaction->name }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="cleared">Cleared</option>
                                <option value="reconciled">Reconciled</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-2"></i>Generate Transaction
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection

