<?php
/**
 * Admin Panel Initialization
 * Include this file at the start of every admin page
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include router for URL auto-fixing
require_once(__DIR__ . '/../../router.php');

// Include language and configuration
require_once(__DIR__ . '/../../language.php');

// Check authentication
if (!isset($_SESSION['username'])) {
    // Save the intended destination
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    
    // Redirect to login
    $loginUrl = adminUrl('account/login.php');
    header("Location: $loginUrl");
    exit();
}

// Set common variables
$username = $_SESSION['username'];

// Language handling
$languages = getColumn("SELECT DISTINCT text_language FROM texts;");
$lang = getSetting('defaultLanguage');

if (isset($_GET['language'])) {
    $lang = $_GET['language'];
    $_SESSION['language'] = $lang;
} elseif (isset($_SESSION['language'])) {
    $lang = $_SESSION['language'];
}

// Set paths
$filesPath = defined('FILES_PATH') ? FILES_PATH : "../files";
$cssPath = defined('CSS_PATH') ? CSS_PATH : '../css';
$jsPath = defined('JS_PATH') ? JS_PATH : '../js';

// Get text direction
$dir = trim(getTextValue('dir', $lang));

// Set default title if not already set
if (!isset($title)) {
    $title = 'Admin Panel';
}

?>
