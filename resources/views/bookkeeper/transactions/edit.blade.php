@extends('layouts.dashboard')

@section('title', 'Edit Transaction - ' . $drive->name)

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Transaction</h4>
                    <a href="{{ route('drives.bookkeeper.transactions.show', [$drive, $transaction]) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>

                <form action="{{ route('drives.bookkeeper.transactions.update', [$drive, $transaction]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" id="date" class="form-control" value="{{ old('date', $transaction->date->format('Y-m-d')) }}" required>
                                @error('date')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    <option value="income" {{ old('type', $transaction->type) === 'income' ? 'selected' : '' }}>Income</option>
                                    <option value="expense" {{ old('type', $transaction->type) === 'expense' ? 'selected' : '' }}>Expense</option>
                                    <option value="transfer" {{ old('type', $transaction->type) === 'transfer' ? 'selected' : '' }}>Transfer</option>
                                    <option value="adjustment" {{ old('type', $transaction->type) === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                                </select>
                                @error('type')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" id="description" class="form-control" rows="2" required>{{ old('description', $transaction->description) }}</textarea>
                        @error('description')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="account_id" class="form-label">Account <span class="text-danger">*</span></label>
                                <select name="account_id" id="account_id" class="form-select" required>
                                    <option value="">Select Account</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" {{ old('account_id', $transaction->account_id) == $account->id ? 'selected' : '' }}>
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
                                        <option value="{{ $category->id }}" {{ old('category_id', $transaction->category_id) == $category->id ? 'selected' : '' }}>
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
                                <label for="budget_id" class="form-label">Budget <span class="text-muted">(Optional)</span></label>
                                <select name="budget_id" id="budget_id" class="form-select">
                                    <option value="">None</option>
                                    @foreach($budgets as $budget)
                                        <option value="{{ $budget->id }}" {{ old('budget_id', $transaction->budget_id) == $budget->id ? 'selected' : '' }}>
                                            {{ $budget->name }}
                                            @if($budget->category)
                                                ({{ $budget->category->name }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('budget_id')
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
                                    <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="0" value="{{ old('amount', $transaction->amount) }}" required>
                                </div>
                                @error('amount')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select" required>
                                    <option value="pending" {{ old('status', $transaction->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="cleared" {{ old('status', $transaction->status) === 'cleared' ? 'selected' : '' }}>Cleared</option>
                                    <option value="reconciled" {{ old('status', $transaction->status) === 'reconciled' ? 'selected' : '' }}>Reconciled</option>
                                </select>
                                @error('status')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payee" class="form-label">Payee <span class="text-muted">(Optional)</span></label>
                                <input type="text" name="payee" id="payee" class="form-control" value="{{ old('payee', $transaction->payee) }}">
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
                                    <option value="cash" {{ old('payment_method', $transaction->payment_method) === 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="check" {{ old('payment_method', $transaction->payment_method) === 'check' ? 'selected' : '' }}>Check</option>
                                    <option value="credit_card" {{ old('payment_method', $transaction->payment_method) === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                    <option value="debit_card" {{ old('payment_method', $transaction->payment_method) === 'debit_card' ? 'selected' : '' }}>Debit Card</option>
                                    <option value="bank_transfer" {{ old('payment_method', $transaction->payment_method) === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="other" {{ old('payment_method', $transaction->payment_method) === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('payment_method')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="reference" class="form-label">Reference <span class="text-muted">(Optional)</span></label>
                        <input type="text" name="reference" id="reference" class="form-control" value="{{ old('reference', $transaction->reference) }}">
                        @error('reference')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes <span class="text-muted">(Optional)</span></label>
                        <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes', $transaction->notes) }}</textarea>
                        @error('notes')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($transaction->attachments->count() > 0)
                        <div class="mb-3">
                            <label class="form-label">Current Attachments</label>
                            <div class="row">
                                @foreach($transaction->attachments as $attachment)
                                    <div class="col-md-4 mb-2">
                                        <div class="card">
                                            <div class="card-body p-2">
                                                @if(str_starts_with($attachment->mime_type, 'image/'))
                                                    <img src="{{ route('drives.bookkeeper.transactions.attachments.show', [$drive, $transaction, $attachment]) }}" alt="{{ $attachment->original_filename }}" class="img-thumbnail" style="max-height: 100px; width: 100%; object-fit: cover;">
                                                @else
                                                    <i class="fas fa-file fa-2x text-muted"></i>
                                                @endif
                                                <small class="d-block text-truncate" title="{{ $attachment->original_filename }}">{{ $attachment->original_filename }}</small>
                                                <button type="button" 
                                                    class="btn btn-sm btn-outline-danger mt-1" 
                                                    onclick="deleteAttachment({{ $attachment->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="attachments" class="form-label">Add Attachments <span class="text-muted">(Optional)</span></label>
                        <input type="file" name="attachments[]" id="attachments" class="form-control" multiple accept="image/*,.pdf,.doc,.docx">
                        <small class="text-muted">You can upload multiple images or documents (PDF, DOC, DOCX). Maximum file size: 10MB per file.</small>
                        @error('attachments.*')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Transaction
                        </button>
                        <a href="{{ route('drives.bookkeeper.transactions.show', [$drive, $transaction]) }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
                
                <!-- Hidden delete forms for attachments (one per attachment) -->
                @if($transaction->attachments->count() > 0)
                    @foreach($transaction->attachments as $attachment)
                        <form id="delete-attachment-{{ $attachment->id }}" 
                              action="{{ route('drives.bookkeeper.transactions.attachments.destroy', [$drive, $transaction, $attachment]) }}" 
                              method="POST" 
                              style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function deleteAttachment(attachmentId) {
    if (confirm('Are you sure you want to delete this attachment?')) {
        document.getElementById('delete-attachment-' + attachmentId).submit();
    }
}
</script>
@endpush
@endsection

