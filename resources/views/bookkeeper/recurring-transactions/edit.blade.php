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

                    <!-- Advanced Recurrence Options -->
                    <div class="mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="mb-3">
                                    <i class="fas fa-cog me-2"></i>Advanced Recurrence Options
                                    <small class="text-muted">(Optional - for more control)</small>
                                </h6>
                                
                                <!-- Frequency Interval -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="frequency_interval" class="form-label">Repeat Every</label>
                                        <div class="input-group">
                                            <input type="number" name="frequency_interval" id="frequency_interval" class="form-control" value="{{ old('frequency_interval', $recurringTransaction->frequency_interval ?? 1) }}" min="1" max="365">
                                            <span class="input-group-text" id="interval-unit">time(s)</span>
                                        </div>
                                        <small class="text-muted">Leave as 1 for standard recurrence (every day/week/month/year)</small>
                                        @error('frequency_interval')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Day of Week (for weekly and monthly) -->
                                <div class="row mb-3" id="day-of-week-section" style="display: none;">
                                    <div class="col-md-6">
                                        <label for="frequency_day_of_week" class="form-label">Day of Week</label>
                                        <select name="frequency_day_of_week" id="frequency_day_of_week" class="form-select">
                                            <option value="">Any Day</option>
                                            <option value="0" {{ old('frequency_day_of_week', $recurringTransaction->frequency_day_of_week) == '0' ? 'selected' : '' }}>Sunday</option>
                                            <option value="1" {{ old('frequency_day_of_week', $recurringTransaction->frequency_day_of_week) == '1' ? 'selected' : '' }}>Monday</option>
                                            <option value="2" {{ old('frequency_day_of_week', $recurringTransaction->frequency_day_of_week) == '2' ? 'selected' : '' }}>Tuesday</option>
                                            <option value="3" {{ old('frequency_day_of_week', $recurringTransaction->frequency_day_of_week) == '3' ? 'selected' : '' }}>Wednesday</option>
                                            <option value="4" {{ old('frequency_day_of_week', $recurringTransaction->frequency_day_of_week) == '4' ? 'selected' : '' }}>Thursday</option>
                                            <option value="5" {{ old('frequency_day_of_week', $recurringTransaction->frequency_day_of_week) == '5' ? 'selected' : '' }}>Friday</option>
                                            <option value="6" {{ old('frequency_day_of_week', $recurringTransaction->frequency_day_of_week) == '6' ? 'selected' : '' }}>Saturday</option>
                                        </select>
                                        <small class="text-muted">e.g., Every Monday, or First Friday of month</small>
                                        @error('frequency_day_of_week')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Day of Month (for monthly) -->
                                <div class="row mb-3" id="day-of-month-section" style="display: none;">
                                    <div class="col-md-6">
                                        <label for="frequency_day_of_month" class="form-label">Day of Month</label>
                                        <input type="number" name="frequency_day_of_month" id="frequency_day_of_month" class="form-control" value="{{ old('frequency_day_of_month', $recurringTransaction->frequency_day_of_month) }}" min="1" max="31">
                                        <small class="text-muted">e.g., 15 = 15th of every month (1-31)</small>
                                        @error('frequency_day_of_month')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Week of Month (for monthly with weekday) -->
                                <div class="row mb-3" id="week-of-month-section" style="display: none;">
                                    <div class="col-md-6">
                                        <label for="frequency_week_of_month" class="form-label">Week of Month</label>
                                        <select name="frequency_week_of_month" id="frequency_week_of_month" class="form-select">
                                            <option value="">Any Week</option>
                                            <option value="1" {{ old('frequency_week_of_month', $recurringTransaction->frequency_week_of_month) == '1' ? 'selected' : '' }}>First</option>
                                            <option value="2" {{ old('frequency_week_of_month', $recurringTransaction->frequency_week_of_month) == '2' ? 'selected' : '' }}>Second</option>
                                            <option value="3" {{ old('frequency_week_of_month', $recurringTransaction->frequency_week_of_month) == '3' ? 'selected' : '' }}>Third</option>
                                            <option value="4" {{ old('frequency_week_of_month', $recurringTransaction->frequency_week_of_month) == '4' ? 'selected' : '' }}>Fourth</option>
                                            <option value="5" {{ old('frequency_week_of_month', $recurringTransaction->frequency_week_of_month) == '5' ? 'selected' : '' }}>Last</option>
                                        </select>
                                        <small class="text-muted">e.g., First Monday, Second Friday, Last Wednesday</small>
                                        @error('frequency_week_of_month')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
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
                                    <span class="input-group-text">{{ currency_code_for($drive) ? \App\Helpers\CurrencyHelper::getSymbol(currency_code_for($drive)) : '$' }}</span>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const frequencySelect = document.getElementById('frequency');
    const intervalInput = document.getElementById('frequency_interval');
    const intervalUnit = document.getElementById('interval-unit');
    const dayOfWeekSection = document.getElementById('day-of-week-section');
    const dayOfMonthSection = document.getElementById('day-of-month-section');
    const weekOfMonthSection = document.getElementById('week-of-month-section');
    const frequencyDayOfWeek = document.getElementById('frequency_day_of_week');
    const frequencyDayOfMonth = document.getElementById('frequency_day_of_month');
    const frequencyWeekOfMonth = document.getElementById('frequency_week_of_month');

    function updateRecurrenceUI() {
        const frequency = frequencySelect.value;
        
        // Update interval unit text
        if (frequency) {
            const units = {
                'daily': 'day(s)',
                'weekly': 'week(s)',
                'monthly': 'month(s)',
                'yearly': 'year(s)'
            };
            intervalUnit.textContent = units[frequency] || 'time(s)';
        }

        // Show/hide day of week section (for weekly and monthly)
        if (frequency === 'weekly' || frequency === 'monthly') {
            dayOfWeekSection.style.display = 'block';
        } else {
            dayOfWeekSection.style.display = 'none';
            // Don't clear value if it was set before, just hide the section
        }

        // Show/hide day of month section (for monthly)
        if (frequency === 'monthly') {
            dayOfMonthSection.style.display = 'block';
        } else {
            dayOfMonthSection.style.display = 'none';
            // Don't clear value if it was set before, just hide the section
        }

        // Show/hide week of month section (for monthly with day of week)
        if (frequency === 'monthly' && frequencyDayOfWeek.value !== '') {
            weekOfMonthSection.style.display = 'block';
        } else {
            weekOfMonthSection.style.display = 'none';
            // Don't clear value if it was set before, just hide the section
        }
    }

    frequencySelect.addEventListener('change', updateRecurrenceUI);
    frequencyDayOfWeek.addEventListener('change', function() {
        const frequency = frequencySelect.value;
        if (frequency === 'monthly' && this.value !== '') {
            weekOfMonthSection.style.display = 'block';
        } else {
            weekOfMonthSection.style.display = 'none';
            if (!frequencyWeekOfMonth.value) {
                frequencyWeekOfMonth.value = '';
            }
        }
    });

    // Initialize on page load
    updateRecurrenceUI();
});
</script>
@endpush
@endsection

