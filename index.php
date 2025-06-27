<?php
// RemoteDownloaderPHP — https://github.com/BaseMax/RemoteDownloaderPHP
// Developed by Max Base (Seyyed Ali Mohammadiyeh)

// === CONFIGURATION ===
set_time_limit(0);
ini_set('zlib.output_compression', 'Off');
ob_implicit_flush(true);
while (ob_get_level()) ob_end_flush();

define('LOG_FILE', __DIR__ . '/downloader.log');
define('USER_AGENT', 'RemoteDownloaderPHP/1.1');
define('DOWNLOAD_DIR', __DIR__ . '/downloads');

if (!is_dir(DOWNLOAD_DIR)) {
    mkdir(DOWNLOAD_DIR, 0755, true);
}

function log_message($msg) {
    file_put_contents(LOG_FILE, "[" . date('Y-m-d H:i:s') . "] $msg\n", FILE_APPEND);
}

function sanitize_filename($name) {
    return preg_replace('/[^a-zA-Z0-9\._-]/', '_', basename($name));
}

function get_remote_headers($url) {
    $head = @get_headers($url, 1);
    if (!$head || (!isset($head[0]) || !preg_match('/HTTP\/\d\.\d\s(200|301|302)/', $head[0]))) {
        return false;
    }
    return $head;
}

// === INPUT HANDLING ===
$url = trim($_REQUEST['url'] ?? '');
$confirm = isset($_POST['confirm']);
$customName = trim($_POST['filename'] ?? '');

if (!$url) {
    echo <<<HTML
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>RemoteDownloaderPHP</title>
<style>
  body{font-family:sans-serif;padding:2em;}
  input,button{padding:0.5em;width:90%;margin:0.5em 0;}
</style></head><body>
<h1>RemoteDownloaderPHP</h1>
<form method="POST">
  <input name="url" placeholder="https://example.com/file.zip" required />
  <input name="filename" placeholder="Optional custom filename" />
  <button type="submit">Check & Continue</button>
</form>
<footer style="margin-top:3em; font-size:0.8em; color:#666; text-align:center;">
  <hr>
  <p>🔗 <a href="https://github.com/BaseMax/RemoteDownloaderPHP" target="_blank" rel="noopener noreferrer">RemoteDownloaderPHP on GitHub</a></p>
</footer>
</body></html>
HTML;
    exit;
}

// === FETCH HEADERS ===
$headers = get_remote_headers($url);
if (!$headers) {
    echo "❌ Invalid or unreachable URL.";
    log_message("Invalid URL: $url");
    exit;
}

$type = $headers['Content-Type'] ?? 'unknown';
if (is_array($type)) {
    $type = end($type);
}

$lengthBytes = $headers['Content-Length'] ?? null;
if (is_array($lengthBytes)) {
    $lengthBytes = end($lengthBytes);
}

$sizeReadable = $lengthBytes ? round($lengthBytes / 1024 / 1024, 2) . ' MB' : 'unknown';

$acceptRanges = (isset($headers['Accept-Ranges']) && stripos($headers['Accept-Ranges'], 'bytes') !== false);
$finalFilename = sanitize_filename($customName ?: basename(parse_url($url, PHP_URL_PATH)) ?: 'downloaded_file');

if (!$confirm) {
    echo <<<HTML
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>Confirm Download</title>
<style>
  body{font-family:sans-serif;padding:2em;}
  button{padding:0.5em 1em;margin-top:1em;}
</style>
</head><body>
<h2>Confirm Remote Download</h2>
<p><strong>URL:</strong> {$url}</p>
<p><strong>Type:</strong> {$type}</p>
<p><strong>Size:</strong> {$sizeReadable}</p>
<p><strong>Resume Supported:</strong> {$acceptRanges}</p>
<p><strong>Filename:</strong> {$finalFilename}</p>
<form method="POST">
  <input type="hidden" name="url" value="{$url}" />
  <input type="hidden" name="filename" value="{$finalFilename}" />
  <input type="hidden" name="confirm" value="1" />
  <button type="submit">Start Download</button>
</form>
<footer style="margin-top:3em; font-size:0.8em; color:#666; text-align:center;">
  <hr>
  <p>🔗 <a href="https://github.com/BaseMax/RemoteDownloaderPHP" target="_blank" rel="noopener noreferrer">RemoteDownloaderPHP on GitHub</a></p>
</footer>
</body></html>
HTML;
    exit;
}

// === INITIATE DOWNLOAD TO SERVER ===
log_message("Download started from $url as $finalFilename");

$filePath = DOWNLOAD_DIR . '/' . $finalFilename;
$fp = fopen($filePath, 'wb');
if (!$fp) {
    echo "❌ Cannot open file for writing: $filePath";
    log_message("Cannot open file for writing: $filePath");
    exit;
}

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_RETURNTRANSFER => false,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FILE => $fp,
    CURLOPT_BUFFERSIZE => 1024 * 1024,
    CURLOPT_USERAGENT => USER_AGENT,
    CURLOPT_FAILONERROR => true
]);

curl_exec($ch);

if (curl_errno($ch)) {
    $err = curl_error($ch);
    log_message("❌ cURL error: $err");
    fclose($fp);

    $incompletePath = DOWNLOAD_DIR . '/not-complete-' . basename($filePath);
    if (file_exists($filePath)) {
        rename($filePath, $incompletePath);
        log_message("Renamed incomplete file to: " . basename($incompletePath));
    }

    echo "❌ Download failed: $err";
    curl_close($ch);
    exit;
} else {
    log_message("✅ Download completed: $finalFilename");
}

curl_close($ch);
fclose($fp);

// === SHOW SUCCESS PAGE ===
echo <<<HTML
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>Download Complete</title>
<style>
  body{font-family:sans-serif;padding:2em;}
  a.button {
    display: inline-block; padding: 0.5em 1em; margin-top: 1em;
    background: #28a745; color: white; text-decoration: none; border-radius: 4px;
  }
</style>
</head><body>
<h2>Download Completed Successfully</h2>
<p>File saved on server as: <strong>{$finalFilename}</strong></p>
<p><a href="downloads/{$finalFilename}" class="button" target="_blank" rel="noopener noreferrer">Download file from server</a></p>
<footer style="margin-top:3em; font-size:0.8em; color:#666; text-align:center;">
  <hr>
  <p>🔗 <a href="https://github.com/BaseMax/RemoteDownloaderPHP" target="_blank" rel="noopener noreferrer">RemoteDownloaderPHP on GitHub</a></p>
</footer>
</body></html>
HTML;

exit;
