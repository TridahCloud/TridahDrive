@extends('layouts.dashboard')

@section('title', 'Create Payroll Entry - ' . $drive->name)

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
                            <li class="breadcrumb-item"><a href="{{ route('drives.people-manager.payroll.index', $drive) }}">Payroll</a></li>
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">Create Payroll Entry</h1>
                </div>
                <a href="{{ route('drives.people-manager.payroll.index', $drive) }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>
    </div>

    <form action="{{ route('drives.people-manager.payroll.store', $drive) }}" method="POST">
        @csrf
        <div class="dashboard-card mb-4">
            <div class="mb-3">
                <label for="person_id" class="form-label">Person *</label>
                <select class="form-select" id="person_id" name="person_id" required>
                    @foreach($people as $person)
                        <option value="{{ $person->id }}">{{ $person->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="pay_date" class="form-label">Pay Date *</label>
                <input type="date" class="form-control" id="pay_date" name="pay_date" value="{{ old('pay_date') }}" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="period_start_date" class="form-label">Period Start *</label>
                    <input type="date" class="form-control" id="period_start_date" name="period_start_date" value="{{ old('period_start_date') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="period_end_date" class="form-label">Period End *</label>
                    <input type="date" class="form-control" id="period_end_date" name="period_end_date" value="{{ old('period_end_date') }}" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="net_pay" class="form-label">Net Pay</label>
                <input type="number" step="0.01" class="form-control" id="net_pay" name="net_pay" value="{{ old('net_pay') }}">
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="paid" {{ old('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('drives.people-manager.payroll.index', $drive) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Payroll Entry</button>
            </div>
        </div>
    </form>
</div>
@endsection

