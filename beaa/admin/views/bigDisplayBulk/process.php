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
        executeQuery("DELETE FROM bigdisplayservices WHERE bds_id=$id;");
        header("Location: " . basename($_SERVER['PHP_SELF']));
        break;
    case 'edit':
    case 'add':

        $id = 0;
        $bigdisplay = 0;
        $qty = 0;
        $priority = 0;
        $category = 0;

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

            if (!isset($_POST['qty']) || empty($_POST['qty'])) {
                array_push($errorList, "quantity is required!");
            } elseif (strlen($_POST['qty']) > 2) {
                array_push($errorList, "quantity is too big, maximum digits are 2!");
            } elseif (!is_numeric($_POST['qty']) || (int) $_POST['qty'] < 0) {
                array_push($errorList, "quantity must be a positive number!");
            } else {
                $qty = (int) trim($_POST['qty']);
            }

            if (!isset($_POST['priority']) || empty($_POST['priority'])) {
                array_push($errorList, "priority is required!");
            } elseif (strlen($_POST['priority']) > 2) {
                array_push($errorList, "priority is too big, maximum digits are 2!");
            } elseif (!is_numeric($_POST['priority']) || (int) $_POST['priority'] < 0) {
                array_push($errorList, "priority must be a positive number!");
            } else {
                $priority = (int) trim($_POST['priority']);
            }

            if (!isset($_POST['category']) || empty($_POST['category'])) {
                array_push($errorList, "category is required!");
            } elseif (!getValue("SELECT category_id FROM categories WHERE category_id=" . trim($_POST['category']))) {
                array_push($errorList, "category does NOT exist!");
            } else {
                $category = (int) trim($_POST['category']);
            }

            $maxBulk = (int) getSetting("maxBulkNumber");
            $totalQty = (int) getValue("SELECT SUM(qty) FROM bigdisplayservices WHERE bd_id = $bigdisplay;");
            $oldQty = (int) getValue("SELECT qty FROM bigdisplayservices WHERE bds_id = $id;");
            $newQty = $totalQty - $oldQty + $qty;
            $allowedQty = $maxBulk - ($totalQty - $oldQty);

//                    $_SESSION['tq'] = $newQty;
            if ($newQty > $maxBulk) {
                $lastError = "Total allowed: 50, " . getTextValue('numberOftickets', $lang) . " " . getTextValue('errorBulkNumber', $lang) . " $allowedQty.";
                array_push($errorList, $lastError);
            }
//                    var_dump($id);
//                    var_dump($qty);
//                    var_dump($maxBulk);
//                    var_dump($totalQty);
//                    var_dump($oldQty);
//                    var_dump($newQty);
//                    var_dump($errorList);

            if ($mode == 'edit') { // post edit
                if (getValue("SELECT count(*) FROM bigdisplayservices WHERE bds_id <> $id AND category_id =" . trim($_POST['category']) . " AND bd_id=" . trim($_POST['bigdisplay'])) > 0) {
                    array_push($errorList, "You can't add the same category for a bigdisplay twice!");
                }

                if (count($errorList) == 0) {
                    $queryUpdate = "UPDATE bigdisplayservices SET bd_id=$bigdisplay,category_id=$category, qty=$qty ,priority=$priority  WHERE bds_id=$id;";
                    if (executeQuery($queryUpdate)) {
                        header("Location: " . basename($_SERVER['PHP_SELF']));
                    } else {
                        array_push($errorList, "SQL error" . $lastSQLError);
                    }
                }
            } else { // post add
                if (getValue("SELECT count(*) FROM bigdisplayservices WHERE category_id =" . trim($_POST['category']) . " AND bd_id=" . trim($_POST['bigdisplay'])) > 0) {
                    array_push($errorList, "You can't add the same category for a bigdisplay twice!");
                }

                if (count($errorList) == 0) {
                    $queryAdd = "INSERT INTO bigdisplayservices(bd_id,category_id, qty, priority) VALUES($bigdisplay,$category, $qty, $priority);";
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
            $qty = $_POST['qty'];
            $priority = $_POST['priority'];
            $category = $_POST['category'];
        } else {

            // -- [ GET request
            $editmode = ($mode == 'edit' ? 1 : 0);
            $id = isset($_GET['id']) ? $_GET['id'] : 0;
            $bds = getRow("SELECT * FROM bigdisplayservices WHERE bds_id=$id;");
            $bigdisplay = ($editmode ? $bds['bd_id'] : '');
            $category = ($editmode ? $bds['category_id'] : '');
            $qty = ($editmode ? $bds['qty'] : '');
            $priority = ($editmode ? $bds['priority'] : '');
        }
}
