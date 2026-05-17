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
        executeQuery("DELETE FROM displays WHERE display_id=$id;");
        // delete related items
        header("Location: " . basename($_SERVER['PHP_SELF']));
        break;
    case 'edit':
    case 'add':

        $id = 0;
        $displayName = '';
        $displayZone = 0;

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $errorList = array();

            if ($mode == 'edit') {
                if (!isset($_POST['id']) || empty($_POST['id'])) {
                    array_push($errorList, "id is not set!");
                } else {
                    $id = (int) $_POST['id'];
                }
            }

            // check values for errors!
            if (!isset($_POST['name']) || empty($_POST['name'])) {
                array_push($errorList, "Display Name is required!");
            } elseif (strlen($_POST['name']) > 40) {
                array_push($errorList, "Display Name is too long, maximum length is 40!");
            } elseif (!is_string($_POST['name'])) {
                array_push($errorList, "Display Name can only be numbers and/or letters!");
            } else {
                $displayName = trim($_POST['name']);
            }

            if (!isset($_POST['zone']) || empty($_POST['zone'])) {
                array_push($errorList, "zone is required!");
            } elseif (!getValue("SELECT zone_id FROM zones WHERE zone_id=" . trim($_POST['zone']))) {
                array_push($errorList, "zone does NOT exist!");
            } else {
                $displayZone = (int) trim($_POST['zone']);
            }

            if ($mode == 'edit') { // post edit
                $name = trim($_POST['name']);
                if (getValue("SELECT display_id FROM displays WHERE display_id <> $id AND display_name='$name'")) {
                    array_push($errorList, "Display Name is already used!");
                }

                if (count($errorList) == 0) {
                    $query = "UPDATE displays SET "
                            . "display_name='$displayName',"
                            . "display_zone=$displayZone "
                            . "WHERE display_id=$id;";

                    if (executeQuery($query)) {
                        header("Location: " . basename($_SERVER['PHP_SELF']));
                    } else {
                        array_push($errorList, "SQL error" . $lastSQLError);
                    }
                }
            } else { // post add
                $name = trim($_POST['name']);
                if (getValue("SELECT display_id FROM displays WHERE display_name='$name'")) {
                    array_push($errorList, "Display Name is already used!");
                }

                if (count($errorList) == 0) {
                    $query = "INSERT INTO displays ("
                            . "display_name,"
                            . "display_zone,"
                            . "display_updated) "
                            . "VALUES("
                            . "'$displayName',"
                            . "$displayZone,"
                            . "0);";

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

            $displayName = $_POST['name'];
            $displayZone = $_POST['zone'];
        } else {

            // -- [ GET request
            $editmode = ($mode == 'edit' ? 1 : 0);
            $id = isset($_GET['id']) ? $_GET['id'] : 0;
            $displays = getRow("SELECT * FROM displays WHERE display_id=$id;");
            $displayName = ($editmode ? $displays['display_name'] : '');
            $displayZone = ($editmode ? $displays['display_zone'] : '');
        }
}