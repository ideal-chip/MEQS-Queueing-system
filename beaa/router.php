<?php
/**
 * iDEAL-Q Dynamic Router
 * Handles URL routing and redirects
 */

require_once __DIR__ . '/config.php';

class Router {
    private $routes = [];
    private $basePath = '/beaa';
    
    public function __construct() {
        // Set base path from config
        if (defined('BASE_PATH')) {
            $this->basePath = BASE_PATH;
        }
    }
    
    /**
     * Add a route
     */
    public function add($pattern, $handler) {
        $this->routes[$pattern] = $handler;
    }
    
    /**
     * Get current request URI
     */
    private function getCurrentUri() {
        $uri = $_SERVER['REQUEST_URI'];
        // Remove query string
        $uri = strtok($uri, '?');
        // Remove base path if present
        if (strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
        }
        return $uri ?: '/';
    }
    
    /**
     * Match current request to a route
     */
    public function match() {
        $uri = $this->getCurrentUri();
        
        // Direct match
        if (isset($this->routes[$uri])) {
            return $this->routes[$uri];
        }
        
        // Pattern match
        foreach ($this->routes as $pattern => $handler) {
            $pattern = str_replace('/', '\/', $pattern);
            $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_]+)', $pattern);
            
            if (preg_match('/^' . $pattern . '$/', $uri, $matches)) {
                array_shift($matches); // Remove full match
                return [$handler, $matches];
            }
        }
        
        return null;
    }
    
    /**
     * Dispatch the router
     */
    public function dispatch() {
        $match = $this->match();
        
        if (!$match) {
            return false;
        }
        
        if (is_array($match)) {
            list($handler, $params) = $match;
            if (is_callable($handler)) {
                return call_user_func_array($handler, $params);
            }
        } elseif (is_callable($match)) {
            return call_user_func($match);
        }
        
        return false;
    }
    
    /**
     * Auto-fix URLs - redirect old paths to new paths
     */
    public static function autoFix() {
        $uri = $_SERVER['REQUEST_URI'];
        $needsRedirect = false;
        $newUri = $uri;
        
        // List of paths that need /beaa/ prefix
        $pathsToFix = [
            '/admin/',
            '/api/',
            '/counter/',
            '/files/',
            '/css/',
            '/js/',
            '/display/',
            '/bigdisplay/',
            '/feedback/'
        ];
        
        foreach ($pathsToFix as $path) {
            if (strpos($uri, $path) === 0 && strpos($uri, '/beaa' . $path) !== 0) {
                $newUri = '/beaa' . $uri;
                $needsRedirect = true;
                break;
            }
        }
        
        if ($needsRedirect) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $newUri);
            exit;
        }
    }
}

// Auto-fix URLs if needed
if (php_sapi_name() !== 'cli') {
    Router::autoFix();
}

?>
