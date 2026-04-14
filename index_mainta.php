<?php

$maintenance_mode = true; // Set this to false to turn off maintenance mode

if ($maintenance_mode) {
    // Serve the maintenance mode page
    header('HTTP/1.1 503 Service Unavailable');
    header('Retry-After: 3600'); // Retry after 1 hour
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Maintenance Mode</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                text-align: center;
                padding: 50px;
                background-color: #f7f7f7;
            }
            h1 {
                color: #333;
            }
            p {
                color: #666;
            }
        </style>
    </head>
    <body>
        <h1>We'll be back soon!</h1>
        <p>We regret to inform you that this website is currently suspended. This action may be due to various reasons, including maintenance, policy violations, or billing issues.</p>
        <p>Please check back later.</p>
    </body>
    </html>

    <?php
    exit;
}
?>