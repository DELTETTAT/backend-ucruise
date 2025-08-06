{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Request</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
            font-family: 'Poppins', sans-serif;
            color: #333;
            line-height: 1.6;
        }
        
        .email-container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        .header {
            background: linear-gradient(135deg, #50d0f7ff 0%, #4b62a2ff 100%);
            padding: 25px 30px;
            text-align: center;
            color: white;
        }
        
        .header h1 {
            margin: 0;
            font-weight: 600;
            font-size: 24px;
        }
        
        .content {
            padding: 30px;
        }
        
        .status-card {
            background: #f8f9fa;
            border-left: 4px solid #4CAF50;
            padding: 20px;
            border-radius: 0 8px 8px 0;
            margin: 20px 0;
            text-align: center;
        }
        
        .status-card h2 {
            margin: 0;
            color: #2c3e50;
            font-weight: 500;
        }
        
        .status-card.rejected {
            border-left-color: #e74c3c;
        }
        
        .form-card {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .form-card h3 {
            margin-top: 0;
            color: #2c3e50;
            font-weight: 500;
        }
        
        textarea {
            width: 100%;
            min-height: 120px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            margin-bottom: 15px;
            resize: vertical;
            transition: border 0.3s;
        }
        
        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
        }
        
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .button.reject {
            background: linear-gradient(135deg, #ff6b6b 0%, #f53b3b 100%);
        }
        
        .button.approve {
            background: linear-gradient(135deg, #48dbfb 0%, #0abde3 100%);
        }
        
        .leave-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .leave-details p {
            margin: 8px 0;
        }
        
        .detail-label {
            font-weight: 500;
            color: #555;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            color: #777;
            font-size: 14px;
            border-top: 1px solid #eee;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
            justify-content: center;
        }
        
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        
        .signature {
            margin-top: 30px;
            font-style: italic;
            color: #555;
        }
        
        .signature-name {
            font-weight: 500;
            font-style: normal;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="email-container"> 
        <div class="header">
            <h1>Leave Request Notification</h1>
        </div>
        
        <div class="content">
            @if($statusMessage)
                <div class="status-card {{ strpos(strtolower($statusMessage), 'rejected') !== false ? 'rejected' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="{{ strpos(strtolower($statusMessage), 'rejected') !== false ? '#e74c3c' : '#4CAF50' }}" style="margin-bottom: 15px;">
                        <path d="{{ strpos(strtolower($statusMessage), 'rejected') !== false ? 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z' : 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z' }}"/>
                    </svg>
                    <h2>{{ $statusMessage }}</h2>
                </div>
            @elseif(!empty($dataform) && isset($dataform['leave_id']))
                <div class="form-card">
                    <h3>Reject Leave Request</h3>
                    <p>Please provide a reason for rejecting this leave request:</p>
                    <form action="{{ url('/') }}/api/leaveRejected" method="post">
                        @csrf
                        <input type="hidden" name="db_name" value="{{ $dataform['db_name'] }}">
                        <input type="hidden" name="employee_id" value="{{ $dataform['employee_id'] }}">
                        <input type="hidden" name="leave_id" value="{{ $dataform['leave_id'] }}">
                        <textarea name="reason" placeholder="Enter your reason here..." required></textarea>
                        <button type="submit" class="button reject">Submit Rejection</button>
                    </form>
                </div>
            {{-- @elseif(!empty($emailData))
                <div class="greeting">Hello <strong>Sir/Ma'am</strong>,</div>
                
                <div class="leave-details">
                    <p><span class="detail-label">Request Type:</span> Leave Application</p>
                    @if(!empty($emailData['start_date']))
                        <p><span class="detail-label">From:</span> {{ $emailData['start_date'] }}</p>
                    @endif
                    @if(!empty($emailData['end_date']))
                        <p><span class="detail-label">To:</span> {{ $emailData['end_date'] }}</p>
                    @endif
                    @if(!empty($emailData['type']))
                        <p><span class="detail-label">Leave Type:</span> {{ $emailData['type'] }}</p>
                    @endif
                    @if(!empty($emailData['reason']))
                        <p><span class="detail-label">Reason:</span> {{ $emailData['reason'] }}</p>
                    @endif
                </div> --}}
                {{-- <p>I hope this message finds you well.</p>
                <p>I will ensure that my responsibilities are handled efficiently before my leave and will coordinate with the team as needed.</p>
                
                <p>Kindly consider and approve my request.</p>
                
                <div class="signature">
                    <p>Thank you for your support.</p>
                    <p>Best regards,</p>
                    <p class="signature-name">{{ $emailData['user_name'] }}</p>
                </div> --}}
                {{-- @if(!empty($emailData))
                    <div class="greeting">Hello <strong>Sir/Ma'am</strong>,</div>

                    <p>
                        I am writing to request leave from 
                        <strong>{{ \Carbon\Carbon::parse($emailData['start_date'])->format('Y-m-d') }}</strong>
                        @if(!empty($emailData['end_date']) && $emailData['start_date'] != $emailData['end_date'])
                            to <strong>{{ \Carbon\Carbon::parse($emailData['end_date'])->format('Y-m-d') }}</strong>
                        @endif
                        @if(!empty($emailData['type']))
                            due to <strong>{{ $emailData['type'] }}</strong>.
                        @else
                            .
                        @endif
                    </p>
                @endif
                @if(!empty($custom_content))
                    <div class="custom-email-content">
                        {!! $custom_content !!}
                    </div>
                @else
                    <p>I hope this message finds you well.</p>
                    <p>I will ensure that my responsibilities are handled efficiently before my leave and will coordinate with the team as needed.</p>
                    <p>Kindly consider and approve my request.</p>
                @endif

                @if(isset($emailData['action_buttons']))
                    <div class="action-buttons">
                        {!! str_replace(['<a ', '</a>'], ['<a class="button approve" ', '</a>'], 
                           str_replace('button reject', 'button reject', $emailData['action_buttons'])) !!}
                    </div>
                @endif
            @endif
        </div>
        
        <div class="footer">
            {{ env('APP_NAME') }} &copy; {{ date('Y') }} | All Rights Reserved
        </div>
    </div>
</body>
</html> --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Request</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
            font-family: 'Poppins', sans-serif;
            color: #333;
            line-height: 1.6;
        }
        .email-container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        .header {
            background: linear-gradient(135deg, #50d0f7ff 0%, #4b62a2ff 100%);
            padding: 25px 30px;
            text-align: center;
            color: white;
        }
        .header h1 {
            margin: 0;
            font-weight: 600;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .status-card {
            background: #f8f9fa;
            border-left: 4px solid #4CAF50;
            padding: 20px;
            border-radius: 0 8px 8px 0;
            margin: 20px 0;
            text-align: center;
        }
        .status-card h2 {
            margin: 0;
            color: #2c3e50;
            font-weight: 500;
        }
        .status-card.rejected {
            border-left-color: #e74c3c;
        }
        .form-card {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .form-card h3 {
            margin-top: 0;
            color: #2c3e50;
            font-weight: 500;
        }
        textarea {
            width: 100%;
            min-height: 120px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            margin-bottom: 15px;
            resize: vertical;
            transition: border 0.3s;
        }
        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        .button.reject {
            background: linear-gradient(135deg, #ff6b6b 0%, #f53b3b 100%);
        }
        .button.approve {
            background: linear-gradient(135deg, #48dbfb 0%, #0abde3 100%);
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #777;
            font-size: 14px;
            border-top: 1px solid #eee;
        }
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
            justify-content: center;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .signature {
            margin-top: 30px;
            font-style: italic;
            color: #555;
        }
        .signature-name {
            font-weight: 500;
            font-style: normal;
            color: #2c3e50;
        }
    </style>
</head>
<body>
<div class="email-container">

    <div class="content">
        @if($statusMessage)
            <div class="status-card {{ strpos(strtolower($statusMessage), 'rejected') !== false ? 'rejected' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="{{ strpos(strtolower($statusMessage), 'rejected') !== false ? '#e74c3c' : '#4CAF50' }}" style="margin-bottom: 15px;">
                    <path d="{{ strpos(strtolower($statusMessage), 'rejected') !== false ? 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z' : 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z' }}"/>
                </svg>
                <h2>{{ $statusMessage }}</h2>
            </div>
        @elseif(!empty($dataform) && isset($dataform['leave_id']))
            <div class="form-card">
                <h3>Reject Leave Request</h3>
                <p>Please provide a reason for rejecting this leave request:</p>
                <form action="{{ url('/') }}/api/leaveRejected" method="post">
                    @csrf
                    <input type="hidden" name="db_name" value="{{ $dataform['db_name'] }}">
                    <input type="hidden" name="employee_id" value="{{ $dataform['employee_id'] }}">
                    <input type="hidden" name="leave_id" value="{{ $dataform['leave_id'] }}">
                    <textarea name="reason" placeholder="Enter your reason here..." required></textarea>
                    <button type="submit" class="button reject">Submit Rejection</button>
                </form>
            </div>
        @elseif(!empty($emailData))
            <div class="greeting">Hello <strong>Sir/Ma'am</strong>,</div>

            <p>
                I am writing to request leave from
                <strong>{{ \Carbon\Carbon::parse($emailData['start_date'])->format('Y-m-d') }}</strong>
                @if(!empty($emailData['end_date']) && $emailData['start_date'] != $emailData['end_date'])
                    to <strong>{{ \Carbon\Carbon::parse($emailData['end_date'])->format('Y-m-d') }}</strong>
                @endif
                @if(!empty($emailData['type']))
                    due to <strong>{{ $emailData['type'] }}</strong>.
                @else
                    .
                @endif
            </p>

            @if(!empty($custom_content))
                <div class="custom-email-content">
                    {!! $custom_content !!}
                </div>
            @else
                <p>I hope this message finds you well.</p>
                <p>I will ensure that my responsibilities are handled efficiently before my leave and will coordinate with the team as needed.</p>
                <p>Kindly consider and approve my request.</p>
            @endif

            <div class="signature">
                <p>Thank you for your support.</p>
                <p>Best regards,</p>
                <p class="signature-name">{{ $emailData['user_name'] }}</p>
            </div>

            @if(isset($emailData['action_buttons']))
                <div class="action-buttons">
                    {!! str_replace(['<a ', '</a>'], ['<a class="button approve" ', '</a>'],
                       str_replace('button reject', 'button reject', $emailData['action_buttons'])) !!}
                </div>
            @endif
        @endif
    </div>

    <div class="footer">
        {{ env('APP_NAME') }} &copy; {{ date('Y') }} | All Rights Reserved
    </div>
</div>
</body>
</html>