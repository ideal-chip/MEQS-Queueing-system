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
        $catKey = getValue("SELECT category_key FROM categories WHERE category_id=$id;");
        executeMultiQuery("DELETE FROM categories WHERE category_id=$id;"
                . "DELETE FROM texts WHERE text_key='$catKey';"
                . "DELETE FROM bigdisplayservices WHERE category_id=$id;"
                . "DELETE FROM countercategories WHERE cc_category=$id; "
                . "DELETE FROM kioskbuttons WHERE kb_category=$id;"
                . "DELETE FROM subcategories WHERE main_category_id = $id;"
                . "UPDATE counters SET direct_transfer_category = 0 WHERE direct_transfer_category= $id;");
        header("Location: " . basename($_SERVER['PHP_SELF']));
        break;
    case 'edit':
    case 'add':

        $id = 0;
        $categoryKey = '';
        $serialNoRef = '';
        $categoryChar = '';
        $categoryParent = 0;
        $categoryZone = 0;
        $categoryEnabled = (isset($_POST['enabled']) ? 1 : 0);

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
            $trimmedKey = trim($_POST['key']);
            $CatKey = substr($trimmedKey, 0, 3);
            $CatNum = substr($trimmedKey, 3, 3);
            if (!isset($_POST['key']) || empty($_POST['key'])) {
                array_push($errorList, "Category Key is required!");
            } elseif (strlen($_POST['key']) > 6) {
                array_push($errorList, "Category Key is too long, maximum length is 6!");
            } elseif (!ctype_alnum($_POST['key'])) {
                array_push($errorList, "Category Key can only be numbers and/ or letters and have no spaces!");
            } elseif (strtoupper($CatKey) != "CAT" || !ctype_digit($CatNum)) {
                array_push($errorList, "Category Key must be in the format [CAT#], where # is a number");
            } else {
                $categoryKey = strtoupper($trimmedKey);
            }
//                    
//                    var_dump($trimmedKey);
//                    var_dump($CatKey);
//                    var_dump($CatNum);   

            $serialRefTrimmed = trim($_POST['serial-no-ref']);
            if (!isset($serialRefTrimmed) || empty($serialRefTrimmed)) {
                array_push($errorList, "Serial No. Ref is required!");
            } elseif (mb_strlen($serialRefTrimmed, 'UTF-8') != 1) {
                array_push($errorList, "Serial No. Ref should be one letter!");
            } elseif (!preg_match('/^[\p{Arabic}a-zA-Z\- .ـ]+$/u', $serialRefTrimmed)) {
                array_push($errorList, "Serial No. Ref can only be letters!");
            } else {
                $serialNoRef = strtoupper($serialRefTrimmed);
            }

            if (!isset($_POST['char']) || empty($_POST['char'])) {
                array_push($errorList, "Category Char is required!");
            } elseif (strlen($_POST['char']) != 1) {
                array_push($errorList, "category Char should be one letter!");
            } elseif (!ctype_alpha($_POST['char'])) {
                array_push($errorList, "Category Char can only be letters!");
            } else {
                $categoryChar = strtoupper(trim($_POST['char']));
            }

            if (!isset($_POST['zone']) || empty($_POST['zone'])) {
                array_push($errorList, "zone is required!");
            } elseif (!getValue("SELECT zone_id FROM zones WHERE zone_id=" . trim($_POST['zone']))) {
                array_push($errorList, "zone does NOT exist!");
            } else {
                $categoryZone = (int) trim($_POST['zone']);
            }

            if ($mode == 'edit') { // post edit
                $key = strtoupper(trim($_POST['key']));
                if (getValue("SELECT category_id FROM categories WHERE category_id <> $id AND category_key='$key'")) {
                    array_push($errorList, "Category key is already used!");
                }

                $char = strtoupper(trim($_POST['char']));
                if (getValue("SELECT category_id FROM categories WHERE category_id <> $id AND category_char='$char'")) {
                    array_push($errorList, "Category char is already used!");
                }

                $serialRefTrimmed = strtoupper(trim($_POST['serial-no-ref']));
                if (getValue("SELECT category_id FROM categories WHERE category_id <> $id AND serial_no_ref='$serialRefTrimmed'")) {
                    array_push($errorList, "Serial No. Ref is already used!");
                }

                if (count($errorList) == 0) {

                    $oldCatKey = getValue("SELECT `category_key` FROM `categories` WHERE `category_id` = $id;");
                    $query = "UPDATE categories "
                            . "SET category_key='$categoryKey',"
                            . "serial_no_ref='$serialNoRef',"
                            . "category_char='$categoryChar',"
                            . "category_parent=IF($categoryParent<>0,$categoryParent,NULL),"
                            . "category_zone=$categoryZone,"
                            . "category_enabled=$categoryEnabled "
                            . "WHERE category_id=$id;";

                    if (executeQuery($query)) {
                        executeQuery("UPDATE texts SET text_key = '$categoryKey' WHERE text_key = '$oldCatKey';");
                        header("Location: " . basename($_SERVER['PHP_SELF']));
                    } else {
                        array_push($errorList, "SQL error" . $lastSQLError);
                    }
                }
            } else { // post add
                $key = strtoupper(trim($_POST['key']));
                if (getValue("SELECT category_id FROM categories WHERE category_key='$key'")) {
                    array_push($errorList, "Category key is already used!");
                }

                $char = strtoupper(trim($_POST['char']));
                if (getValue("SELECT category_id FROM categories WHERE category_char='$char'")) {
                    array_push($errorList, "Category char is already used!");
                }

                $serialRefTrimmed = strtoupper(trim($_POST['serial-no-ref']));
                if (getValue("SELECT category_id FROM categories WHERE serial_no_ref='$serialRefTrimmed'")) {
                    array_push($errorList, "Serial No. Ref is already used!");
                }

                if (count($errorList) == 0) {

                    $catTextQry = "INSERT INTO texts (`text_language`, `text_key`, `text_value`) VALUES  
                                     ('ar', '$categoryKey', '$categoryKey'), 
                                     ('en', '$categoryKey', '$categoryKey')";
                    $catInsertQry = "INSERT INTO categories ("
                            . "category_key,"
                            . "serial_no_ref,"
                            . "category_char,"
                            . "category_parent,"
                            . "category_zone,"
                            . "category_enabled) "
                            . "VALUES('$categoryKey',"
                            . "'$serialNoRef',"
                            . "'$categoryChar',"
                            . "$categoryParent,"
                            . "$categoryZone,"
                            . "$categoryEnabled)";

                    if (executeMultiQuery("$catInsertQry;$catTextQry;")) {
                        header("Location: " . basename($_SERVER['PHP_SELF']));
                    } else {
                        array_push($errorList, "SQL error" . $lastSQLError);
                    }
                }
            }
            //wrong values
            $editmode = ($mode == 'edit' ? 1 : 0);
            $id = isset($_POST['id']) ? $_POST['id'] : 0;

            $categoryKey = $_POST['key'];
            $serialNoRef = $_POST['serial-no-ref'];
            $categoryChar = $_POST['char'];
            $categoryParent = $_POST['parent'];
            $categoryZone = $_POST['zone'];
            $categoryEnabled = (isset($_POST['enabled']) ? 1 : 0);
        } else {

            // -- [ GET request
            $editmode = ($mode == 'edit' ? 1 : 0);
            $id = isset($_GET['id']) ? $_GET['id'] : 0;
            $category = getRow("SELECT * FROM categories WHERE category_id=$id;");
            $categoryKey = ($editmode ? $category['category_key'] : '');
            $serialNoRef = ($editmode ? $category['serial_no_ref'] : '');
            $categoryChar = ($editmode ? $category['category_char'] : '');
            $categoryParent = ($editmode ? $category['category_parent'] : '');
            $categoryZone = ($editmode ? $category['category_zone'] : '');
            $categoryEnabled = ($editmode ? $category['category_enabled'] : '');
        }
}
