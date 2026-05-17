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
        executeQuery("DELETE FROM kiosks WHERE kiosk_id=$id;");
        header("Location: " . basename($_SERVER['PHP_SELF']));
        break;
    case 'edit':
    case 'add':

        $id = 0;
        $kioskName = '';
        $printerType = '';
        $printerLocation = '';
        $printerParam = '';
        $kioskZone = 0;

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
                array_push($errorList, "Kiosk Name is required!");
            } elseif (strlen($_POST['name']) > 20) {
                array_push($errorList, "Kiosk Name is too long, maximum length is 20!");
            } elseif (!is_string($_POST['name'])) {
                array_push($errorList, "Kiosk Name can only be numbers and/or letters!");
            } else {
                $kioskName = trim($_POST['name']);
            }

//                    if (!isset($_POST['printertype']) || empty($_POST['printertype'])) {
//                        array_push($errorList, "Printer type is required!");
//                    } elseif (strlen($_POST['printertype']) > 20) {
//                        array_push($errorList, "Printer type is too long, maximum length is 20!");
//                    } elseif (!is_string($_POST['name'])) {
//                        array_push($errorList, "Printer type can only be numbers and/or letters!");
//                    } else {
//                        $printerType = trim($_POST['printertype']);
//                    }

            $printerKey = trim($_POST['printertype']);
            if (!isset($printerKey)) {
                array_push($errorList, "Printer type is required!");
            } elseif (!key_exists((int) $printerKey, $KioskPrinterTypes)) {
                array_push($errorList, "Printer type is invalid!");
            } else {
                $printerTypeKey = (int) trim($_POST['printertype']);
                $printerType = $KioskPrinterTypes[$printerTypeKey];
            }

            if (!isset($_POST['printerlocation']) || empty($_POST['printerlocation'])) {
                array_push($errorList, "Printer location is required!");
            } elseif (strlen($_POST['printerlocation']) > 40) {
                array_push($errorList, "Printer location is too long, maximum length is 40!");
            } elseif (!is_string($_POST['printerlocation'])) {
                array_push($errorList, "Printer location can only be numbers and/or letters!");
            } else {
                $printerLocation = trim($_POST['printerlocation']);
            }

            if (!isset($_POST['printerparam']) || empty($_POST['printerparam'])) {
                array_push($errorList, "Printer parameters is required!");
            } elseif (strlen($_POST['printerparam']) > 40) {
                array_push($errorList, "Printer parameters is too long, maximum length is 40!");
            } elseif (!is_string($_POST['printerparam'])) {
                array_push($errorList, "Printer parameters can only be numbers and/or letters!");
            } else {
                $printerParam = trim($_POST['printerparam']);
            }

            if (!isset($_POST['zone']) || empty($_POST['zone'])) {
                array_push($errorList, "zone is required!");
            } elseif (!getValue("SELECT zone_id FROM zones WHERE zone_id=" . trim($_POST['zone']))) {
                array_push($errorList, "zone does NOT exist!");
            } else {
                $kioskZone = (int) trim($_POST['zone']);
            }

            if ($mode == 'edit') { // post edit
                $name = trim($_POST['name']);
                if (getValue("SELECT kiosk_id FROM kiosks WHERE kiosk_id <> $id AND kiosk_name='$name'")) {
                    array_push($errorList, "Kiosk Name is already used!");
                }

                if (count($errorList) == 0) {
                    $query = "UPDATE kiosks SET "
                            . "kiosk_name='$kioskName',"
                            . "kiosk_printer_type='$printerType',"
                            . "kiosk_printer_location='$printerLocation',"
                            . "kiosk_printer_parameters='$printerParam',"
                            . "kiosk_zone=$kioskZone "
                            . "WHERE kiosk_id=$id;";

                    if (executeQuery($query)) {
                        header("Location: " . basename($_SERVER['PHP_SELF']));
                    } else {
                        array_push($errorList, "SQL error" . $lastSQLError);
                    }
                }
            } else { // post add
                $name = trim($_POST['name']);
                if (getValue("SELECT kiosk_id FROM kiosks WHERE kiosk_name='$name'")) {
                    array_push($errorList, "Kiosk Name is already used!");
                }

                if (count($errorList) == 0) {
                    $query = "INSERT INTO kiosks ("
                            . "kiosk_name,"
                            . "kiosk_printer_type,"
                            . "kiosk_printer_location,"
                            . "kiosk_printer_parameters,"
                            . "kiosk_zone,"
                            . "kiosk_updated) VALUES("
                            . "'$kioskName',"
                            . "'$printerType',"
                            . "'$printerLocation',"
                            . "'$printerParam',"
                            . "$kioskZone,"
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

            $kioskName = $_POST['name'];

            $printerTypeKey = (int) trim($_POST['printertype']);
            $printerType = $KioskPrinterTypes[$printerTypeKey];

            $printerLocation = $_POST['printerlocation'];
            $printerParam = $_POST['printerparam'];
            $kioskZone = $_POST['zone'];
        } else {

            // -- [ GET request
            $editmode = ($mode == 'edit' ? 1 : 0);
            $id = isset($_GET['id']) ? $_GET['id'] : 0;
            $kiosks = getRow("SELECT * FROM kiosks WHERE kiosk_id=$id;");
            $kioskName = ($editmode ? $kiosks['kiosk_name'] : '');

            $printerTypeName = ($editmode ? $kiosks['kiosk_printer_type'] : '');
            $printerType = (int) array_search($printerTypeName, $KioskPrinterTypes);

            $printerLocation = ($editmode ? $kiosks['kiosk_printer_location'] : '');
            $printerParam = ($editmode ? $kiosks['kiosk_printer_parameters'] : '');
            $kioskZone = ($editmode ? $kiosks['kiosk_zone'] : '');
        }
}
