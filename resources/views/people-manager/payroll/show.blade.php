@extends('layouts.dashboard')

@section('title', 'Payroll Entry - ' . $drive->name)

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
                            <li class="breadcrumb-item active">View</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">Payroll Entry</h1>
                </div>
                <div>
                    <a href="{{ route('drives.people-manager.payroll.index', $drive) }}" class="btn btn-outline-secondary">Back</a>
                    @if($drive->canEdit(auth()->user()))
                        <a href="{{ route('drives.people-manager.payroll.edit', [$drive, $payrollEntry]) }}" class="btn btn-outline-primary ms-2">Edit</a>
                        @if(!$payrollEntry->synced_to_bookkeeper)
                            <form action="{{ route('drives.people-manager.payroll.sync', [$drive, $payrollEntry]) }}" method="POST" class="d-inline ms-2">
                                @csrf
                                <button type="submit" class="btn btn-info" title="Sync to BookKeeper">
                                    <i class="fas fa-sync"></i> Sync
                                </button>
                            </form>
                        @else
                            <form action="{{ route('drives.people-manager.payroll.sync', [$drive, $payrollEntry]) }}" method="POST" class="d-inline ms-2">
                                @csrf
                                <button type="submit" class="btn btn-warning" title="Re-sync to update BookKeeper transaction">
                                    <i class="fas fa-sync-alt"></i> Re-sync
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <h4>{{ $payrollEntry->person->full_name ?? 'Unknown' }}</h4>
        <p><strong>Pay Date:</strong> {{ $payrollEntry->pay_date->format('M d, Y') }}</p>
        @if($payrollEntry->payroll_period)
            <p><strong>Period:</strong> {{ $payrollEntry->payroll_period }}</p>
        @endif
        @if($payrollEntry->net_pay)
            <p><strong>Net Pay:</strong> {{ currency_for($payrollEntry->net_pay, $drive) }}</p>
        @endif
        <p><strong>Status:</strong> {{ ucfirst($payrollEntry->status) }}</p>
        @if($payrollEntry->synced_to_bookkeeper)
            <span class="badge bg-success">Synced to BookKeeper</span>
        @endif
    </div>
</div>
@endsection

