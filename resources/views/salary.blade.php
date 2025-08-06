<!DOCTYPE html>
<html>
<head>
    <title>Salary Slip - {{ $period }}</title>
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
    <div class="header">
        <img src="{{ $logoBase64 }}" alt="Company Logo"> <!-- Replace with your logo -->
        <h2>{{ $company_name }}</h2>
        <p>Official Salary Statement</p>
        <p>{{ $period }}</p>
    </div>

    <div class="section-title">Employee Details</div>
    <table>
        <tr>
            <td><strong>Name:</strong> {{ $employee_name }}</td>
            <td><strong>Employee Code:</strong> {{ $emp_id }}</td>
        </tr>
        <tr>
            <td><strong>Department:</strong> {{ $department }}</td>
            <td><strong>Designation:</strong> {{ $designation }}</td>
        </tr>
        <tr>
            <td><strong>Email:</strong> {{ $email }}</td>
            <td><strong>Phone:</strong> {{ $phone }}</td>
        </tr>
        <tr>
            <td><strong>Date of Birth:</strong> {{ $dob }}</td>
            <td><strong>Transaction ID:</strong> {{ $transaction_id }}</td>
        </tr>
    </table>

    <div class="section-title">Attendance Summary</div>
    <table>
        <tr>
            <td><strong>Month Days:</strong> {{ $total_days }}</td>
            <td><strong>Total Working Days:</strong> {{ $days_worked }}</td>
            <td><strong>Absent Days:</strong> {{ $total_leaves }}</td>
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
            <td>{{ $basic_salary }}</td>
            <td></td>
        </tr>
        <tr>
            <td>House Rent Allowance (HRA)</td>
            <td>{{ $hra }}</td>
            <td></td>
        </tr>
        <tr>
            <td>Conveyance Allowance</td>
            <td>{{ $conveyance }}</td>
            <td></td>
        </tr>
        <tr>
            <td>Medical Allowance</td>
            <td>{{ $medical }}</td>
            <td></td>
        </tr>
        <tr>
            <td>Special Allowance / Bonus</td>
            <td>{{ $special_allowance }}</td>
            <td></td>
        </tr>
        <tr>
            <td class="totals">Gross Salary</td>
            <td class="totals">{{ $gross_salary }}</td>
            <td></td>
        </tr>
        <tr>
            <td>EPF Contribution</td>
            <td></td>
            <td>{{ $epf }}</td>
        </tr>
        <tr>
            <td>Professional Tax</td>
            <td></td>
            <td>{{ $professional_tax }}</td>
        </tr>
      
        <tr>
            <td>Leave Decution</td>
            <td></td>
            <td>{{ $leavededuction }}</td>
        </tr>
        <tr>
            <td class="totals">Total Deductions</td>
            <td></td>
            <td class="totals">{{ $total_deductions }}</td>
        </tr>
        <tr>
            <td class="totals">Net Paid Salary</td>
            <td class="totals" colspan="2">{{ $net_pay }}</td>
        </tr>
    </table>

    <div class="signature">
        Authorized Signatory<br>
        {{ $company_name }}
    </div>

    <div class="footer">
        NET PAY Via Bank: ₹{{ $net_pay }} ({{ $amount_in_words }} only)<br>
        Generated on: {{ $generation_date }}<br>
        This is an electronically generated document and does not require signature.
    </div>
</body>
</html>
