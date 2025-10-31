@extends('layouts.dashboard')

@section('title', 'Edit Account - ' . $drive->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Account</h4>
                    <a href="{{ route('drives.bookkeeper.accounts.index', $drive) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>

                <form action="{{ route('drives.bookkeeper.accounts.update', [$drive, $account]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="parent_id" class="form-label">Parent Account <span class="text-muted">(Optional)</span></label>
                        <select name="parent_id" id="parent_id" class="form-select">
                            <option value="">None (Top Level)</option>
                            @foreach($parentAccounts as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id', $account->parent_id) == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->account_code }} - {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="account_code" class="form-label">Account Code <span class="text-danger">*</span></label>
                                <input type="text" name="account_code" id="account_code" class="form-control" value="{{ old('account_code', $account->account_code) }}" required>
                                @error('account_code')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type" class="form-label">Account Type <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    <option value="asset" {{ old('type', $account->type) === 'asset' ? 'selected' : '' }}>Asset</option>
                                    <option value="liability" {{ old('type', $account->type) === 'liability' ? 'selected' : '' }}>Liability</option>
                                    <option value="equity" {{ old('type', $account->type) === 'equity' ? 'selected' : '' }}>Equity</option>
                                    <option value="revenue" {{ old('type', $account->type) === 'revenue' ? 'selected' : '' }}>Revenue</option>
                                    <option value="expense" {{ old('type', $account->type) === 'expense' ? 'selected' : '' }}>Expense</option>
                                </select>
                                @error('type')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Account Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $account->name) }}" required>
                        @error('name')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="subtype" class="form-label">Subtype <span class="text-muted">(Optional)</span></label>
                        <input type="text" name="subtype" id="subtype" class="form-control" value="{{ old('subtype', $account->subtype) }}" placeholder="e.g., Current Assets, Long-term Liabilities">
                        @error('subtype')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span class="text-muted">(Optional)</span></label>
                        <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $account->description) }}</textarea>
                        @error('description')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" id="is_active" class="form-check-input" {{ old('is_active', $account->is_active) ? 'checked' : '' }}>
                            <label for="is_active" class="form-check-label">Active</label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Account
                        </button>
                        <a href="{{ route('drives.bookkeeper.accounts.index', $drive) }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

