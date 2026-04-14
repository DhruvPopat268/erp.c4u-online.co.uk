<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            color: #fff;
            background-color: #007bff;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .logo {
            display: block;
            width: 100px !important;
            height: auto !important;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
        @if($logo)
            <img src="{{ $message->embed($logo) }}" alt="Logo" class="logo">
        @endif

        @if($headerImage)
            <a href="{{ $headerImageUrl ?? '#' }}">
                <img src="{{ $message->embed($headerImage) }}" alt="Header Image" style="width: 100%; height: auto; display: block; margin-top: 20px; margin-bottom: 20px;">
            </a>
        @endif

        <p>{!! $text !!}</p>

        @if($buttonUrl && $buttonText)
            <a href="{{ $buttonUrl }}" class="button">{{ $buttonText }}</a>
        @endif

        <hr style="margin-top: 20px; margin-bottom: 20px; border: 0; border-top: 1px solid #ccc;">

        <p style="font-size: 0.8em; color: #666;">
            This email was sent from {{ config('app.name') }}.
        </p>
    </div>
</body>
</html>
