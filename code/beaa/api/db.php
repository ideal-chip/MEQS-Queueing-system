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

loadEnvFile(dirname(__DIR__, 3) . '/.env');

function envValue($key, $default = '') {
    $value = getenv($key);
    return $value === false ? $default : $value;
}

//-------------------------------------< global mysqli method >----------

define('DB_SERVER', envValue('DB_HOST', 'localhost'));
define('DB_PORT', (int) envValue('DB_PORT', '3306'));
define('DB_USERNAME', envValue('DB_USER', 'project_demo_user'));
define('DB_PASSWORD', envValue('DB_PASSWORD', 'ProjectDemo@12345'));
define('DB_NAME', envValue('DB_NAME', 'project_demo_db'));

/* Attempt to connect to MySQL database */

$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
// Check connection
if($mysqli === false){
    die("ERROR: Could not connect. " . $mysqli->connect_error);
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
            $result = $row[0];
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
