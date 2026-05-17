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
        executeQuery("DELETE FROM countercategories WHERE cc_id=$id;");
        header("Location: " . basename($_SERVER['PHP_SELF']));
        break;
    case 'edit':
    case 'add':

        $id = 0;
        $ccCounter = 0;
        $ccCategory = 0;

        // defualts
        $ccRequestedLevel = 0;
        $ccNextLevel = 1;

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
            if (!isset($_POST['counter']) || empty($_POST['counter'])) {
                array_push($errorList, "Counter is required!");
            } elseif (!getValue("SELECT counter_id FROM counters WHERE counter_id=" . trim($_POST['counter']))) {
                array_push($errorList, "Counter does NOT exist!");
            } else {
                $ccCounter = (int) trim($_POST['counter']);
            }

            if (!isset($_POST['category']) || empty($_POST['category'])) {
                array_push($errorList, "Category is required!");
            } elseif (!getValue("SELECT category_id FROM categories WHERE category_id=" . trim($_POST['category']))) {
                array_push($errorList, "Category does NOT exist!");
            } else {
                $ccCategory = (int) trim($_POST['category']);
            }

            if ($mode == 'edit') { // post edit
                $val = getValue("SELECT COUNT(*) FROM countercategories WHERE cc_id <> $id AND cc_counter = $ccCounter AND cc_category = $ccCategory;");
                if ($val > 0) {
                    array_push($errorList, "You can't add the same category for a counter twice!");
                }

                if (count($errorList) == 0) {
                    $query = "UPDATE countercategories SET "
                            . "cc_counter=$ccCounter,"
                            . "cc_category=$ccCategory,"
                            . "cc_requested_level=$ccRequestedLevel,"
                            . "cc_next_level=$ccNextLevel,"
                            . "cc_enabled=1 "
                            . "WHERE cc_id=$id;";

                    if (executeQuery($query)) {
                        header("Location: " . basename($_SERVER['PHP_SELF']));
                    } else {
                        array_push($errorList, "SQL error" . $lastSQLError);
                    }
                }
            } else { // post add
                if (getValue("SELECT COUNT(*) FROM countercategories WHERE cc_counter = $ccCounter AND cc_category = $ccCategory;") > 0) {
                    array_push($errorList, "You can't add the same category for a counter twice!");
                }

                if (count($errorList) == 0) {
                    $query = "INSERT INTO countercategories ("
                            . "cc_counter,"
                            . "cc_category,"
                            . "cc_requested_level,"
                            . "cc_next_level,"
                            . "cc_enabled) "
                            . "VALUES("
                            . "$ccCounter,"
                            . "$ccCategory,"
                            . "$ccRequestedLevel,"
                            . "$ccNextLevel,"
                            . "1);";

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

            $ccCounter = $_POST['counter'];
            $ccCategory = $_POST['category'];
            $ccRequestedLevel = 0;
            $ccNextLevel = 1;
        } else {

            // -- [ GET request
            $editmode = ($mode == 'edit' ? 1 : 0);
            $id = isset($_GET['id']) ? $_GET['id'] : 0;
            $counterCategories = getRow("SELECT * FROM countercategories WHERE cc_id=$id;");
            $ccCounter = ($editmode ? $counterCategories['cc_counter'] : '');
            $ccCategory = ($editmode ? $counterCategories['cc_category'] : '');
            $ccRequestedLevel = ($editmode ? $counterCategories['cc_requested_level'] : '');
            $ccNextLevel = ($editmode ? $counterCategories['cc_next_level'] : '');
        }
}
