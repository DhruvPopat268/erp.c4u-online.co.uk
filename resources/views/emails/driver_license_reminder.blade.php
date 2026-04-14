<!DOCTYPE html>
<html>
<head>
    <title>Driver License Expiry Reminder</title>
</head>
<body>
    <div class="gmail_quote">
        <p>Dear {{ $emailData['driverName'] }},</p>
        <p>Please be advised that the following information is either approaching its expiration date or has already expired. If any of the listed details have expired, kindly provide us with the updated information at your earliest convenience. Kindly take a note of the following:</p>

        @if ($emailData['expiryDates']['driver_licence_expiry'])
            <p><b>Driver Licence Expiry</b></p>
            <table style="border-collapse:collapse;width:50%;background-color:white" cellspacing="0" cellpadding="5" border="1">
                <thead>
                    <tr style="text-align:center">
                        <th>DRIVER NAME</th>
                        <th>Driver Licence No</th>
                        <th>Due By</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                    <td>{{ $emailData['driverName'] }}</td>
                    <td>{{ $emailData['licenseNumber'] }}</td>
                    <td>{{ $emailData['expiryDates']['driver_licence_expiry'] }}</td>
                    </tr>
                </tbody>
            </table>
        @endif

    @if ($emailData['expiryDates']['cpc_validto'])
        <p style="margin-top: 10px"><b>Driver CPC Card Expiry</b></p>
        <table style="border-collapse:collapse;width:50%;background-color:white" cellspacing="0" cellpadding="5" border="1">
            <thead>
                <tr style="text-align:center">
                    <th>DRIVER NAME</th>
                    <th>CPC Card Expiry Date</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $emailData['driverName'] }}</td>
                    <td>{{ $emailData['cpcCard'] }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    @if ($emailData['expiryDates']['tacho_card_valid_to'])
            <p style="margin-top: 10px"><b>Tacho Card Expiry</b></p>
            <table style="border-collapse:collapse;width:50%;background-color:white" cellspacing="0" cellpadding="5" border="1">
                <thead>
                    <tr style="text-align:center">
                        <th>DRIVER NAME</th>
                        <th>Tacho Card No</th>
                        <th>Due By</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                    <td>{{ $emailData['driverName'] }}</td>
                    <td>{{ $emailData['tachoCardNo'] }}</td>
                    <td>{{ $emailData['expiryDates']['tacho_card_valid_to'] }}</td>
                    </tr>
                </tbody>
            </table>
        @endif


        <font color="#888888">
            <span class="gmail_signature_prefix">-- </span><br>
            <div dir="ltr" class="gmail_signature" data-smartmail="gmail_signature">
                <div dir="ltr">
                    <div>
                        <font face="trebuchet ms, sans-serif"><b>Kind regards,</b></font>
                    </div>
                    <div>
                        <font face="trebuchet ms, sans-serif"><b><br></b></font>
                    </div>
                    <div>
                        <font face="trebuchet ms, sans-serif" size="4"><b>PT</b></font>
                        <b style="font-family:&quot;trebuchet ms&quot;,sans-serif;font-size:large">C Compliance</b>
                    </div>
                    <div>
                        <font face="trebuchet ms, sans-serif" size="4"><b>07868882977</b></font><br>
                    </div>
                    <div><br></div>
                    <img src="https://erp.c4u-online.co.uk/storage/uploads/logo/email%20footer%20unimix_small.png" alt="Email Footer">
                    </div>
            </div>
        </font>
    </div>
</body>
</html>
