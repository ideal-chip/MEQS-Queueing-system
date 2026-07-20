<?php
/**
 * iDEAL-Q REST API v1 -- front controller.
 *
 * Every /beaa/api/v1/... request is routed here by router.php (dev server)
 * / .htaccess (Apache), with the remaining path in $_SERVER['API_V1_PATH'].
 * All responses are JSON: {"success":bool,"data":..,"meta":..,"error":..}.
 *
 * Public endpoints (used by the Flutter app -- no auth, rate-limited):
 *   GET  /feedback/form
 *   GET  /counters/{id}/feedback/form
 *   POST /feedback/submissions
 *   POST /counters/{id}/feedback/submissions
 *   GET  /counters?feedback_enabled=1
 *
 * Admin endpoints (session auth required -- same login as /beaa/admin/):
 *   GET  /admin/feedback/summary
 *   GET  /admin/feedback/submissions
 */

error_reporting(0);
require_once __DIR__ . '/../../language.php';

global $mysqli;

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

//====================================================================  | response helpers

function apiRespond($status, $data = null, $meta = null, $error = null) {
    http_response_code($status);
    echo json_encode([
        'success' => $error === null,
        'data' => $data,
        'meta' => $meta,
        'error' => $error,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function apiError($status, $code, $message) {
    apiRespond($status, null, null, ['code' => $code, 'message' => $message]);
}

function jsonBody() {
    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

//====================================================================  | basic rate limiting (per IP, per endpoint, file-based)

function rateLimit($bucket, $maxRequests = 20, $windowSeconds = 60) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $dir = sys_get_temp_dir() . '/idealq_ratelimit';
    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }
    $file = $dir . '/' . preg_replace('/[^a-zA-Z0-9_.]/', '_', $bucket . '_' . $ip);
    $now = time();
    $hits = [];
    if (is_file($file)) {
        $hits = json_decode(file_get_contents($file), true) ?: [];
    }
    $hits = array_values(array_filter($hits, function ($t) use ($now, $windowSeconds) {
        return $t > $now - $windowSeconds;
    }));
    if (count($hits) >= $maxRequests) {
        apiError(429, 'rate_limited', 'Too many requests. Please try again shortly.');
    }
    $hits[] = $now;
    @file_put_contents($file, json_encode($hits));
}

//====================================================================  | counter helpers

function findCounter($mysqli, $counterId) {
    $stmt = $mysqli->prepare(
        "SELECT counter_id, counter_name, counter_no, counter_zone,
            (SELECT zone_name FROM zones WHERE zone_id = counters.counter_zone) AS zone_name
         FROM counters WHERE counter_id = ?"
    );
    $stmt->bind_param('i', $counterId);
    $stmt->execute();
    $stmt->bind_result($id, $name, $no, $zoneId, $zoneName);
    $found = $stmt->fetch();
    $stmt->close();
    if (!$found) {
        return null;
    }
    return [
        'counter_id' => (int) $id,
        'counter_name' => $name,
        'counter_number' => (int) $no,
        'zone_id' => (int) $zoneId,
        'zone_name' => $zoneName,
    ];
}

function feedbackQuestions($mysqli, $lang) {
    $lang = in_array($lang, ['ar', 'en'], true) ? $lang : 'en';
    $stmt = $mysqli->prepare(
        "SELECT text_key, text_value FROM texts
         WHERE text_key IN ('fb0','fb1','fb2','fb3','fb4') AND text_language = ?
         ORDER BY text_key"
    );
    $stmt->bind_param('s', $lang);
    $stmt->execute();
    $stmt->bind_result($key, $value);
    $out = [];
    while ($stmt->fetch()) {
        $out[] = ['key' => $key, 'label' => $value];
    }
    $stmt->close();
    return $out;
}

function submitFeedback($mysqli, $counterId, $body) {
    $lang = isset($body['language']) && in_array($body['language'], ['ar', 'en'], true) ? $body['language'] : null;
    $ratings = isset($body['ratings']) && is_array($body['ratings']) ? $body['ratings'] : [];
    $note = isset($body['note']) ? substr((string) $body['note'], 0, 1000) : '';

    $fb = [];
    for ($i = 0; $i < 5; $i++) {
        $v = isset($ratings['fb' . $i]) ? (int) $ratings['fb' . $i] : null;
        $fb[$i] = ($v >= 1 && $v <= 5) ? $v : null;
    }
    $rated = array_filter($fb, function ($v) { return $v !== null; });
    if (count($rated) === 0) {
        apiError(422, 'validation_error', 'At least one rating (1-5) is required.');
    }
    $avg = round(array_sum($rated) / count($rated), 2);

    $scope = 'global';
    $counterIdParam = null;
    $counterName = null;
    $counterNo = null;
    $counterZone = null;

    if ($counterId !== null) {
        $counter = findCounter($mysqli, $counterId);
        if (!$counter) {
            apiError(404, 'counter_not_found', 'The requested counter does not exist.');
        }
        $scope = 'counter';
        $counterIdParam = $counter['counter_id'];
        $counterName = $counter['counter_name'];
        $counterNo = $counter['counter_number'];
        $counterZone = $counter['zone_name'];
    }

    $stmt = $mysqli->prepare(
        "INSERT INTO feedback
            (feedback_scope, counter_id, counter_name_snapshot, counter_number_snapshot, counter_zone_snapshot,
             feedback_language, fb0, fb1, fb2, fb3, fb4, feedback_score, feedback_note, feedback_date)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
    );
    $stmt->bind_param(
        'sisissiiiiids',
        $scope, $counterIdParam, $counterName, $counterNo, $counterZone, $lang,
        $fb[0], $fb[1], $fb[2], $fb[3], $fb[4], $avg, $note
    );
    $ok = $stmt->execute();
    $newId = $mysqli->insert_id;
    $stmt->close();

    if (!$ok) {
        apiError(500, 'server_error', 'Could not save feedback.');
    }
    setSetting('feedbackUpdated', '1');

    apiRespond(201, [
        'feedback_id' => $newId,
        'scope' => $scope,
        'counter_id' => $counterIdParam,
        'average_score' => $avg,
    ]);
}

//====================================================================  | routing

$path = rtrim($_SERVER['API_V1_PATH'] ?? '', '/');
$method = $_SERVER['REQUEST_METHOD'];

// GET /feedback/form
if ($method === 'GET' && $path === '/feedback/form') {
    $lang = $_GET['language'] ?? getSetting('defaultLanguage');
    apiRespond(200, [
        'scope' => 'global',
        'language' => $lang,
        'questions' => feedbackQuestions($mysqli, $lang),
    ]);
}

// GET /counters/{id}/feedback/form
if ($method === 'GET' && preg_match('#^/counters/(\d+)/feedback/form$#', $path, $m)) {
    $counter = findCounter($mysqli, (int) $m[1]);
    if (!$counter) {
        apiError(404, 'counter_not_found', 'The requested counter does not exist.');
    }
    $lang = $_GET['language'] ?? getSetting('defaultLanguage');
    apiRespond(200, [
        'scope' => 'counter',
        'counter' => $counter,
        'language' => $lang,
        'questions' => feedbackQuestions($mysqli, $lang),
    ]);
}

// POST /feedback/submissions
if ($method === 'POST' && $path === '/feedback/submissions') {
    rateLimit('feedback_submit', 20, 60);
    submitFeedback($mysqli, null, jsonBody());
}

// POST /counters/{id}/feedback/submissions
if ($method === 'POST' && preg_match('#^/counters/(\d+)/feedback/submissions$#', $path, $m)) {
    rateLimit('feedback_submit', 20, 60);
    submitFeedback($mysqli, (int) $m[1], jsonBody());
}

// GET /counters?feedback_enabled=1
if ($method === 'GET' && $path === '/counters') {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = $protocol . '://' . $host . '/beaa';

    $rows = $mysqli->query(
        "SELECT c.counter_id, c.counter_name, c.counter_no, c.counter_active, c.counter_zone,
                z.zone_name
         FROM counters c LEFT JOIN zones z ON z.zone_id = c.counter_zone
         ORDER BY c.counter_name"
    );
    $out = [];
    if ($rows) {
        while ($row = $rows->fetch_assoc()) {
            $out[] = [
                'counter_id' => (int) $row['counter_id'],
                'counter_name' => $row['counter_name'],
                'counter_number' => (int) $row['counter_no'],
                'zone_id' => (int) $row['counter_zone'],
                'zone_name' => $row['zone_name'],
                'active' => (bool) $row['counter_active'],
                'feedback_url' => $base . '/feedback/' . $row['counter_id'] . '/',
            ];
        }
    }
    apiRespond(200, $out, ['total' => count($out)]);
}

//====================================================================  | admin endpoints (session-protected)

function requireAdmin() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['username'])) {
        apiError(401, 'unauthorized', 'Admin login required.');
    }
}

if ($method === 'GET' && $path === '/admin/feedback/summary') {
    requireAdmin();
    $from = $_GET['from'] ?? date('Y-m-d');
    $to = $_GET['to'] ?? date('Y-m-d');
    $stmt = $mysqli->prepare(
        "SELECT feedback_scope, COUNT(*) total, ROUND(AVG(feedback_score), 2) avg_score
         FROM feedback WHERE DATE(feedback_date) BETWEEN ? AND ? GROUP BY feedback_scope"
    );
    $stmt->bind_param('ss', $from, $to);
    $stmt->execute();
    $stmt->bind_result($scope, $total, $avgScore);
    $summary = ['global' => ['total' => 0, 'avg_score' => null], 'counter' => ['total' => 0, 'avg_score' => null]];
    while ($stmt->fetch()) {
        $summary[$scope] = ['total' => (int) $total, 'avg_score' => $avgScore !== null ? (float) $avgScore : null];
    }
    $stmt->close();
    apiRespond(200, $summary, ['from' => $from, 'to' => $to]);
}

if ($method === 'GET' && $path === '/admin/feedback/submissions') {
    requireAdmin();
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = min(100, max(1, (int) ($_GET['per_page'] ?? 20)));
    $offset = ($page - 1) * $perPage;
    $scope = in_array($_GET['scope'] ?? '', ['global', 'counter'], true) ? $_GET['scope'] : null;

    $where = '1=1';
    if ($scope !== null) {
        $where .= " AND feedback_scope = '" . $mysqli->real_escape_string($scope) . "'";
    }
    $total = (int) $mysqli->query("SELECT COUNT(*) c FROM feedback WHERE $where")->fetch_assoc()['c'];

    $stmt = $mysqli->prepare(
        "SELECT feedback_id, feedback_scope, counter_id, counter_name_snapshot, feedback_language,
                fb0, fb1, fb2, fb3, fb4, feedback_score, feedback_note, feedback_date
         FROM feedback WHERE $where ORDER BY feedback_date DESC LIMIT ? OFFSET ?"
    );
    $stmt->bind_param('ii', $perPage, $offset);
    $stmt->execute();
    $stmt->bind_result($id, $fscope, $cid, $cname, $lang, $fb0, $fb1, $fb2, $fb3, $fb4, $score, $note, $date);
    $rows = [];
    while ($stmt->fetch()) {
        $rows[] = [
            'feedback_id' => (int) $id, 'scope' => $fscope, 'counter_id' => $cid, 'counter_name' => $cname,
            'language' => $lang, 'ratings' => ['fb0' => $fb0, 'fb1' => $fb1, 'fb2' => $fb2, 'fb3' => $fb3, 'fb4' => $fb4],
            'score' => $score !== null ? (float) $score : null, 'note' => $note, 'date' => $date,
        ];
    }
    $stmt->close();
    apiRespond(200, $rows, [
        'current_page' => $page, 'per_page' => $perPage, 'total' => $total,
        'total_pages' => (int) ceil($total / $perPage),
    ]);
}

apiError(404, 'not_found', 'Unknown API endpoint: ' . $method . ' ' . $path);
