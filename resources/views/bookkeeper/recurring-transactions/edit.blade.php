@extends('layouts.dashboard')

@section('title', 'Edit Recurring Transaction - ' . $drive->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Recurring Transaction</h4>
                    <a href="{{ route('drives.bookkeeper.recurring-transactions.show', [$drive, $recurringTransaction]) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>

                <form action="{{ route('drives.bookkeeper.recurring-transactions.update', [$drive, $recurringTransaction]) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $recurringTransaction->name) }}" required>
                        @error('name')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span class="text-muted">(Optional)</span></label>
                        <textarea name="description" id="description" class="form-control" rows="2">{{ old('description', $recurringTransaction->description) }}</textarea>
                        @error('description')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-select" required>
                                    <option value="income" {{ old('type', $recurringTransaction->type) === 'income' ? 'selected' : '' }}>Income</option>
                                    <option value="expense" {{ old('type', $recurringTransaction->type) === 'expense' ? 'selected' : '' }}>Expense</option>
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
                                    <option value="daily" {{ old('frequency', $recurringTransaction->frequency) === 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="weekly" {{ old('frequency', $recurringTransaction->frequency) === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="monthly" {{ old('frequency', $recurringTransaction->frequency) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="yearly" {{ old('frequency', $recurringTransaction->frequency) === 'yearly' ? 'selected' : '' }}>Yearly</option>
                                </select>
                                @error('frequency')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date', $recurringTransaction->start_date->format('Y-m-d')) }}" required>
                                @error('start_date')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="end_date" class="form-label">End Date <span class="text-muted">(Optional)</span></label>
                                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date', $recurringTransaction->end_date?->format('Y-m-d')) }}">
                                @error('end_date')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="next_due_date" class="form-label">Next Due Date <span class="text-danger">*</span></label>
                                <input type="date" name="next_due_date" id="next_due_date" class="form-control" value="{{ old('next_due_date', $recurringTransaction->next_due_date->format('Y-m-d')) }}" required>
                                <small class="text-muted">When is the next occurrence?</small>
                                @error('next_due_date')
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
                                    <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="0" value="{{ old('amount', $recurringTransaction->amount) }}" required>
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
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $recurringTransaction->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="account_id" class="form-label">Account <span class="text-danger">*</span></label>
                                <select name="account_id" id="account_id" class="form-select" required>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" {{ old('account_id', $recurringTransaction->account_id) == $account->id ? 'selected' : '' }}>
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
                                        <option value="{{ $category->id }}" {{ old('category_id', $recurringTransaction->category_id) == $category->id ? 'selected' : '' }}>
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
                                <input type="text" name="payee" id="payee" class="form-control" value="{{ old('payee', $recurringTransaction->payee) }}">
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
                                    <option value="cash" {{ old('payment_method', $recurringTransaction->payment_method) === 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="check" {{ old('payment_method', $recurringTransaction->payment_method) === 'check' ? 'selected' : '' }}>Check</option>
                                    <option value="credit_card" {{ old('payment_method', $recurringTransaction->payment_method) === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                    <option value="debit_card" {{ old('payment_method', $recurringTransaction->payment_method) === 'debit_card' ? 'selected' : '' }}>Debit Card</option>
                                    <option value="bank_transfer" {{ old('payment_method', $recurringTransaction->payment_method) === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="other" {{ old('payment_method', $recurringTransaction->payment_method) === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('payment_method')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes <span class="text-muted">(Optional)</span></label>
                        <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes', $recurringTransaction->notes) }}</textarea>
                        @error('notes')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Recurring Transaction
                        </button>
                        <a href="{{ route('drives.bookkeeper.recurring-transactions.show', [$drive, $recurringTransaction]) }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

