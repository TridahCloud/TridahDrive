<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hours Report - {{ $person->full_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: white;
        }
        .report-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .report-header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .report-header h2 {
            font-size: 20px;
            color: #666;
        }
        .report-info {
            margin-bottom: 30px;
        }
        .report-info p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th,
        table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .totals {
            margin-top: 20px;
            padding: 15px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
        }
        .totals h3 {
            margin-bottom: 10px;
        }
        .totals p {
            margin: 5px 0;
            font-size: 14px;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
        .print-actions {
            margin-bottom: 20px;
            text-align: right;
        }
        .print-actions button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .print-actions button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="print-actions no-print">
        <button onclick="window.print()">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>

    <div class="report-header">
        <h1>Hours Worked Report</h1>
        <h2>{{ $drive->name }}</h2>
    </div>

    <div class="report-info">
        <p><strong>Employee:</strong> {{ $person->full_name }}</p>
        <p><strong>Type:</strong> {{ ucfirst($person->type) }}</p>
        @if($person->employee_id)
            <p><strong>Employee ID:</strong> {{ $person->employee_id }}</p>
        @endif
        @if($person->job_title)
            <p><strong>Job Title:</strong> {{ $person->job_title }}</p>
        @endif
        <p><strong>Report Period:</strong> {{ $startDate->format('M d, Y') }} to {{ $endDate->format('M d, Y') }}</p>
        <p><strong>Generated:</strong> {{ now()->format('M d, Y h:i A') }}</p>
    </div>

    @if($timeLogs->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Clock In</th>
                    <th>Clock Out</th>
                    <th>Regular Hours</th>
                    <th>Overtime Hours</th>
                    <th>Total Hours</th>
                    <th>Status</th>
                    @if($totalPay > 0)
                        <th>Total Pay</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($timeLogs as $timeLog)
                    <tr>
                        <td>{{ $timeLog->work_date->format('M d, Y') }}</td>
                        <td>{{ $timeLog->clock_in ? $timeLog->clock_in->format('h:i A') : '-' }}</td>
                        <td>{{ $timeLog->clock_out ? $timeLog->clock_out->format('h:i A') : '-' }}</td>
                        <td>{{ number_format($timeLog->regular_hours ?? 0, 2) }}</td>
                        <td>{{ number_format($timeLog->overtime_hours ?? 0, 2) }}</td>
                        <td>{{ number_format($timeLog->total_hours ?? 0, 2) }}</td>
                        <td>{{ ucfirst($timeLog->status) }}</td>
                        @if($totalPay > 0)
                            <td>{{ $timeLog->total_pay ? currency_for($timeLog->total_pay, $drive) : '-' }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <h3>Summary</h3>
            <p><strong>Total Regular Hours:</strong> {{ number_format($totalRegularHours, 2) }}</p>
            <p><strong>Total Overtime Hours:</strong> {{ number_format($totalOvertimeHours, 2) }}</p>
            <p><strong>Total Hours:</strong> {{ number_format($totalHours, 2) }}</p>
            @if($totalPay > 0)
                <p><strong>Total Pay:</strong> {{ currency_for($totalPay, $drive) }}</p>
            @endif
            <p><strong>Number of Entries:</strong> {{ $timeLogs->count() }}</p>
        </div>
    @else
        <div class="no-data">
            <p>No time logs found for this period.</p>
        </div>
    @endif
</body>
</html>

