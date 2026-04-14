<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>License Check Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #444;
        }
        .content {
            margin-top: 20px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 10px;
            font-size: 14px;
            color: white;
            background-color: #4CAF50;
            text-decoration: none;
            border-radius: 5px;
        }
        .button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <p>Dear {{ $recipientName }},</p>

            <p>
               
                Please find attached confirmation  that the driver licence check for {{ $emailData['currentMonthYear'] }} has been carried out in accordance with our records for the drivers listed in our database.
            </p>

<table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
    <thead>
        <tr>
            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Driver Name</th>
            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($emailData['drivers'] as $driver)
            <tr>
                <td style="border: 1px solid #ddd; padding: 8px;">{{ $driver['name'] }}</td>
                <td style="border: 1px solid #ddd; padding: 8px;">
                    <!-- Download Button for Driver PDF -->
                    <a href="{{ route('driver.pdf', ['slug' => $driver['slug']]) }}" style="background-color: #007bff; color: white; padding: 6px 12px; border-radius: 5px; text-decoration: none;">
                        Download
                    </a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<br>
            
            <p style="color:#ff3c00">Should you identify any discrepancy, please contact our office immediately at support@ptctransport.co.uk</p>
            <p>Please note, it remains your responsibility to ensure that all driver information within the database is accurate and kept up to date. Any non-conformity identified will not be passed to PTC.</p><br>
            <p>Kind regards,<br>Customer Service</p>

        </div>
        <div class="footer">
           
            <div style="text-align: left; margin-bottom: 10px;">
        <img src="https://erp.c4u-online.co.uk/storage/uploads/logo/email%20footer%20unimix_small.png" 
             style="vertical-align:middle; border-style:none; max-width: 180px;">
    </div>
    <div style="text-align: left; margin-bottom: 10px;">
        <img src="https://erp.c4u-online.co.uk/storage/uploads/logo/Email%20Footer%20logo%20small.png" 
             style="vertical-align:middle; border-style:none; max-width: 120px;">
    </div>
    <div style="text-align: left; margin-bottom: 10px;">
             <p>&copy; {{ date('Y') }} PTC Transport. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
