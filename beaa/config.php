<?php
/**
 * iDEAL-Q System Configuration
 * Dynamic Base Path Configuration
 */

// Automatically detect the base path
function getBasePath() {
    // Always return /beaa for this project
    // The application files are in /beaa directory
    return '/beaa';
}

// Define constants for base paths
define('BASE_PATH', getBasePath());
define('ADMIN_BASE_PATH', BASE_PATH . '/admin');
define('API_BASE_PATH', BASE_PATH . '/api');
define('COUNTER_BASE_PATH', BASE_PATH . '/counter');
define('FILES_PATH', BASE_PATH . '/files');
define('CSS_PATH', BASE_PATH . '/css');
define('JS_PATH', BASE_PATH . '/js');

// Full URL base
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
define('BASE_URL', $protocol . '://' . $host . BASE_PATH);
define('ADMIN_BASE_URL', BASE_URL . '/admin');

// For debugging (can be removed in production)
if (isset($_GET['debug_paths'])) {
    echo '<pre>';
    echo "BASE_PATH: " . BASE_PATH . "\n";
    echo "ADMIN_BASE_PATH: " . ADMIN_BASE_PATH . "\n";
    echo "API_BASE_PATH: " . API_BASE_PATH . "\n";
    echo "COUNTER_BASE_PATH: " . COUNTER_BASE_PATH . "\n";
    echo "BASE_URL: " . BASE_URL . "\n";
    echo "ADMIN_BASE_URL: " . ADMIN_BASE_URL . "\n";
    echo '</pre>';
}

/**
 * Generate URL with automatic base path
 */
function url($path = '') {
    $path = ltrim($path, '/');
    return BASE_PATH . ($path ? '/' . $path : '');
}

/**
 * Generate admin URL
 */
function adminUrl($path = '') {
    $path = ltrim($path, '/');
    return ADMIN_BASE_PATH . ($path ? '/' . $path : '');
}

/**
 * Generate API URL
 */
function apiUrl($path = '') {
    $path = ltrim($path, '/');
    return API_BASE_PATH . ($path ? '/' . $path : '');
}

/**
 * Generate asset URL (CSS, JS, Images)
 */
function asset($path) {
    $path = ltrim($path, '/');
    return BASE_PATH . '/' . $path;
}

/**
 * Redirect to URL
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Redirect to admin page
 */
function redirectToAdmin($page = '') {
    redirect(adminUrl($page));
}

/**
 * Check if current page matches
 */
function isCurrentPage($page) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    return $currentPage === $page;
}

?>
