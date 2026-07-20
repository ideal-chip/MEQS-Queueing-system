<?php
/**
 * Router for PHP's built-in development server (php -S ... router.php).
 *
 * The app was designed to live under /beaa and relies on Apache's
 * .htaccess to redirect convenience URLs like /feedback -> /beaa/feedback/.
 * The built-in dev server never reads .htaccess, so without this router
 * those short URLs silently fall through to the docroot's index.php
 * (the marketing home page) instead of the intended /beaa page. This
 * router restores that redirect behavior for local/dev serving.
 */

$uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$docroot = __DIR__;
$fsPath = $docroot . $uri;

// Apache's mod_dir auto-appends a trailing slash to directory requests so
// relative asset links (../css/...) resolve correctly; the built-in server
// does not, so do it here.
if ($uri !== '/' && substr($uri, -1) !== '/' && is_dir($fsPath)
    && (is_file("$fsPath/index.php") || is_file("$fsPath/index.html"))) {
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    header('Location: ' . $uri . '/' . ($qs !== '' ? '?' . $qs : ''), true, 302);
    exit;
}

// Real files/directories: let the built-in server handle them as usual.
if ($uri !== '/' && (is_file($fsPath) || is_dir($fsPath))) {
    return false;
}

// Legacy short paths that actually live under /beaa/ (mirrors .htaccess +
// beaa/router.php's Router::autoFix() path list).
$shortcuts = ['admin', 'api', 'counter', 'files', 'css', 'js', 'display', 'bigdisplay', 'feedback', 'audio', 'bulkcall'];
$segment = trim($uri, '/');
$first = strtok($segment, '/');
if ($first !== false && in_array($first, $shortcuts, true)) {
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    header('Location: /beaa/' . $segment . ($qs !== '' ? '?' . $qs : ''), true, 302);
    exit;
}

// A missing static asset (has a recognizable file extension: .css, .png, ...)
// must 404, not silently render the home page — otherwise broken links to
// missing CSS/JS/images look like they "work" (HTTP 200) while actually
// serving the wrong content, hiding the real problem.
$ext = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
$staticExts = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'webp',
    'woff', 'woff2', 'ttf', 'eot', 'map', 'json', 'mp3', 'wav', 'txt', 'pdf'];
if ($ext !== '' && in_array($ext, $staticExts, true)) {
    http_response_code(404);
    header('Content-Type: text/plain');
    echo "404 Not Found: $uri";
    exit;
}

// No match and it looks like a page/route request: fall back to the home
// page (same default the built-in server already used for unmatched paths).
require $docroot . '/index.php';
