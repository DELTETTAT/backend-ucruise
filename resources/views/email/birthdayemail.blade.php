
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Happy Birthday</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">

  <table width="100%" cellpadding="0" cellspacing="0" style="padding: 40px 0;">
    <tr>
      <td align="center">

        <!-- Email Container with Background Image -->
        <table width="600" cellpadding="0" cellspacing="0"
               background="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSIEbl9NbGVePhFv5bdmW9zAhvqV2zs4Ynmig&s"
               style="background-repeat: repeat; background-size: 150px; background-color: #f9f9f9; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); overflow: hidden;">

          <tr>
            <td style="padding: 30px 40px 10px;" align="center">
              <h1 style="font-size: 32px; color: #FF4081; margin: 0;">🎂 Happy Birthday, {{$userData['name']}}! 🎉</h1>
            </td>
          </tr>

          <tr>
            <td align="center" style="padding: 5px 40px 0;">
              <h3 style="font-size: 20px; color: #666666; margin: 0;">Today is your special day!</h3>
            </td>
          </tr>

          <tr>
            <td align="center" style="padding: 20px 40px;">
              <p style="font-size: 16px; color: #444444; line-height: 1.8; margin: 0;">
                We hope your day is filled with happiness, surprises, and unforgettable moments. <br>
                May the year ahead bring you endless success, joy, and laughter. <br><br>
                Thank you for being a part of our journey. You’re truly appreciated!
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td align="center" style="background-color: #fafafa; padding: 20px; color: #999999; font-size: 12px;">
              &copy; {{ $userData['year'] ?? date('Y') }} {{ $userData['company'] ?? 'Unify Group'}}. All rights reserved.
            </td>
          </tr>

        </table>

      </td>
    </tr>
  </table>

</body>
</html>
