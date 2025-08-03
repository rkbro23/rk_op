<?php
header('Access-Control-Allow-Origin: *');
header('Cache-Control: max-age=300');

// Get encoded URL from path
$encodedUrl = ltrim($_SERVER['REQUEST_URI'], '/proxy/');
$originalUrl = base64_decode($encodedUrl);

// Validate URL
if (!filter_var($originalUrl, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    die("Invalid URL");
}

// Forward with proper headers
$options = [
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: Mozilla/5.0\r\n"
    ]
];

$context = stream_context_create($options);
$stream = fopen($originalUrl, 'r', false, $context);

if ($stream) {
    header('Content-Type: application/vnd.apple.mpegurl');
    fpassthru($stream);
    fclose($stream);
} else {
    http_response_code(502);
    die("Stream unavailable");
}
?>
