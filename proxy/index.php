<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/vnd.apple.mpegurl");

// Extract filename from URL (like starplus.m3u8)
$uri = basename($_SERVER['REQUEST_URI']);
$id = strtolower(str_replace(".m3u8", "", $uri));

// Load from original.m3u
$playlist = __DIR__ . '/original.m3u';
if (!file_exists($playlist)) {
    http_response_code(500);
    die("Playlist missing");
}

$lines = file($playlist);
$found = null;

foreach ($lines as $line) {
    $line = trim($line);
    if (filter_var($line, FILTER_VALIDATE_URL)) {
        $filename = pathinfo(parse_url($line, PHP_URL_PATH), PATHINFO_FILENAME);
        if (strtolower($filename) === $id) {
            $found = $line;
            break;
        }
    }
}

if (!$found) {
    http_response_code(404);
    die("Channel not found");
}

// Fetch stream
$options = [
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: Mozilla/5.0\r\n"
    ]
];
$ctx = stream_context_create($options);
$stream = @fopen($found, 'r', false, $ctx);

if ($stream) {
    fpassthru($stream);
    fclose($stream);
} else {
    http_response_code(502);
    die("Stream failed");
}
?>
