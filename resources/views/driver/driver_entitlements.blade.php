

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Entitlements</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .container {
            flex: 1;
            width: 90%;
            margin: 0 auto;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-bottom: 2px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header img {
            max-width: 150px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .driver-info {
            margin: 20px 0;
            text-align: right;
        }
        .driver-info h2 {
            margin: 0 0 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #dee2e6;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #e9ecef;
        }
        .link {
            margin-top: 20px;
            text-align: center;
        }
        .link a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
        .link a:hover {
            text-decoration: underline;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 10px;
            text-align: center;
            border-top: 2px solid #e0e0e0;
            margin-top: auto;
            color: black
        }
    </style>
</head>
<body>
    @php
    use App\Models\Utility;
    $setting = \App\Models\Utility::settings();

@endphp
    <div class="header">
        <img src="{{ $img }}" style="max-width: 150px;" alt="Logo"/>
        <div class="driver-info">
            <h2>{{ $driver->name }}</h2>
            <p><strong>Date of Birth:</strong> {{ $driver->driver_dob }}</p>
            <p><strong>Address:</strong> {{ $driver->driver_address }}, {{ $driver->post_code }}</p>
        </div>
    </div>
    <div class="row">
        <div class="col-xxl-5" style="width: 90%;margin-left: 9%;margin-right: 9%;">
            <div class="card report_card total_amount_card">
                <div class="card-body pt-0" style="margin-top: 2%;">
                    <address class="mb-0 text-sm">
                        <h3 style="text-align: center;">{{ __('Entitlements') }}</h3>

                        <address class="mb-0 text-sm">
                            <div class="card flex-fill">
                                <div class="card-body">
                                    <!-- Table for Entitlements -->
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th style="width: 10%;">{{ __('Category Code') }}</th>
                                                <th>{{ __('Legal Literal') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($driver->entitlements as $entitlement)
                                            <tr>
                                                <td>{{ $entitlement->category_code }}</td>
                                                <td>{{ $entitlement->category_legal_literal }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </address>
                    </address>
                </div>
            </div>
    </div>
    </div>
    <div class="footer">
        <p class="mb-0 text-muted"> &copy;
            {{ date('Y') }} {{ $setting['footer_text'] ? $setting['footer_text'] : config('app.name', 'ERPGo') }}
        </p>
    </div>
</body>
</html>
