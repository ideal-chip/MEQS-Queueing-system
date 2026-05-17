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
        executeQuery("DELETE FROM bigdisplayscounters WHERE bdc_id=$id;");
        header("Location: " . basename($_SERVER['PHP_SELF']));
        break;
    case 'edit':
    case 'add':

        $id = 0;
        $bigdisplay = '';
        $counter = '';

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
            if (!isset($_POST['bigdisplay']) || empty($_POST['bigdisplay'])) {
                array_push($errorList, "bigdisplay name is required!");
            } elseif (!getValue("SELECT display_id FROM bigdisplays WHERE display_id=" . trim($_POST['bigdisplay']))) {
                array_push($errorList, "bigdisplay does NOT exist!");
            } else {
                $bigdisplay = (int) trim($_POST['bigdisplay']);
            }

            if (!isset($_POST['counter']) || empty($_POST['counter'])) {
                array_push($errorList, "counter is required!");
            } elseif (!getValue("SELECT counter_id FROM counters WHERE counter_id=" . trim($_POST['counter']))) {
                array_push($errorList, "counter does NOT exist!");
            } else {
                $counter = (int) trim($_POST['counter']);
            }

            if ($mode == 'edit') { // post edit
                if (getValue("SELECT count(*) FROM bigdisplayscounters WHERE bdc_id <> $id AND bdc_counter=" . trim($_POST['counter']) . " AND bdc_bigdisplay=" . trim($_POST['bigdisplay'])) > 0) {
                    array_push($errorList, "You can't add the same counter for a bigdisplay twice!");
                }

                if (count($errorList) == 0) {
                    $query = "UPDATE bigdisplayscounters SET bdc_bigdisplay=$bigdisplay,bdc_counter=$counter WHERE bdc_id=$id;";
                    if (executeQuery($query)) {
                        header("Location: " . basename($_SERVER['PHP_SELF']));
                    } else {
                        array_push($errorList, "SQL error" . $lastSQLError);
                    }
                }
            } else { // post add
                if (getValue("SELECT count(*) FROM bigdisplayscounters WHERE bdc_counter=" . trim($_POST['counter']) . " AND bdc_bigdisplay=" . trim($_POST['bigdisplay'])) > 0) {
                    array_push($errorList, "You can't add the same counter for a bigdisplay twice!");
                }

                if (count($errorList) == 0) {
                    $queryAdd = "INSERT INTO bigdisplayscounters(bdc_bigdisplay,bdc_counter) VALUES($bigdisplay,$counter);";
                    if (executeQuery($queryAdd)) {
                        header("Location: " . basename($_SERVER['PHP_SELF']));
                    } else {
                        array_push($errorList, "SQL error" . $lastSQLError);
                    }
                }
            }
            //wrong values
            $editmode = ($mode == 'edit' ? 1 : 0);
            $id = isset($_POST['id']) ? $_POST['id'] : 0;
            $bigdisplay = $_POST['bigdisplay'];
            $counter = $_POST['counter'];
        } else {

            // -- [ GET request
            $editmode = ($mode == 'edit' ? 1 : 0);
            $id = isset($_GET['id']) ? $_GET['id'] : 0;
            $bdc = getRow("SELECT * FROM bigdisplayscounters WHERE bdc_id=$id;");
            $bigdisplay = ($editmode ? $bdc['bdc_bigdisplay'] : '');
            $counter = ($editmode ? $bdc['bdc_counter'] : '');
        }
}
