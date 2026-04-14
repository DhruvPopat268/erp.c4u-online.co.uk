<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internal Server Error</title>
    <style>
        /* Ensure body and html take up the full height without margin or padding */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        /* Container style to center the image */
        .image-container {
            width: 100%;
            height: 100vh; /* Full viewport height */
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        /* Style the image to fill the entire container */
        img.background-image {
            width: 100%;
            height: auto; /* Keeps the aspect ratio of the image */
            max-height: 100vh; /* Ensures the image doesn't overflow the viewport height */
            object-fit: cover; /* Covers the screen without distortion */
        }

        /* Add responsive styles for smaller devices */
        @media (max-width: 600px) {
            img.background-image {
                width: 100vw; /* Full width on smaller screens */
                height: auto;
            }
        }

        /* Style for logo at the top */
        .logo-container {
            position: absolute;
            top: 20px; /* Adjust the distance from the top */
            left: 50%;
            transform: translateX(-50%); /* Center the logo horizontally */
            text-align: center;
            max-width: 350px; /* Limit logo size */
            width: 100%;
        }

        .logo-container img {
            width: 54%; /* Ensure logo scales to container width */
            max-width: 350px; /* Maximum width for the logo */
        }

        .error-message {
            position: absolute;
            top: 50%; /* Center vertically */
            left: 50%;
            transform: translate(-50%, -50%);
            color: rgb(2, 2, 2);
            font-size: 36px;
            font-weight: bold;
            text-align: center;
        }
        .error-message .number {
            position: absolute;
            top: -19%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: rgb(2, 2, 2);
            font-size: 67px;
            font-weight: bold;
            text-align: center;
            font-family: cursive;
        }
        .error-message .message p {
            font-size: 26px;
            margin-top: -36px;
            color: #08080852;
        }

        .error-buttons {
            margin-top: 20px;
        }

        .error-buttons a {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px;
            background-color: #007bff;
            color: #fff;
            font-size: 16px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .error-buttons a:hover {
            background-color: #0056b3;
        }

        @media (max-width: 768px) {
            .error-message .number {
                font-size: 41px;
                margin-top: 22px;
            }
            .error-message .message h3 {
                font-size: 24px;
            }
            .error-message .message p {
                font-size: 16px;
                margin-top: -10px;
            }
        }
    </style>
</head>
@php
    use App\Models\Utility;
    $setting = Utility::settings();
    $company_logo = Utility::getValByName('company_logo');
    $imagePath = storage_path('/uploads/logo/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : '5-logo-dark.png'));

    // Check if the image file exists
    if (file_exists($imagePath)) {
        $imageData = base64_encode(file_get_contents($imagePath));
        $img = 'data:image/png;base64,' . $imageData;
    } else {
        \Log::error('Image file does not exist: ' . $imagePath);
        $img = ''; // Fallback image
    }
@endphp
<body>
    <div class="image-container">
        <div class="error-message">
            <div class="number">
            <h1>500</h1>
        </div>
        <div class="message">
            <h3>Internal Server Error</h3>
            <p>The Server encountered an error and could not complete your request</p>
        </div>
            <!-- Add buttons for Back and Home Page -->
            <div class="error-buttons">
                <a href="javascript:history.back()">Go Back</a>
                <a href="{{ url('/') }}">Go Home Page</a>
            </div>
        </div>

        <div class="logo-container">
            <a href="#" class="b-brand">
                @if($img)
                    <img src="{{ $img }}" alt="{{ config('app.name', 'PTC') }}" class="logo logo-lg">
                @else
                    <img src="{{ asset('storage/uploads/logo/5-logo-dark.png') }}" alt="{{ config('app.name', 'PTC') }}" class="logo logo-lg">
                @endif
            </a>
        </div>
    </div>
</body>
</html>
