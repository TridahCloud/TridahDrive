@extends('layouts.dashboard')

@section('title', 'Edit Budget - ' . $drive->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Budget</h4>
                    <a href="{{ route('drives.bookkeeper.budgets.index', $drive) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>

                <form action="{{ route('drives.bookkeeper.budgets.update', [$drive, $budget]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Budget Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $budget->name) }}" required>
                        @error('name')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span class="text-muted">(Optional)</span></label>
                        <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $budget->description) }}</textarea>
                        @error('description')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category <span class="text-muted">(Optional)</span></label>
                        <select name="category_id" id="category_id" class="form-select">
                            <option value="">No Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $budget->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="period_type" class="form-label">Period Type <span class="text-danger">*</span></label>
                            <select name="period_type" id="period_type" class="form-select" required>
                                <option value="monthly" {{ old('period_type', $budget->period_type) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="quarterly" {{ old('period_type', $budget->period_type) == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                <option value="yearly" {{ old('period_type', $budget->period_type) == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                <option value="custom" {{ old('period_type', $budget->period_type) == 'custom' ? 'selected' : '' }}>Custom</option>
                            </select>
                            @error('period_type')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">Budget Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $drive->currency ?? '$' }}</span>
                                <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="0" value="{{ old('amount', $budget->amount) }}" required>
                            </div>
                            @error('amount')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date', $budget->start_date->format('Y-m-d')) }}" required>
                            @error('start_date')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">End Date <span class="text-muted">(Optional)</span></label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date', $budget->end_date ? $budget->end_date->format('Y-m-d') : '') }}">
                            @error('end_date')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" id="is_active" class="form-check-input" {{ old('is_active', $budget->is_active) ? 'checked' : '' }}>
                            <label for="is_active" class="form-check-label">Active</label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Budget
                        </button>
                        <a href="{{ route('drives.bookkeeper.budgets.index', $drive) }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

