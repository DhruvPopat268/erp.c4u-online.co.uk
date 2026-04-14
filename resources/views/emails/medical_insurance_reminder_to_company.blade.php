<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Medical Insurance Renewal Reminder</title>
</head>
<body>
    <h1>Medical Insurance Renewal Reminder</h1>

    <p><b>Hello {{ strtoupper($company->name) }},</b></p>

    <p>This is a reminder that medical insurance is due for renewal soon for the following drivers:</p>

    <ul>
        @foreach($drivers as $driver)
            @php
                // Convert DOB from 'd/m/Y' to a DateTime object
                $dob = \Carbon\Carbon::createFromFormat('d/m/Y', $driver['driver_dob']);

                // Calculate age
                $age = \Carbon\Carbon::now()->diffInYears($dob);

                // Format DOB as 'd M Y' (e.g., 04 Jul 1974)
                $formattedDob = $dob->format('d M Y');
            @endphp
            <li>{{ $driver['name'] }} (DOB: {{ $formattedDob }}, Age: {{ $age }})</li>
        @endforeach
    </ul>

    <p>Please ensure that the necessary steps are taken to renew the medical insurance.</p>

    <h3>Thank you.</h3>
</body>
</html>
