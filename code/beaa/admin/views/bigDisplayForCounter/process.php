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
        executeQuery("DELETE FROM bigdisplayforcounter WHERE id=$id;");
        header("Location: " . basename($_SERVER['PHP_SELF']));
        break;
    case 'edit':
    case 'add':

        $id = 0;
        $displayID = 0;
        $CounterID = 0;
        $quantity = 0;

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
            if (!isset($_POST['bdid']) || empty($_POST['bdid'])) {
                array_push($errorList, "bigdisplay name is required!");
            } elseif (!getValue("SELECT display_id FROM bigdisplays WHERE display_id=" . trim($_POST['bdid']))) {
                array_push($errorList, "bigdisplay does NOT exist!");
            } else {
                $displayID = (int) trim($_POST['bdid']);
            }

            if (!isset($_POST['counterid']) || empty($_POST['counterid'])) {
                array_push($errorList, "counter is required!");
            } elseif (!getValue("SELECT counter_id FROM counters WHERE counter_id=" . trim($_POST['counterid']))) {
                array_push($errorList, "counter does NOT exist!");
            } else {
                $CounterID = (int) trim($_POST['counterid']);
            }

            if (!isset($_POST['quantity']) || empty($_POST['quantity'])) {
                array_push($errorList, "quantity is required!");
            } elseif (strlen($_POST['quantity']) > 2) {
                array_push($errorList, "quantity is too big, maximum digits are 2!");
            } elseif (!is_numeric($_POST['quantity']) || (int) $_POST['quantity'] < 0) {
                array_push($errorList, "quantity must be a positive number!");
            } elseif ((int) trim($_POST['quantity']) > 10) {
                array_push($errorList, "quantity must be less or equal to 10!");
            } else {
                $quantity = (int) trim($_POST['quantity']);
            }

            if ($mode == 'edit') { // post edit
                $bigdisplayID = trim($_POST['bdid']);
                if (getValue("SELECT id FROM bigdisplayforcounter WHERE id<>$id AND bd_id=$bigdisplayID;")) {
                    array_push($errorList, "Big Display already used!");
                }

                if (getValue("SELECT count(*) FROM bigdisplayforcounter WHERE id<>$id AND counter_id =" . trim($_POST['counterid']) . " AND bd_id=" . trim($_POST['bdid'])) > 0) {
                    array_push($errorList, "You can't add the same counter for a bigdisplay twice!");
                }

                if (count($errorList) == 0) {
                    $query = "UPDATE bigdisplayforcounter SET bd_id=$displayID,counter_id=$CounterID, quantity=$quantity WHERE id=$id;";
                    if (executeQuery($query)) {
                        header("Location: " . basename($_SERVER['PHP_SELF']));
                    } else {
                        array_push($errorList, "SQL error" . $lastSQLError);
                    }
                }
            } else { // post add
                $bigdisplayID = trim($_POST['bdid']);
                if (getValue("SELECT id FROM bigdisplayforcounter WHERE bd_id=$bigdisplayID;")) {
                    array_push($errorList, "Big Display already used!");
                }

                if (getValue("SELECT count(*) FROM bigdisplayforcounter WHERE counter_id=" . trim($_POST['counterid']) . " AND bd_id=" . trim($_POST['bdid'])) > 0) {
                    array_push($errorList, "You can't add the same counter for a bigdisplay twice!");
                }

                if (count($errorList) == 0) {
                    $query = "INSERT INTO bigdisplayforcounter (bd_id, counter_id,quantity) VALUES($displayID, $CounterID,$quantity);";
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
            $displayID = $_POST['bdid'];
            $CounterID = $_POST['counterid'];
            $quantity = $_POST['quantity'];
        } else {

            // -- [ GET request
            $editmode = ($mode == 'edit' ? 1 : 0);
            $id = isset($_GET['id']) ? $_GET['id'] : 0;
            $displays = getRow("SELECT * FROM bigdisplayforcounter WHERE id=$id;");
            $displayID = ($editmode ? $displays['bd_id'] : '');
            $CounterID = ($editmode ? $displays['counter_id'] : '');
            $quantity = ($editmode ? $displays['quantity'] : '');
        }
}
