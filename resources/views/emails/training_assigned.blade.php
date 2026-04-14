<!DOCTYPE html>
<html>
<head>
    <title>Upcoming Training Course Reminder</title>
</head>
<body>
    <h1>Dear {{ $driver->name }},</h1>
    <p>This is a reminder that your scheduled training course, <strong>{{ $training->trainingCourse->name }}</strong>, provided by <strong>{{ $training->company->name }}</strong>, will begin on <strong>{{ $fromDate }}</strong> and end on <strong>{{ $toDate }}</strong>.</p>

    <h2>Training Details:</h2>
    <ul>
        <li>Training Type: {{ $training->trainingType->name }}</li>
        <li>Course Name: {{ $training->trainingCourse->name }}</li>
                <li>Description: {{ $training->description }}</li>
        <li>Start Date: {{ $fromDate }}</li>
        <li>End Date: {{ $toDate }}</li>
        <li>Duration: {{ $training->trainingCourse->duration }} hours</li>
    </ul>

    <p>Please ensure you are prepared and have access to any necessary materials before the start date. Should you have any questions or require further assistance, feel free to contact our support team.</p>

    <p>We look forward to your participation.</p>

    <p>Best regards,<br>
    Support Team<br>
    PTC Support Team<br>
    Suite #31, Unimix House, Abbey Road Park Royal, London, NW10 7TR</p>
     <p style="box-sizing:border-box;margin-top:0;margin-bottom:1rem"><img src="https://erp.c4u-online.co.uk/storage/uploads/logo/email%20footer%20unimix_small.png" style="box-sizing:border-box;vertical-align:middle;border-style:none" class="CToWUd" data-bit="iit" jslog="138226; u014N:xr6bB; 53:WzAsMl0."></p>
    <p style="box-sizing:border-box;margin-top:0;margin-bottom:1rem"><img src="https://erp.c4u-online.co.uk/storage/uploads/logo/Email%20Footer%20logo%20small.png" style="box-sizing:border-box;vertical-align:middle;border-style:none" class="CToWUd" data-bit="iit" jslog="138226; u014N:xr6bB; 53:WzAsMl0."></p>
    
</body>
</html>
