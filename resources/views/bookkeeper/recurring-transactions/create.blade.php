@extends('layouts.dashboard')

@section('title', 'Create Recurring Transaction - ' . $drive->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0"><i class="fas fa-plus me-2"></i>Create New Recurring Transaction</h4>
                    <a href="{{ route('drives.bookkeeper.recurring-transactions.index', $drive) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>

                <form action="{{ route('drives.bookkeeper.recurring-transactions.store', $drive) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g., Monthly Subscription, Weekly Salary">
                        <small class="text-muted">A short name to identify this recurring transaction</small>
                        @error('name')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span class="text-muted">(Optional)</span></label>
                        <textarea name="description" id="description" class="form-control" rows="2" placeholder="Detailed description of the recurring transaction">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    <option value="income" {{ old('type') === 'income' ? 'selected' : '' }}>Income</option>
                                    <option value="expense" {{ old('type') === 'expense' ? 'selected' : '' }}>Expense</option>
                                </select>
                                @error('type')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="frequency" class="form-label">Frequency <span class="text-danger">*</span></label>
                                <select name="frequency" id="frequency" class="form-select" required>
                                    <option value="">Select Frequency</option>
                                    <option value="daily" {{ old('frequency') === 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="weekly" {{ old('frequency') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="monthly" {{ old('frequency') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="yearly" {{ old('frequency') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                                </select>
                                @error('frequency')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date', date('Y-m-d')) }}" required>
                                <small class="text-muted">When this recurring transaction starts</small>
                                @error('start_date')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="end_date" class="form-label">End Date <span class="text-muted">(Optional)</span></label>
                                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date') }}">
                                <small class="text-muted">When this recurring transaction should stop (leave empty for indefinite)</small>
                                @error('end_date')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="0" value="{{ old('amount') }}" required>
                                </div>
                                @error('amount')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Active Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                                <small class="text-muted">Inactive recurring transactions won't generate new transactions</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="account_id" class="form-label">Account <span class="text-danger">*</span></label>
                                <select name="account_id" id="account_id" class="form-select" required>
                                    <option value="">Select Account</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" {{ old('account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->account_code }} - {{ $account->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('account_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category <span class="text-muted">(Optional)</span></label>
                                <select name="category_id" id="category_id" class="form-select">
                                    <option value="">None</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payee" class="form-label">Payee <span class="text-muted">(Optional)</span></label>
                                <input type="text" name="payee" id="payee" class="form-control" value="{{ old('payee') }}" placeholder="Who receives/pays this amount">
                                @error('payee')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Payment Method <span class="text-muted">(Optional)</span></label>
                                <select name="payment_method" id="payment_method" class="form-select">
                                    <option value="">None</option>
                                    <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="check" {{ old('payment_method') === 'check' ? 'selected' : '' }}>Check</option>
                                    <option value="credit_card" {{ old('payment_method') === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                    <option value="debit_card" {{ old('payment_method') === 'debit_card' ? 'selected' : '' }}>Debit Card</option>
                                    <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="other" {{ old('payment_method') === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('payment_method')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes <span class="text-muted">(Optional)</span></label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Additional notes about this recurring transaction">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Recurring Transaction
                        </button>
                        <a href="{{ route('drives.bookkeeper.recurring-transactions.index', $drive) }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

