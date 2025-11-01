@extends('layouts.dashboard')

@section('title', 'Recurring Transaction - ' . $drive->name)

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
                            <li class="breadcrumb-item"><a href="{{ route('drives.bookkeeper.dashboard', $drive) }}">BookKeeper</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('drives.bookkeeper.recurring-transactions.index', $drive) }}">Recurring Transactions</a></li>
                            <li class="breadcrumb-item active">{{ $recurringTransaction->name }}</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">
                        <i class="fas fa-sync-alt me-2"></i>{{ $recurringTransaction->name }}
                    </h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <div class="d-flex gap-2">
                    @if($recurringTransaction->is_active)
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateModal">
                            <i class="fas fa-check me-2"></i>Generate Transaction
                        </button>
                    @endif
                    <a href="{{ route('drives.bookkeeper.recurring-transactions.edit', [$drive, $recurringTransaction]) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit
                    </a>
                    <form action="{{ route('drives.bookkeeper.recurring-transactions.destroy', [$drive, $recurringTransaction]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this recurring transaction? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Delete
                        </button>
                    </form>
                    <a href="{{ route('drives.bookkeeper.recurring-transactions.index', $drive) }}" class="btn btn-outline-secondary">
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

    <div class="row">
        <div class="col-md-8">
            <div class="dashboard-card mb-4">
                <h4 class="mb-3">Transaction Details</h4>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Name:</th>
                                <td><strong>{{ $recurringTransaction->name }}</strong></td>
                            </tr>
                            <tr>
                                <th>Description:</th>
                                <td>{{ $recurringTransaction->description ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Type:</th>
                                <td>
                                    <span class="badge bg-{{ $recurringTransaction->type === 'income' ? 'success' : 'danger' }}">
                                        {{ ucfirst($recurringTransaction->type) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Frequency:</th>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst($recurringTransaction->frequency) }}</span>
                                    @if($recurringTransaction->frequency_interval && $recurringTransaction->frequency_interval > 1)
                                        <span class="text-muted">(Every {{ $recurringTransaction->frequency_interval }} 
                                            @if($recurringTransaction->frequency === 'daily')
                                                days
                                            @elseif($recurringTransaction->frequency === 'weekly')
                                                weeks
                                            @elseif($recurringTransaction->frequency === 'monthly')
                                                months
                                            @elseif($recurringTransaction->frequency === 'yearly')
                                                years
                                            @endif
                                        )</span>
                                    @endif
                                    @if($recurringTransaction->frequency_day_of_week !== null)
                                        @php
                                            $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                                        @endphp
                                        <br><small class="text-muted">On {{ $days[$recurringTransaction->frequency_day_of_week] }}</small>
                                        @if($recurringTransaction->frequency === 'monthly' && $recurringTransaction->frequency_week_of_month !== null)
                                            @php
                                                $weeks = [1 => 'First', 2 => 'Second', 3 => 'Third', 4 => 'Fourth', 5 => 'Last'];
                                            @endphp
                                            <small class="text-muted"> ({{ $weeks[$recurringTransaction->frequency_week_of_month] }} of month)</small>
                                        @endif
                                    @endif
                                    @if($recurringTransaction->frequency === 'monthly' && $recurringTransaction->frequency_day_of_month !== null && $recurringTransaction->frequency_day_of_week === null)
                                        <br><small class="text-muted">On day {{ $recurringTransaction->frequency_day_of_month }} of month</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Amount:</th>
                                <td class="{{ $recurringTransaction->type === 'income' ? 'text-success' : 'text-danger' }}">
                                    <strong>{{ currency_for($recurringTransaction->amount, $drive) }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <th>Account:</th>
                                <td>{{ $recurringTransaction->account->name }} ({{ $recurringTransaction->account->account_code }})</td>
                            </tr>
                            <tr>
                                <th>Category:</th>
                                <td>
                                    @if($recurringTransaction->category)
                                        <span class="badge" style="background-color: {{ $recurringTransaction->category->color }}">
                                            {{ $recurringTransaction->category->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Start Date:</th>
                                <td>{{ $recurringTransaction->start_date->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <th>End Date:</th>
                                <td>{{ $recurringTransaction->end_date ? $recurringTransaction->end_date->format('M d, Y') : 'Indefinite' }}</td>
                            </tr>
                            <tr>
                                <th>Next Due Date:</th>
                                <td>
                                    @if($recurringTransaction->isOverdue())
                                        <span class="text-danger">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            {{ $recurringTransaction->next_due_date->format('M d, Y') }}
                                        </span>
                                        <br><small class="text-muted">{{ $recurringTransaction->next_due_date->diffForHumans() }}</small>
                                    @elseif($recurringTransaction->isDue())
                                        <span class="text-warning">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ $recurringTransaction->next_due_date->format('M d, Y') }}
                                        </span>
                                    @else
                                        {{ $recurringTransaction->next_due_date->format('M d, Y') }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    @if($recurringTransaction->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            @if($recurringTransaction->payee)
                                <tr>
                                    <th>Payee:</th>
                                    <td>{{ $recurringTransaction->payee }}</td>
                                </tr>
                            @endif
                            @if($recurringTransaction->payment_method)
                                <tr>
                                    <th>Payment Method:</th>
                                    <td>{{ ucfirst(str_replace('_', ' ', $recurringTransaction->payment_method)) }}</td>
                                </tr>
                            @endif
                            <tr>
                                <th>Created:</th>
                                <td>{{ $recurringTransaction->created_at->format('M d, Y g:i A') }}</td>
                            </tr>
                            <tr>
                                <th>Created By:</th>
                                <td>{{ $recurringTransaction->creator->name }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($recurringTransaction->notes)
                    <div class="mt-3">
                        <h5>Notes</h5>
                        <p class="text-muted">{{ $recurringTransaction->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-md-4">
            <div class="dashboard-card">
                <h5 class="mb-3">Quick Actions</h5>
                <div class="d-grid gap-2">
                    @if($recurringTransaction->is_active)
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateModal">
                            <i class="fas fa-check me-2"></i>Generate Transaction
                        </button>
                        <form action="{{ route('drives.bookkeeper.recurring-transactions.skip', [$drive, $recurringTransaction]) }}" method="POST" onsubmit="return confirm('Skip this occurrence? The next due date will be updated.');">
                            @csrf
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="fas fa-forward me-2"></i>Skip Next Occurrence
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('drives.bookkeeper.recurring-transactions.edit', [$drive, $recurringTransaction]) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit Template
                    </a>
                    <a href="{{ route('drives.bookkeeper.recurring-transactions.index', $drive) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list me-2"></i>All Recurring Transactions
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate Transaction Modal -->
<div class="modal fade" id="generateModal" tabindex="-1">
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
                        <label class="form-label">Amount <small class="text-muted">(Default: {{ currency_for($recurringTransaction->amount, $drive) }})</small></label>
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
@endsection

