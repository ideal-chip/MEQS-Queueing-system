<?php

$errorList = array();
//defualts
$id = 0;
$kbPriority = 0;
$kbKiosk = 0;
$kbCategory = 0;

//defualts-always
$kbLevel = 0;
$kbTheme = '';
$kbThemeParameters = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mode = (isset($_POST['mode']) ? $_POST['mode'] : 'list');
} else {
    $mode = (isset($_GET['mode']) ? $_GET['mode'] : 'list');
}

switch ($mode) {
    case 'delete':
        $id = $_GET['id'];
        executeQuery("DELETE FROM kioskbuttons WHERE kb_id=$id;");
        header("Location: " . basename($_SERVER['PHP_SELF']));
        break;
    case 'edit':
    case 'add':

        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            if ($mode == 'edit') {
                if (!isset($_POST['id']) || empty($_POST['id'])) {
                    array_push($errorList, "id is not set!");
                } else {
                    $id = (int) $_POST['id'];
                }
            }

            // check values for errors!
            if (!isset($_POST['kiosk']) || empty($_POST['kiosk'])) {
                array_push($errorList, "kiosk is required!");
            } elseif (!getValue("SELECT kiosk_id FROM kiosks WHERE kiosk_id=" . trim($_POST['kiosk']))) {
                array_push($errorList, "kiosk does NOT exist!");
            } else {
                $kbKiosk = (int) trim($_POST['kiosk']);
            }

            if (!isset($_POST['category']) || empty($_POST['category'])) {
                array_push($errorList, "Category is required!");
            } elseif (!getValue("SELECT category_id FROM categories WHERE category_id=" . trim($_POST['category']))) {
                array_push($errorList, "Category does NOT exist!");
            } else {
                $kbCategory = (int) trim($_POST['category']);
            }

            if (!isset($_POST['priority'])) {
                array_push($errorList, "Priority is required!");
            } else {
                $kbPriority = (int) trim($_POST['priority']);
            }

//                    if (strlen($_POST['theme']) > 20) {
//                        array_push($errorList, "Theme is too long, maximum length is 20!");
//                    } else {
//                        $kbTheme = trim($_POST['theme']);
//                    }
//
//                    if (strlen($_POST['param']) > 40) {
//                        array_push($errorList, "Parameters is too long, maximum length is 40!");
//                    } else {
//                        $kbThemeParameters = trim($_POST['param']);
//                    }

            if ($mode == 'edit') { // post edit
                $val = getValue("SELECT COUNT(*) FROM kioskbuttons WHERE kb_id <> $id AND kb_kiosk = $kbKiosk AND kb_category=$kbCategory;");
                if ($val > 0) {
                    array_push($errorList, "You can't add the same category for a kiosk twice!");
                }

                if (count($errorList) == 0) {
                    $query = "UPDATE kioskbuttons SET "
                            . "kb_kiosk='$kbKiosk',"
                            . "kb_category=$kbCategory,"
                            . "kb_level=$kbLevel,"
                            . "kb_priority=$kbPriority,"
                            . "kb_theme='$kbTheme',"
                            . "kb_theme_parameters='$kbThemeParameters' "
                            . "WHERE kb_id=$id;";

                    if (executeQuery($query)) {
                        header("Location: " . basename($_SERVER['PHP_SELF']));
                    } else {
                        array_push($errorList, "SQL error" . $lastSQLError);
                    }
                }
            } else { // post add
                $val = getValue("SELECT COUNT(*) FROM kioskbuttons WHERE kb_kiosk = $kbKiosk AND kb_category=$kbCategory;");
                if ($val > 0) {
                    array_push($errorList, "You can't add the same category for a kiosk twice!");
                }

                if (count($errorList) == 0) {
                    $query = "INSERT INTO kioskbuttons ("
                            . "kb_kiosk,kb_category,"
                            . "kb_level,kb_priority,"
                            . "kb_theme,kb_theme_parameters) VALUES("
                            . "$kbKiosk,"
                            . "$kbCategory,"
                            . "$kbLevel,"
                            . "$kbPriority,"
                            . "'$kbTheme',"
                            . "'$kbThemeParameters');";

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

            $kbKiosk = $_POST['kiosk'];
            $kbCategory = $_POST['category'];
            $kbPriority = 0;

            $kbLevel = 0;
//            $kbTheme = $_POST['theme'];
//            $kbThemeParameters = $_POST['param'];
        } else {

            // -- [ GET request
            $editmode = ($mode == 'edit' ? 1 : 0);
            $id = isset($_GET['id']) ? $_GET['id'] : 0;
            $kioskButtons = getRow("SELECT * FROM kioskbuttons WHERE kb_id=$id;");

            $kbKiosk = ($editmode ? $kioskButtons['kb_kiosk'] : '');
            $kbCategory = ($editmode ? $kioskButtons['kb_category'] : '');
            $kbPriority = ($editmode ? $kioskButtons['kb_priority'] : '');

            $kbLevel = ($editmode ? $kioskButtons['kb_level'] : '');
//            $kbTheme = ($editmode ? $kioskButtons['kb_theme'] : '');
//            $kbThemeParameters = ($editmode ? $kioskButtons['kb_theme_parameters'] : '');
        }
}