<!DOCTYPE html>
<html>
<head>
    <title>WalkAround Completed</title>
</head>
<body>
    <h1>WalkAround Completed By {{ $driver_name }}</h1>
    <p>Dear Team,</p>

    <p>This is an automated notification regarding the completion of a WalkAround inspection.</p>

    <p><strong>WalkAround Details:</strong></p>
    <ul>
        <li>WalkAround ID: #{{ $id }}</li>
        <li>Vehicle: {{ $vehicle }}</li>
        <li>Duration: {{ $duration }}</li>
        <li>Total Defects Identified: {{ $defect_count }}</li>
        <!-- Add more fields as needed -->
    </ul>

    <p>Please review the defects and take necessary actions as per the inspection report.</p>

    <p>For more information, please log in to the system.</p>

    <p>This is an automatically generated email. No reply is required.</p>

    <p>Best regards,<br>
    PTC Transport</p>
</body>
</html>
