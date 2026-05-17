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
        executeQuery("DELETE FROM counters WHERE counter_id=$id;");
        executeQuery("DELETE FROM countercategories WHERE cc_counter=$id;");
        executeQuery("DELETE FROM bigdisplayscounters WHERE bdc_counter=$id;");
        executeQuery("DELETE FROM bigdisplayforcounter WHERE counter_id=$id;");
        header("Location: " . basename($_SERVER['PHP_SELF']));
        break;
    case 'edit':
    case 'add':

        $id = 0;
        $counterName = '';
        $counterNo = 0;
        $counterDisplay = 0;
        $counterAudio = 0;
        $counterZone = 0;
        $counterCategoryDirect = 0;
        $counterPicker = (isset($_POST['counter-picker']) ? 1 : 0);

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
                array_push($errorList, "Counter Name is required!");
            } elseif (strlen($_POST['name']) > 20) {
                array_push($errorList, "Counter Name is too long, maximum length is 20!");
            } elseif (!is_string($_POST['name'])) {
                array_push($errorList, "Counter Name can only be numbers and/or letters!");
            } else {
                $counterName = trim($_POST['name']);
            }

            if (!isset($_POST['no']) || empty($_POST['no'])) {
                array_push($errorList, "Counter Number is required!");
            } elseif (strlen($_POST['no']) > 2) {
                array_push($errorList, "Counter Number is too big, maximum digits are 2!");
            } elseif (!is_numeric($_POST['no']) || (int) $_POST['no'] < 0) {
                array_push($errorList, "Counter Number must be a positive number!");
            } else {
                $counterNo = (int) trim($_POST['no']);
            }

            if (!isset($_POST['display']) || empty($_POST['display'])) {
                array_push($errorList, "display is required!");
            } elseif (!getValue("SELECT display_id FROM displays WHERE display_id=" . trim($_POST['display']))) {
                array_push($errorList, "display does NOT exist!");
            } else {
                $counterDisplay = (int) trim($_POST['display']);
            }

            if (!isset($_POST['audio']) || empty($_POST['audio'])) {
                array_push($errorList, "audio is required!");
            } elseif (!getValue("SELECT audio_id FROM audios WHERE audio_id=" . trim($_POST['audio']))) {
                array_push($errorList, "audio does NOT exist!");
            } else {
                $counterAudio = (int) trim($_POST['audio']);
            }

            if (!isset($_POST['zone']) || empty($_POST['zone'])) {
                array_push($errorList, "zone is required!");
            } elseif (!getValue("SELECT zone_id FROM zones WHERE zone_id=" . trim($_POST['zone']))) {
                array_push($errorList, "zone does NOT exist!");
            } else {
                $counterZone = (int) trim($_POST['zone']);
            }

//                   
            if (!is_numeric($_POST['transferdirect']) || (int) $_POST['transferdirect'] < 0) {
                array_push($errorList, "Direct Transfer Category must be a positive number!");
            } elseif ((int) $_POST['transferdirect'] != 0 && !getValue("SELECT category_id FROM categories WHERE category_id=" . trim($_POST['transferdirect']))) {
                array_push($errorList, "Category does NOT exist!");
            } else {
                $counterCategoryDirect = (int) trim($_POST['transferdirect']);
            }

            if ($mode == 'edit') { // post edit
                $name = trim($_POST['name']);
                if (getValue("SELECT counter_id FROM counters WHERE counter_id <> $id AND counter_name='$name'")) {
                    array_push($errorList, "Counter Name is already used!");
                }

                $number = trim($_POST['no']);
                if (getValue("SELECT counter_id FROM counters WHERE counter_id <> $id AND counter_no=$number")) {
                    array_push($errorList, "Counter Number [$number] is already used!");
                }

                $display = trim($_POST['display']);
                if (getValue("SELECT counter_id FROM counters WHERE counter_id <> $id AND counter_display=$display")) {
                    array_push($errorList, "Counter Display [$display] is already used!");
                }

                if (count($errorList) == 0) {

                    $query = "UPDATE counters SET "
                            . "counter_name='$counterName',"
                            . "counter_no=$counterNo,"
                            . "counter_display=$counterDisplay,"
                            . "counter_audio=$counterAudio,"
                            . "counter_zone=$counterZone,"
                            . "direct_transfer_category=$counterCategoryDirect,"
                            . "can_pick_tickets=$counterPicker  "
                            . "WHERE counter_id=$id;";

                    if (executeQuery($query)) {
                        header("Location: " . basename($_SERVER['PHP_SELF']));
                    } else {
                        array_push($errorList, "SQL error" . $lastSQLError);
                    }
                }
            } else { // post add
                $name = trim($_POST['name']);
                if (getValue("SELECT counter_id FROM counters WHERE counter_name='$name'")) {
                    array_push($errorList, "Counter Name is already used!");
                }

                $number = trim($_POST['no']);
                if (getValue("SELECT counter_id FROM counters WHERE counter_no=$number")) {
                    array_push($errorList, "Counter Number [$number] is already used!");
                }

                $display = trim($_POST['display']);
                if (getValue("SELECT counter_id FROM counters WHERE counter_display=$display")) {
                    array_push($errorList, "Counter Display [$display] is already used!");
                }

                if (count($errorList) == 0) {

                    $query = "INSERT INTO counters ("
                            . "counter_name,"
                            . "counter_no,"
                            . "counter_display,"
                            . "counter_audio,"
                            . "counter_zone,"
                            . "direct_transfer_category,"
                            . "can_pick_tickets) "
                            . "VALUES("
                            . "'$counterName',"
                            . "$counterNo,"
                            . "$counterDisplay,"
                            . "$counterAudio,"
                            . "$counterZone,"
                            . "$counterCategoryDirect,"
                            . "$counterPicker);";

                    if (executeMultiQuery("$query")) {
                        header("Location: " . basename($_SERVER['PHP_SELF']));
                    } else {
                        array_push($errorList, "SQL error" . $lastSQLError);
                    }
                }
            }
            //wrong values
            $editmode = ($mode == 'edit' ? 1 : 0);
            $id = isset($_POST['id']) ? $_POST['id'] : 0;

            $counterName = $_POST['name'];
            $counterNo = $_POST['no'];
            $counterDisplay = $_POST['display'];
            $counterAudio = $_POST['audio'];
            $counterZone = $_POST['zone'];
            $counterCategoryDirect = $_POST['transferdirect'];
            $counterPicker = (isset($_POST['counter-picker']) ? 1 : 0);
        } else {

            // -- [ GET request
            $editmode = ($mode == 'edit' ? 1 : 0);
            $id = isset($_GET['id']) ? $_GET['id'] : 0;
            $counters = getRow("SELECT * FROM counters WHERE counter_id=$id;");
            $counterName = ($editmode ? $counters['counter_name'] : '');
            $counterNo = ($editmode ? $counters['counter_no'] : '');
            $counterDisplay = ($editmode ? $counters['counter_display'] : '');
            $counterAudio = ($editmode ? $counters['counter_audio'] : '');
            $counterZone = ($editmode ? $counters['counter_zone'] : '');

            $counterCategoryDirect = ($editmode ? $counters['direct_transfer_category'] : '');
            $counterPicker = ($editmode ? $counters['can_pick_tickets'] : '');
        }
}
