
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="x-apple-disable-message-reformatting">
  <title>Reminder</title>
  <style>

    table, td, div, h1, p {font-family: Arial, sans-serif;}
  </style>
</head>
<body style="margin:0;padding:0;">


 <h2>Driver Unassignment Notification</h2>
  <p>Have no assigned Vehicle to the following emaployee</p>
    <table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%;">
      <thead>
          <tr>
              <th>Name</th>
              <th>Email</th>
          </tr>
      </thead>
      <tbody>
          @foreach($unassignedEmployees as $employee)
              <tr>
                  <td>{{ $employee['first_name'] ?? '' }}</td>
                  <td>{{ $employee['email'] ?? '' }}</td>
              </tr>
          @endforeach
      </tbody>
    </table>

</body>
</html>
