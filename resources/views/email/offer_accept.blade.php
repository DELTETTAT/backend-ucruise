<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="x-apple-disable-message-reformatting">
  <title>Offer Response</title>
  <style>
        body {
            margin: 0;
            padding: 0;
            text-align: center;
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .box {
            margin: 100px auto;
            width: 400px;
            padding: 30px;
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .box h2 {
            color: green;
            margin-bottom: 20px;
        }
        .box a {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
            color: blue;
            font-size: 16px;
        }
        .box a:hover {
            text-decoration: underline;
        }

        .box input[type="text"] {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #ccc;
        border-radius: 5px;
        margin-bottom: 20px;
        font-size: 16px;
        box-sizing: border-box;
        transition: border 0.3s ease, box-shadow 0.3s ease;
      }

    .box input[type="text"]:focus {
        border-color: #28a745;
        box-shadow: 0 0 5px rgba(40, 167, 69, 0.3);
        outline: none;
    }

    .box input[type="submit"] {
        background-color: #28a745;
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .box input[type="submit"]:hover {
        background-color: #218838;
        transform: translateY(-1px);
    }
    </style>
</head>

  <body style="text-align: center; margin-top: 100px;">
  <div class="box">
    @if($message)
        <h2>{{ $message }}</h2>
    @elseif($dataform && isset($dataform['unique_id']))
        <form action="{{ url('/') }}/api/offeredRejected" method="post">
            @csrf
            <input type="hidden" name="db_name" value="{{ $dataform['db_name'] }}">
            <input type="hidden" name="applicant_id" value="{{ $dataform['applicant_id'] }}">
            <input type="hidden" name="unique_id" value="{{ $dataform['unique_id'] }}">
            <h2>Enter the reason for declining the offer</h2>
            <input type="text" placeholder="Enter Reason" name="reason">
            <input type="submit" value="Submit">
        </form>
    @else
        <h2>Invalid request</h2>
    @endif
</div>

  </body>

</html>


