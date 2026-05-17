<?php

$errorList = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mode = (isset($_POST['mode']) ? $_POST['mode'] : 'list');
} else {
    $mode = (isset($_GET['mode']) ? $_GET['mode'] : 'list');
}

switch ($mode) {
    case 'delete':
        $id = $_GET['id'];
        executeQuery("DELETE FROM zones WHERE zone_id=$id;");
        header("Location: " . basename($_SERVER['PHP_SELF']));
        break;
    case 'edit':
    case 'add':

        $id = 0;
        $zoneName = '';
        $zoneDesc = '';

        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            if ($mode == 'edit') {
                if (!isset($_POST['id']) || empty($_POST['id'])) {
                    array_push($errorList, "id is not set!");
                } else {
                    $id = (int) $_POST['id'];
                }
            }

            // check values for errors!
            if (!isset($_POST['name']) || empty($_POST['name'])) {
                array_push($errorList, "Zone Name is required!");
            } elseif (strlen($_POST['name']) > 20) {
                array_push($errorList, "Zone Name is too long, maximum length is 20!");
            } elseif (!is_string($_POST['name'])) {
                array_push($errorList, "Zone Name can only be numbers and/or letters!");
            } else {
                $zoneName = trim($_POST['name']);
            }

            if (strlen($_POST['desc']) > 40) {
                array_push($errorList, "Description is too long, maximum length is 40!");
            } else {
                $zoneDesc = trim($_POST['desc']);
            }

            if ($mode == 'edit') { // post edit
                $name = trim($_POST['name']);
                if (getValue("SELECT zone_id FROM zones WHERE zone_id <> $id AND zone_name='$name'")) {
                    array_push($errorList, "Zone Name is already used!");
                }

                if (count($errorList) == 0) {
                    $query = "UPDATE zones SET "
                            . "zone_name='$zoneName',"
                            . "zone_desc='$zoneDesc' "
                            . "WHERE zone_id=$id;";

                    if (executeQuery($query)) {
                        header("Location: " . basename($_SERVER['PHP_SELF']));
                    } else {
                        array_push($errorList, "SQL error" . $lastSQLError);
                    }
                }
            } else { // post add
                $name = trim($_POST['name']);
                if (getValue("SELECT zone_id FROM zones WHERE zone_name='$name'")) {
                    array_push($errorList, "Zone Name is already used!");
                }

                if (count($errorList) == 0) {
                    $query = "INSERT INTO zones("
                            . "zone_name,"
                            . "zone_desc) VALUES("
                            . "'$zoneName',"
                            . "'$zoneDesc');";

                    if (executeQuery($query)) {
                        header("Location: " . basename($_SERVER['PHP_SELF']));
                    } else {
                        array_push($errorList, "SQL error" . $lastSQLError);
                    }
                }
            }
            //wrong values
            $editmode = ($mode == 'edit' ? 1 : 0);
            $id = isset($_POST['id']) ? $_POST['id'] : 0;

            $zoneName = $_POST['name'];
            $zoneDesc = $_POST['desc'];
        } else {

            // -- [ GET request
            $editmode = ($mode == 'edit' ? 1 : 0);
            $id = isset($_GET['id']) ? $_GET['id'] : 0;
            $zoneName = ($editmode ? getValue("SELECT zone_name FROM zones WHERE zone_id=$id;") : "");
            $zoneDesc = ($editmode ? getValue("SELECT zone_desc FROM zones WHERE zone_id=$id;") : "");
        }
}
