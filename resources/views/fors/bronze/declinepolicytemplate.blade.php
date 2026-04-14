<!DOCTYPE html>
<html>
<head>
    <title>Decline Drivers List</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>{{ $policyName}} -Decline Drivers List</h1>

    <table>
        <thead>
            <tr>
                <th>Driver Name</th>
                <th>Company Name</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($declinedDrivers as $driver)
                <tr>
                    <td>{{ $driver->name }}</td>
                    <td>{{ $driver->companyName }}</td>
                    <td>{{ $driver->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
