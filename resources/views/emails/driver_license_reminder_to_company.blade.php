<!DOCTYPE html>
<html>
<head>
    <title>Driver License Expiry Reminder</title>
</head>
<body>
    <div class="gmail_quote">
        <p>Dear {{ strtoupper($emailData['companyName']) }},</p>
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
        <br>
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
                        <font face="trebuchet ms, sans-serif" size="4"><b>PT</b></font><b
                            style="font-family:&quot;trebuchet ms&quot;,sans-serif;font-size:large">C Compliance</b>
                    </div>
                    <div>
                        <font face="trebuchet ms, sans-serif" size="4"><b>07868882977</b></font><br>
                    </div>
                    <div><br></div>
                    <img src="https://erp.c4u-online.co.uk/storage/uploads/logo/email%20footer%20unimix_small.png"
                        class="CToWUd a6T" data-bit="iit" tabindex="0">
                    <div class="a6S" dir="ltr" style="opacity: 0.01; left: 588px; top: 654.5px;">
                        <span data-is-tooltip-wrapper="true" class="a5q" jsaction="JIbuQc:.CLIENT">
                            <button class="VYBDae-JX-I VYBDae-JX-I-ql-wdeprb-MD85tf-DKzjMe VYBDae-JX-I-ql-ay5-ays CgzRE"
                                jscontroller="PIVayb"
                                jsaction="click:h5M12e; clickmod:h5M12e;pointerdown:FEiYhc;pointerup:mF5Elf;pointerenter:EX0mI;pointerleave:vpvbp;pointercancel:xyn4sd;contextmenu:xexox;focus:h06R8; blur:zjh6rb;mlnRJb:fLiPzd;"
                                data-idom-class="CgzRE" jsname="hRZeKc" aria-label="Download attachment "
                                data-tooltip-enabled="true" data-tooltip-id="tt-c38" data-tooltip-classes="AZPksf" id=""
                                jslog="91252; u014N:cOuCgd,Kr2w4b,xr6bB; 4:WyIjbXNnLWY6MTgwMDE5NDQyNTc0MzczMjI1NyJd; 43:WyJpbWFnZS9qcGVnIl0.">
                                <span class="OiePBf-zPjgPe VYBDae-JX-UHGRz"></span><span class="bHC-Q"
                                    data-unbounded="false" jscontroller="LBaJxb" jsname="m9ZlFb" soy-skip=""
                                    ssk="6:RWVI5c"></span>
                                <span class="VYBDae-JX-ank-Rtc0Jf" jsname="S5tZuc" aria-hidden="true">
                                    <span class="bzc-ank" aria-hidden="true">
                                        <svg height="20" viewBox="0 -960 960 960" width="20" focusable="false" class=" aoH">
                                            <path
                                                d="M480-336 288-528l51-51 105 105v-342h72v342l105-105 51 51-192 192ZM263.72-192Q234-192 213-213.15T192-264v-72h72v72h432v-72h72v72q0 29.7-21.16 50.85Q725.68-192 695.96-192H263.72Z">
                                            </path>
                                        </svg>
                                    </span>
                                </span>
                                <div class="VYBDae-JX-ano"></div>
                            </button>
                            <div class="ne2Ple-oshW8e-J9" id="tt-c38" role="tooltip" aria-hidden="true">Download</div>
                        </span>
                    </div>
                    <br>

            </div>
        </font>
    </div>
</body>
</html>
