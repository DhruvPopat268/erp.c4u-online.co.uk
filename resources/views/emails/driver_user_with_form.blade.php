<!DOCTYPE html>
<html>
<head>
    <title>Your Driver Account Created</title>
    <style>
        .button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #ffffff;
            background-color: #007BFF; /* Bootstrap primary color */
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }

        .button:hover {
            background-color: #0056b3; /* Darker shade on hover */
        }

        .links {
            margin-top: 20px;
        }

        .links a {
            display: block;
            margin: 5px 0;
            color: #007BFF;
            text-decoration: none;
        }

        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div>
        <h1>Your Driver Account Has Been Created</h1>
        <p>Your username: {{ $username }}</p>
        <p>Your password: {{ $password }}</p>
        <p>Company Account Id: {{ $driver->companyDetails->account_no }}</p>
        <p>Driver Number: {{ $driver->ni_number }}</p>

        <p>If you have any questions, feel free to reach out!</p>

        <div class="links">
            <p>Please find the links to our Driver Application below:</p>
            <a href="https://play.google.com/store/apps/details?id=com.ptc.driver" target="_blank">Android Application: Download here</a>
            <a href="https://apps.apple.com/app/id6708231491" target="_blank">iOS Application: Download here</a>
            <p>You can log in using your credentials and complete the Driver Consent form within the app.</p>
        </div>
    </div>
</body>
</html>
