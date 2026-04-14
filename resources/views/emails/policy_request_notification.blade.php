<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  
</head>
<body style="font-family: Arial, sans-serif; background-color:#f7f7f7; padding:20px;">
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; margin:0 auto; background:#ffffff; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1);">
    <tr>
      <td style="padding:20px; text-align:center; background:#2c3e50; color:#ffffff; border-top-left-radius:8px; border-top-right-radius:8px;">
        <h2>Policy Access Request</h2>
      </td>
    </tr>
    <tr>
      <td style="padding:20px; color:#333333;">
        <p>Dear PTC Transport,</p>
        <p>A new policy request has been submitted by the following operator:</p>
        <table width="100%" cellpadding="6" cellspacing="0" border="0" style="border:1px solid #dddddd; border-radius:6px; margin:15px 0;">
          <tr>
            <td style="background:#f2f2f2; font-weight:bold; width:30%;">Operator Name</td>
            <td>{{ $OperatorName }}</td>
          </tr>
          <tr>
            <td style="background:#f2f2f2; font-weight:bold;">Company Name</td>
            <td>{{ $CompanyName }}</td>
          </tr>
          <tr>
            <td style="background:#f2f2f2; font-weight:bold;">Requested Policies</td>
            <td>
              <ul>
                @foreach($PolicyList as $policy)
                 <li>
  @if(is_array($policy))
    {{ implode(', ', $policy) }}
  @else
    {{ $policy }}
  @endif
</li>
                @endforeach
              </ul>
            </td>
          </tr>
        </table>
        <p>Please review the above request and take the necessary action.</p>
        <p style="margin-top:30px;">Thank you,<br><strong>PTC Transport</strong></p>
      </td>
    </tr>
    <tr>
      <td style="padding:15px; text-align:center; background:#f2f2f2; color:#777777; font-size:12px; border-bottom-left-radius:8px; border-bottom-right-radius:8px;">
        This is an automated notification. Please do not reply directly to this email.
      </td>
    </tr>
  </table>
</body>
</html>
