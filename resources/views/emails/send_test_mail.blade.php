<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .wrapper {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .header {
            background-color: #405189;
            padding: 24px 32px;
        }
        .header h2 {
            color: #ffffff;
            margin: 0;
            font-size: 20px;
        }
        .body {
            padding: 32px;
            color: #333333;
            font-size: 15px;
            line-height: 1.7;
            white-space: pre-wrap;
        }
        .footer {
            padding: 16px 32px;
            background-color: #f8f9fa;
            font-size: 12px;
            color: #888888;
            border-top: 1px solid #eeeeee;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h2>PTC Transport</h2>
        </div>
        <div class="body">
            {{ $emailBody }}
        </div>
        <div class="footer">
            This email was sent from {{ config('app.name') }} &mdash; {{ config('app.url') }}
        </div>
    </div>
</body>
</html>
