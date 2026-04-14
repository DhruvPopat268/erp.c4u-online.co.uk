<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Driver Details Lookup</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS for styling -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Driver Details Lookup</h2>
    <form id="driverForm">
        <div class="form-group">
            <label for="drivingLicenceNumber">Driving Licence Number:</label>
            <input type="text" class="form-control" id="drivingLicenceNumber" name="drivingLicenceNumber" required>
        </div>
        <button type="submit" class="btn btn-primary mt-2">Get Driver Details</button>
    </form>

    <div id="response" class="mt-4">
        <h4>Response:</h4>
        <pre id="jsonOutput" style="background: #f1f1f1; padding: 10px;"></pre>
    </div>
</div>

<script>
    document.getElementById('driverForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const licenceNumber = document.getElementById('drivingLicenceNumber').value;

        try {
            // Step 1: Get the token
            const tokenResponse = await fetch("https://erp.c4u-online.co.uk/api/driver/get/token");
            const tokenData = await tokenResponse.json();

            if (!tokenData.status) {
                document.getElementById('jsonOutput').textContent = "Error getting token: " + tokenData.message;
                return;
            }

            // Step 2: Call driver details API
            const driverResponse = await fetch("https://erp.c4u-online.co.uk/api/third-party/driver-details", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": tokenData.token,
                },
                body: JSON.stringify({
                    drivingLicenceNumber: licenceNumber
                })
            });

            const text = await driverResponse.text();
            try {
                const driverData = JSON.parse(text);
                document.getElementById('jsonOutput').textContent = JSON.stringify(driverData, null, 4);
            } catch (err) {
                document.getElementById('jsonOutput').textContent = "Invalid response:\n" + text;
            }
        } catch (error) {
            document.getElementById('jsonOutput').textContent = "Request failed: " + error.message;
        }
    });
</script>
</body>
</html>
