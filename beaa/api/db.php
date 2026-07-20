<?php
//-------------------------------------< environment >----------

function loadEnvFile($path) {
    if (!is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if ($key !== '' && getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

loadEnvFile(dirname(__DIR__, 2) . '/.env');

function envValue($key, $default = '') {
    $value = getenv($key);
    return $value === false ? $default : $value;
}

//-------------------------------------< global mysqli method >----------

// DB_ENGINE is a documentation/intent flag checked below after connecting;
// it does not select a driver -- this app only ever speaks mysqli.
define('DB_ENGINE', envValue('DB_ENGINE', 'mysql'));
define('DB_SERVER', envValue('DB_HOST', '127.0.0.1'));
define('DB_PORT', (int) envValue('DB_PORT', '3306'));
define('DB_USERNAME', envValue('DB_USER', 'project_demo_user'));
define('DB_PASSWORD', envValue('DB_PASSWORD', ''));
define('DB_NAME', envValue('DB_NAME', 'project_demo_db'));

if (DB_ENGINE !== 'mysql') {
    die("FATAL: DB_ENGINE must be 'mysql'. This application only supports Oracle MySQL Community Server.");
}
if (DB_PASSWORD === '') {
    die("FATAL: DB_PASSWORD is not set. Configure .env (see .env.example) -- there is no built-in default credential.");
}

/*
 * PHP 8.1 changed the default mysqli error reporting mode to
 * MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT, which throws a
 * mysqli_sql_exception on any failed query/connection. This code base was
 * written for the classic (PHP 7) behaviour where a failing query returns
 * false/null and the error text is read from $conn->error. Restore that mode
 * so an isolated bad query degrades gracefully instead of aborting the whole
 * page. (See getRow()/getValue()/executeQuery() below, which all rely on it.)
 */
mysqli_report(MYSQLI_REPORT_OFF);

/* Attempt to connect to the database */

$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
if ($mysqli->connect_errno) {
    die("ERROR: Could not connect. " . $mysqli->connect_error);
}

/*
 * This application must only ever run against Oracle MySQL Community
 * Server. mysqli happily speaks the wire protocol to MariaDB and other
 * forks too, so the driver alone can't tell them apart -- checking
 * @@version_comment (and the absence of "MariaDB" in VERSION()) is the
 * standard way to positively identify the real Oracle build.
 */
$engineCheck = $mysqli->query("SELECT VERSION() AS v, @@version_comment AS c");
$engineRow = $engineCheck ? $engineCheck->fetch_assoc() : null;
if (!$engineRow || stripos($engineRow['v'], 'mariadb') !== false || stripos($engineRow['c'], 'mariadb') !== false) {
    die("FATAL: This application requires Oracle MySQL Community Server 8.4 LTS. "
        . "The connected server does not identify as MySQL (it may be MariaDB or another fork). Refusing to start.");
}

//-------------------------------------< connection method >----------
$dbhost = DB_SERVER;
$dbusername = DB_USERNAME;
$dbpassword = DB_PASSWORD;
$dbname = DB_NAME;
$lastID = 0;
$lastSQLError = "";

function getRow($query) {
    global $dbhost, $dbusername, $dbpassword, $dbname, $lastSQLError;
    $conn = new mysqli($dbhost, $dbusername, $dbpassword, $dbname, DB_PORT);
    if (!$conn->errno) {
        $conn->set_charset("utf8");
        if ($qr = $conn->query($query)) {
            $row = $qr->fetch_array(MYSQLI_ASSOC);
            $result = $row;
        } else {
            $result = null;
            $lastSQLError = $conn->error;
        }
        $conn->close();
    }
    return $result;
}

function getValue($query) {
    global $dbhost, $dbusername, $dbpassword, $dbname, $lastSQLError;
    $conn = new mysqli($dbhost, $dbusername, $dbpassword, $dbname, DB_PORT);
    if (!$conn->errno) {
        $conn->set_charset("utf8");
        if ($qr = $conn->query($query)) {
            $row = $qr->fetch_array(MYSQLI_NUM);
            $result = $row ? $row[0] : null;
        } else {
            $result = null;
            $lastSQLError = $conn->error;
        }
        $conn->close();
    }
    return $result;
}

function getColumn($query) {
    global $dbhost, $dbusername, $dbpassword, $dbname, $lastSQLError;
    $retArray = Array();
    $conn = new mysqli($dbhost, $dbusername, $dbpassword, $dbname, DB_PORT);
    if (!$conn->errno) {
        $conn->set_charset("utf8");
        if ($qr = $conn->query($query)) {
            while ($row = $qr->fetch_array(MYSQLI_NUM))
                $retArray[] = $row[0];
        } else {
            $retArray = null;
            $lastSQLError = $conn->error;
        }
        $conn->close();
    }
    return $retArray;
}

function executeQuery($query) {
    global $dbhost, $dbusername, $dbpassword, $dbname, $lastID, $lastSQLError;
    $conn = new mysqli($dbhost, $dbusername, $dbpassword, $dbname, DB_PORT);
    if (!$conn->errno) {
        $conn->set_charset("utf8");
        $result = $conn->query($query);
        $lastID = $conn->insert_id;
        $conn->close();
    } else {
        $lastSQLError = $conn->error;
    }
    return $result;
}

function executeMultiQuery($query) {
    global $dbhost, $dbusername, $dbpassword, $dbname, $lastID, $lastSQLError;
    $conn = new mysqli($dbhost, $dbusername, $dbpassword, $dbname, DB_PORT);
    if (!$conn->errno) {
        $conn->set_charset("utf8");
        $result = $conn->multi_query($query);
        $conn->close();
    } else {
        $lastSQLError = $conn->error;
    }
    return $result;
}

function getArray($query) {
    global $dbhost, $dbusername, $dbpassword, $dbname, $lastID, $lastSQLError;
    $conn = new mysqli($dbhost, $dbusername, $dbpassword, $dbname, DB_PORT);
    
    $resultArray = array();
    if (!$conn->errno) {
        $conn->set_charset("utf8");
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_array($result)) {
            array_push($resultArray, $row);
        }
        $conn->close();
    } else {
        $lastSQLError = $conn->error;
    }
    return $resultArray;
}

function getArrayAssoc($query) {
    global $dbhost, $dbusername, $dbpassword, $dbname, $lastID, $lastSQLError;
    $conn = new mysqli($dbhost, $dbusername, $dbpassword, $dbname, DB_PORT);
    
    $resultArray = array();
    if (!$conn->errno) {
        $conn->set_charset("utf8");
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($resultArray, $row);
        }
        $conn->close();
    } else {
        $lastSQLError = $conn->error;
    }
    return $resultArray;
}

function getKeyValArray($array, $key, $value) {
    if ($array) {
        $temp = array();
        foreach ($array as $row) {
            $temp[$row[$key]] = $row[$value];
        }
        return $temp;
    }
    return 0;
}



?>
