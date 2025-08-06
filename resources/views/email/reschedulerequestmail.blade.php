<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="x-apple-disable-message-reformatting">
  <title>Location Change Request</title>
  <style>
    table, td, div, h1, p {font-family: Arial, sans-serif;}
  </style>
</head>
<body style="margin:0;padding:0;">
  <table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;background:#ffffff;">
    <tr>
      <td align="center" style="padding:0;">
        <table role="presentation" style="width:602px;border-collapse:collapse;border:1px solid #cccccc;border-spacing:0;text-align:left;">
          <tr>
            <td align="center" style="padding:40px 0 30px 0;background:#70bbd9;color:white;font-size:26px"><b><img src="{{env('LOGO')}}" style="width: 20%"></b>

            </td>
          </tr>
          <tr>
            <td style="padding:36px 30px 42px 30px;">
              <table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
                <tr>
                  <td style="padding:0 0 36px 0;color:#153643;">

                     <div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow-lg">
                            <p class="mb-4">Hello <span class="font-semibold text-gray-800">Sir/Ma’am</span>,</p>

                            <p class="mb-4">
                                I hope this message finds you well.
                            </p>

                            <p class="mb-4">
                                I am writing to inform you that my location has been changed to
                                <span class="font-medium text-gray-900">{{ $emailData['location'] }}, </span>
                                effective from
                                <span class="font-medium text-gray-900">{{ $emailData['date'] }}</span>
                                due to. <br>
                                <span class="font-medium text-gray-900">{{ $emailData['reason'] }}</span>.
                            </p>

                            <p class="mb-4">
                                Due to this change, I kindly request that my work or meeting schedule be rescheduled in accordance with the new location and timing constraints.
                            </p>

                            <p class="mb-4">Kindly consider and approve my request.</p>
                            <p class="mb-4">@if(!empty($emailData['text']))  {{ $emailData['text'] }} @endif</p>
                            <p class="mb-6">Thank you for your support.</p>

                            <div class="text-gray-700">
                                <p class="font-semibold">Best regards,</p>
                                <p>{{ $emailData['user_name'] }}</p>
                            </div>
                    </div>

                  </td>
                </tr>

              </table>
            </td>
          </tr>
          <tr>
            <td style="padding:30px;background:#70bbd9;">
              <table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;font-size:9px;font-family:Arial,sans-serif;">
                <tr>
                  <td style="padding:0;width:50%;" align="left">
                    <p style="margin:0;font-size:14px;line-height:16px;font-family:Arial,sans-serif;color:#ffffff;">
                        {{env('APP_NAME')}} @2023<br/>
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
