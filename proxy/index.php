<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/vnd.apple.mpegurl");
header('Cache-Control: max-age=300');

// Grab ?id= from URL
if (!isset($_GET['id'])) {
    http_response_code(400);
    die("Missing ID");
}

$decoded_id = base64_decode($_GET['id']);
if (!$decoded_id) {
    http_response_code(400);
    die("Invalid ID");
}

// Load original.m3u from same folder
$playlist = __DIR__ . '/original.m3u';
if (!file_exists($playlist)) {
    http_response_code(500);
    die("Playlist not found");
}

$lines = file($playlist);
$foundUrl = null;

foreach ($lines as $line) {
    $line = trim($line);
    if (filter_var($line, FILTER_VALIDATE_URL)) {
        // Check filename (without .m3u8)
        $url_path = parse_url($line, PHP_URL_PATH);
        $filename = pathinfo($url_path, PATHINFO_FILENAME);
        if (strtolower($filename) === strtolower($decoded_id)) {
            $foundUrl = $line;
            break;
        }
    }
}

if (!$foundUrl) {
    http_response_code(404);
    die("Channel not found");
}

// Fetch stream and forward it
$opts = [
    "http" => [
        "method" => "GET",
        "header" => "User-Agent: Mozilla/5.0\r\n"
    ]
];

$context = stream_context_create($opts);
$stream = @fopen($foundUrl, 'r', false, $context);

if ($stream) {
    fpassthru($stream);
    fclose($stream);
} else {
    http_response_code(502);
    die("Stream unavailable");
}
?>
