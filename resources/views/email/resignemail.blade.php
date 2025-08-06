<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="x-apple-disable-message-reformatting">
  <title>Resignation Request</title>
  <style>
    table, td, div, h1, p {font-family: Arial, sans-serif;}
  </style>
</head>
<body style="margin:0;padding:0;">
  <table role="presentation" style="width:100%;background:#ffffff;">
    <tr>
      <td align="center" style="padding:0;">
        <table role="presentation" style="width:602px;text-align:left;">
         
          <tr>
            <td style="padding:36px 30px 42px 30px;">
              <table role="presentation" style="width:100%;">
                <tr>
                  <td style="padding:0 0 36px 0;color:#153643;">

                     <div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow-lg">
                            <p class="mb-4">Dear <span class="font-semibold text-gray-800">Sir/Ma'am</span>,</p>

                            <p class="mb-4">
                                I hope this message finds you well.
                            </p>

                            <p class="mb-4">
                                I am writing to formally submit my resignation from my position at Unify Tech solutions. 
                                My last working day will be <span class="font-medium text-gray-900">{{ $emailData['date'] }}</span>.
                            </p>

                            <p class="mb-4">
                                The reason for my resignation is: <br>
                                {{-- <span class="font-medium text-gray-900">{{ $emailData['reason'] }}</span> --}}
                            </p>
                            <p class="mb-4">@if(!empty($emailData['reason']))  {{ $emailData['reason'] }} @endif</p>

                            @if(!empty($emailData['description']))
                            <p class="mb-4">
                                Additional details: <br>
                                <span class="font-medium text-gray-900">{{ $emailData['description'] }}</span>
                            </p>
                            @endif

                            <p class="mb-4">
                                I want to express my gratitude for the opportunities I've had during my time here. 
                                I've learned a great deal and appreciate the support I've received from the team.
                            </p>

                            <p class="mb-4">
                                Please let me know how I can help with the transition process during my remaining time.
                            </p>

                            <p class="mb-6">Thank you for your understanding.</p>

                            <div class="text-gray-700">
                                <p class="font-semibold">Sincerely,</p>
                                <p>{{ $emailData['user_name'] }}</p>  
                                <p>{{ date('F j, Y') }}</p>
                            </div>
                    </div>

                  </td>
                </tr>

              </table>
            </td>
          </tr>
          <tr>
            <td>
              <table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;font-size:9px;font-family:Arial,sans-serif;">

                <tr>
                  <td style="padding:0;width:50%;" align="left">
                    <p style="margin:0;font-size:14px;line-height:16px;font-family:Arial,sans-serif;color:#ffffff;">
                        {{env('APP_NAME')}} @<?php echo date('Y'); ?><br/>
                    </p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>