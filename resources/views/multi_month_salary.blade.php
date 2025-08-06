<!DOCTYPE html>
<html>
<head>
    <title>Salary Slip</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header img {
            height: 60px;
            margin-bottom: 5px;
        }
        .header h2 {
            margin: 5px 0 2px 0;
        }
        .header p {
            margin: 2px 0;
            font-size: 11px;
        }
        .section-title {
            background-color: #f2f2f2;
            font-weight: bold;
            padding: 5px;
            margin-top: 15px;
            border: 1px solid #ccc;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        td, th {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: left;
        }
        .totals {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 11px;
        }
        .signature {
            margin-top: 40px;
            text-align: right;
            font-weight: bold;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    @php
        // Method 1: Try direct file access first
        $logoPath = public_path('assets/images/unify.png');
        $logoContent = file_exists($logoPath) ? file_get_contents($logoPath) : null;
        $logoBase64 = $logoContent 
            ? 'data:image/png;base64,'.base64_encode($logoContent)
            : 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="200" height="80" viewBox="0 0 200 80"><rect width="200" height="80" fill="#3498db"/><text x="100" y="45" font-family="Arial" font-size="20" fill="white" text-anchor="middle">UNIFY LOGISTIC</text></svg>');
    @endphp

    @foreach($salary_records as $record)
    <div class="header">
        <img src="{{ $logoBase64 }}" alt="Company Logo">
        <h2>{{ $company_name }}</h2>
        <p>Official Salary Statement</p>
        <p>{{ $record['payout_date'] }}</p>
    </div>

    <div class="section-title">Employee Details</div>
    <table>
        <tr>
            <td><strong>Name:</strong> {{ $employee['first_name'] }} {{ $employee['last_name'] }}</td>
            <td><strong>Employee Code:</strong> {{ $employee['unique_id'] }}</td>
        </tr>
        <tr>
            <td><strong>Gender:</strong> {{ $employee['gender'] }}</td>
            <td><strong>Marital Status:</strong> {{ $employee['marital_status'] }}</td>
        </tr>
        <tr>
            <td><strong>Email:</strong> {{ $employee['email'] }}</td>
            <td><strong>Phone:</strong> {{ $employee['phone'] }}</td>
        </tr>
        <tr>
            <td><strong>Date of Birth:</strong> {{ $employee['dob'] }}</td>
            <td><strong>Date of Joining:</strong> {{ $employee['doj'] }}</td>
        </tr>
    </table>

    <div class="section-title">Attendance Summary</div>
    <table>
        <tr>
            <td><strong>Month Days:</strong> {{ $record['attendance_summary']['total_days'] }}</td>
            <td><strong>Total Working Days:</strong> {{ $record['attendance_summary']['working_days'] }}</td>
            <td><strong>Absent Days:</strong> {{ $record['total_leave'] }}</td>
           
        </tr>
    </table>

    <div class="section-title">Salary Details (INR)</div>
    <table>
        <tr>
            <th>Salary Component</th>
            <th>Amount (₹)</th>
            <th>Deductions (₹)</th>
        </tr>
        <tr>
            <td>Basic Salary</td>
            <td>{{ $record['salaryRecord']['basic'] }}</td>
            <td></td>
        </tr>
        <tr>
            <td>House Rent Allowance (HRA)</td>
            <td>{{ $record['salaryRecord']['hra'] }}</td>
            <td></td>
        </tr>
        <tr>
            <td>Medical Allowance</td>
            <td>{{ $record['salaryRecord']['medical'] }}</td>
            <td></td>
        </tr>
        <tr>
            <td>Conveyance Allowance</td>
            <td>{{ $record['salaryRecord']['conveyance'] }}</td>
            <td></td>
        </tr>
        <tr>
            <td>Bonus</td>
            <td>{{ $record['salaryRecord']['bonus'] }}</td>
            <td></td>
        </tr>
        <tr>
            <td class="totals">Gross Salary</td>
            <td class="totals">{{ $record['gross_pay'] }}</td>
            <td></td>
        </tr>
        <tr>
            <td>EPF Contribution</td>
            <td></td>
            <td>{{ $record['emppf'] }}</td>
        </tr>
        <tr>
            <td>Professional Tax</td>
            <td></td>
            <td>{{ $record['salaryRecord']['professional_tax'] }}</td>
        </tr>
        <tr>
            <td>Leave Deduction</td>
            <td></td>
            <td>{{ $record['leavededuction'] }}</td>
        </tr>
        <tr>
            <td class="totals">Total Deductions</td>
            <td></td>
            <td class="totals">{{ $record['deduction'] }}</td>
        </tr>
        <tr>
            <td class="totals">Net Paid Salary</td>
            <td class="totals" colspan="2">{{ $record['total_pay'] }}</td>
        </tr>
    </table>

    <div class="signature">
        Authorized Signatory<br>
        {{ $company_name }}
    </div>

    <div class="footer">
        NET PAY Via Bank: ₹{{ $record['total_pay'] }}<br>
        This is an electronically generated document and does not require signature.
    </div>

    @if(!$loop->last)
    <div class="page-break"></div>
    @endif
    @endforeach
</body>
</html>