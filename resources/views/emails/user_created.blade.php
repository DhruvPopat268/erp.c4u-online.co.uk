<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Created</title>
</head>
<body>
    <p>Dear {{ $user->username }},</p>

    <p>We are pleased to inform you that your account with PTC ERP for <strong>{{ strtoupper($companyname) }}</strong> has been successfully created. You can now access the system using the following login details:</p>

    <p><strong>Email:</strong> {{ $user->email }}<br>
    <strong>Password:</strong> {{ $password }}</p>

    <p>For security purposes, we recommend that you update your password upon first login. To access your account, please visit our website <a href="https://erp.c4u-online.co.uk">www.erp.c4u-online.co.uk</a>.</p>

    <p>If you require any assistance or have any queries, our support team is available to help. We aim to respond to all enquiries within 24 hours.</p>

    <p><strong>Contact Information:</strong><br>
    Telephone: 07448 121 121<br>
    Email: <a href="mailto:office@ptctransport.co.uk">office@ptctransport.co.uk</a><br>
    Website: <a href="https://www.ptctransport.co.uk">www.ptctransport.co.uk</a><br>
    Address: Suite #31, Unimix House, Abbey Road Park Royal, London, NW10 7TR</p>

    <p>Thank you for choosing PTC ERP. We look forward to helping <strong>{{ $companyname }}</strong> manage your business operations more efficiently.</p>

    <p>Yours sincerely,<br>
    PTC Support Team<br>
    Suite #31, Unimix House, Abbey Road Park Royal, London, NW10 7TR</p>
     <p style="box-sizing:border-box;margin-top:0;margin-bottom:1rem"><img src="https://erp.c4u-online.co.uk/storage/uploads/logo/email%20footer%20unimix_small.png" style="box-sizing:border-box;vertical-align:middle;border-style:none" class="CToWUd" data-bit="iit" jslog="138226; u014N:xr6bB; 53:WzAsMl0."></p>
    <p style="box-sizing:border-box;margin-top:0;margin-bottom:1rem"><img src="https://erp.c4u-online.co.uk/storage/uploads/logo/Email%20Footer%20logo%20small.png" style="box-sizing:border-box;vertical-align:middle;border-style:none" class="CToWUd" data-bit="iit" jslog="138226; u014N:xr6bB; 53:WzAsMl0."></p>
    
</body>
</html>
