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
        executeQuery("DELETE FROM extension_numbers WHERE extension_id=$id;");
        header("Location: " . basename($_SERVER['PHP_SELF']));
        break;
    case 'edit':
    case 'add':

        $id = 0;
        $ext_number = '';
        $ext_name = '';

        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            if ($mode == 'edit') {
                if (!isset($_POST['id']) || empty($_POST['id'])) {
                    array_push($errorList, "id is not set!");
                } else {
                    $id = (int) $_POST['id'];
                }
            }

            // check values for errors!
            if (!isset($_POST['ext_number']) || empty($_POST['ext_number'])) {
                array_push($errorList, "ext number is required!");
            } elseif (!is_numeric($_POST['ext_number'])) {
                array_push($errorList, "ext number must be a number!");
            } elseif (strlen($_POST['ext_number']) > 3) {
                array_push($errorList, "ext number is too big, maximum digits are 3!");
            } elseif (strlen($_POST['ext_number']) < 3) {
                array_push($errorList, "ext number is too short, minimum digits are 3!");
            } else {
                $ext_number = (int) trim($_POST['ext_number']);
            }
            
            if (!isset($_POST['ext_name']) || empty($_POST['ext_name'])) {
                array_push($errorList, "Extension Name is required!");
            } elseif (strlen($_POST['ext_name']) > 40) {
                array_push($errorList, "Extension Name is too long, maximum length is 40!");
            } elseif (!is_string($_POST['ext_name'])) {
                array_push($errorList, "Extension Name can only be numbers and/or letters!");
            } else {
                $ext_name = trim($_POST['ext_name']);
            }


            if ($mode == 'edit') { // post edit
                $name = trim($_POST['ext_number']);
                if (getValue("SELECT extension_id FROM extension_numbers WHERE extension_id <> $id AND extension_no='$ext_number'")) {
                    array_push($errorList, "ext number is already used!");
                }

                if (count($errorList) == 0) {
                    $query = "UPDATE extension_numbers SET "
                            . "extension_no=$ext_number ,"
                            . "extension_name = '$ext_name' "
                            . "WHERE extension_id=$id;";

                    if (executeQuery($query)) {
                        header("Location: " . basename($_SERVER['PHP_SELF']));
                    } else {
                        array_push($errorList, "SQL error" . $lastSQLError);
                    }
                }
            } else { // post add
                $name = trim($_POST['ext_number']);
                if (getValue("SELECT extension_id FROM extension_numbers WHERE extension_no='$ext_number'")) {
                    array_push($errorList, "ext number is already used!");
                }

                if (count($errorList) == 0) {
                    $query = "INSERT INTO extension_numbers("
                            . "extension_no, extension_name) VALUES("
                            . "$ext_number, '$ext_name');";

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

            $ext_number = $_POST['ext_number'];
            $ext_name = $_POST['ext_name'];
        } else {

            // -- [ GET request
            
            $editmode = ($mode == 'edit' ? 1 : 0);
            $id = isset($_GET['id']) ? $_GET['id'] : 0;
            
            $extRow = getRow("SELECT * FROM extension_numbers WHERE extension_id=$id;");
            $ext_number = ($editmode ? $extRow["extension_no"] : "");
            $ext_name = ($editmode ? $extRow["extension_name"] : "");
        }
}
