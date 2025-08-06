<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="x-apple-disable-message-reformatting">
  <title>Leave Request</title>
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
                            <p class="mb-4">Hello <span class="font-semibold text-gray-800"> {{ $detais['name'] }}</span>,</p>

                            <p class="mb-4">Your password has been successfully updated. Please find your updated login details below.</p>
                            <p class="mb-4">
                              <span class="font-medium text-gray-900"> Username : {{ $detais['email'] }}</span><br>
                              <span class="font-medium text-gray-900"> Password : {{ $detais['pass'] }}</span><br>
                              <span class="font-medium text-gray-900"> Date : {{ $detais['date'] }}</span>
                            </p>
                    </div>

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
