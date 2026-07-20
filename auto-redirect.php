<?php
/**
 * Auto-redirect script
 * Include this at the top of files that need URL fixing
 */

// Get the current request URI
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];

// Check if we're NOT in /beaa/ path but should be
if (strpos($requestUri, '/beaa/') === false) {
    // Extract the relative path
    $paths = ['/admin/', '/api/', '/counter/', '/files/', '/css/', '/js/'];
    
    foreach ($paths as $path) {
        if (strpos($requestUri, $path) === 0) {
            // Redirect to correct path
            $correctUrl = '/beaa' . $requestUri;
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $correctUrl);
            exit;
        }
    }
}

?>
