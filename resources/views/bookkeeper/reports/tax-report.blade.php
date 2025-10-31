@extends('layouts.dashboard')

@section('title', 'Tax Report - ' . $drive->name)

@push('styles')
<style>
    @media print {
        .no-print {
            display: none !important;
        }
        .report-content {
            padding: 0;
        }
        body {
            background: white;
        }
        .report-section {
            page-break-inside: avoid;
        }
        .report-table {
            page-break-inside: avoid;
        }
    }
    .report-content {
        background: white;
        color: black;
        padding: 2rem;
    }
    .report-header {
        border-bottom: 3px solid #333;
        padding-bottom: 1rem;
        margin-bottom: 2rem;
    }
    .report-section {
        margin-bottom: 2rem;
    }
    .report-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }
    .report-table th,
    .report-table td {
        padding: 0.75rem;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    .report-table th {
        background-color: #f5f5f5;
        font-weight: bold;
        border-bottom: 2px solid #333;
    }
    .report-table tr:hover {
        background-color: #f9f9f9;
    }
    .total-row {
        font-weight: bold;
        border-top: 2px solid #333;
        background-color: #f5f5f5;
    }
    .summary-box {
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 1rem;
        margin-bottom: 1rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4 no-print">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('drives.index') }}">Drives</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('drives.show', $drive) }}">{{ $drive->name }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('drives.bookkeeper.dashboard', $drive) }}">BookKeeper</a></li>
                            <li class="breadcrumb-item active">Tax Report</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">
                        <i class="fas fa-file-invoice-dollar me-2"></i>Tax Report
                    </h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <div class="d-flex gap-2">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fas fa-print me-2"></i>Print Report
                    </button>
                    <a href="{{ route('drives.bookkeeper.dashboard', $drive) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="row mb-4 no-print">
        <div class="col-12">
            <div class="dashboard-card">
                <h5 class="mb-3">Filter Report</h5>
                <form method="GET" action="{{ route('drives.bookkeeper.tax-report', $drive) }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from', $dateFrom->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to', $dateTo->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Account (Optional)</label>
                        <select name="account_id" class="form-select">
                            <option value="">All Accounts</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }} ({{ $account->account_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Category (Optional)</label>
                        <select name="category_id" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-2"></i>Generate Report
                        </button>
                        <a href="{{ route('drives.bookkeeper.tax-report', $drive) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Clear Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Report Content -->
    <div class="report-content">
        <!-- Report Header -->
        <div class="report-header">
            <h2 class="mb-2">{{ $drive->name }}</h2>
            <h3 class="mb-1">Tax Report</h3>
            <p class="mb-0">
                <strong>Period:</strong> {{ $dateFrom->format('F d, Y') }} to {{ $dateTo->format('F d, Y') }}
            </p>
            <p class="mb-0">
                <strong>Generated:</strong> {{ now()->format('F d, Y g:i A') }}
            </p>
        </div>

        <!-- Executive Summary -->
        <div class="report-section">
            <h4 class="mb-3">Executive Summary</h4>
            <div class="row">
                <div class="col-md-4">
                    <div class="summary-box">
                        <h5 class="text-success mb-2">Total Income</h5>
                        <h3 class="mb-0">${{ number_format($incomeTotal, 2) }}</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-box">
                        <h5 class="text-danger mb-2">Total Expenses</h5>
                        <h3 class="mb-0">${{ number_format($expensesTotal, 2) }}</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-box">
                        <h5 class="{{ $netIncome >= 0 ? 'text-success' : 'text-danger' }} mb-2">Net Income</h5>
                        <h3 class="mb-0">${{ number_format($netIncome, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Income by Category -->
        <div class="report-section">
            <h4 class="mb-3">Income by Category</h4>
            @if(count($incomeByCategory) > 0)
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th style="text-align: right;">Transaction Count</th>
                            <th style="text-align: right;">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($incomeByCategory as $categoryData)
                            <tr>
                                <td><strong>{{ $categoryData['category'] }}</strong></td>
                                <td style="text-align: right;">{{ $categoryData['count'] }}</td>
                                <td style="text-align: right;">${{ number_format($categoryData['total'], 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td><strong>TOTAL INCOME</strong></td>
                            <td style="text-align: right;"><strong>{{ count($incomeTransactions) }}</strong></td>
                            <td style="text-align: right;"><strong>${{ number_format($incomeTotal, 2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            @else
                <p class="text-muted">No income transactions found for this period.</p>
            @endif
        </div>

        <!-- Expenses by Category -->
        <div class="report-section">
            <h4 class="mb-3">Expenses by Category</h4>
            @if(count($expensesByCategory) > 0)
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th style="text-align: right;">Transaction Count</th>
                            <th style="text-align: right;">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expensesByCategory as $categoryData)
                            <tr>
                                <td><strong>{{ $categoryData['category'] }}</strong></td>
                                <td style="text-align: right;">{{ $categoryData['count'] }}</td>
                                <td style="text-align: right;">${{ number_format($categoryData['total'], 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td><strong>TOTAL EXPENSES</strong></td>
                            <td style="text-align: right;"><strong>{{ count($expenseTransactions) }}</strong></td>
                            <td style="text-align: right;"><strong>${{ number_format($expensesTotal, 2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            @else
                <p class="text-muted">No expense transactions found for this period.</p>
            @endif
        </div>

        <!-- Income by Account Type -->
        @if(count($incomeByAccountType) > 0)
            <div class="report-section">
                <h4 class="mb-3">Income by Account Type</h4>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Account Type</th>
                            <th style="text-align: right;">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($incomeByAccountType as $type => $amount)
                            <tr>
                                <td><strong>{{ ucfirst($type) }}</strong></td>
                                <td style="text-align: right;">${{ number_format($amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Expenses by Account Type -->
        @if(count($expensesByAccountType) > 0)
            <div class="report-section">
                <h4 class="mb-3">Expenses by Account Type</h4>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Account Type</th>
                            <th style="text-align: right;">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expensesByAccountType as $type => $amount)
                            <tr>
                                <td><strong>{{ ucfirst($type) }}</strong></td>
                                <td style="text-align: right;">${{ number_format($amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Detailed Transaction List -->
        <div class="report-section">
            <h4 class="mb-3">Detailed Transaction List</h4>
            @if($transactions->count() > 0)
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Transaction #</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Account</th>
                            <th>Category</th>
                            <th style="text-align: right;">Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->date->format('M d, Y') }}</td>
                                <td><code>{{ $transaction->transaction_number }}</code></td>
                                <td>
                                    <span class="badge bg-{{ $transaction->type === 'income' ? 'success' : ($transaction->type === 'expense' ? 'danger' : 'secondary') }}">
                                        {{ ucfirst($transaction->type) }}
                                    </span>
                                </td>
                                <td>{{ $transaction->description }}</td>
                                <td>{{ $transaction->account->name ?? 'N/A' }}</td>
                                <td>{{ $transaction->category->name ?? 'Uncategorized' }}</td>
                                <td style="text-align: right;" class="{{ $transaction->type === 'income' ? 'text-success' : 'text-danger' }}">
                                    <strong>{{ $transaction->type === 'income' ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}</strong>
                                </td>
                                <td>{{ ucfirst($transaction->status) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">No transactions found for this period.</p>
            @endif
        </div>

        <!-- Footer -->
        <div class="report-section" style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #ddd;">
            <p class="text-muted small mb-0">
                This report was generated on {{ now()->format('F d, Y g:i A') }} for {{ $drive->name }}.
                Use this report for tax filing purposes. Please review all transactions for accuracy.
            </p>
        </div>
    </div>
</div>
@endsection

