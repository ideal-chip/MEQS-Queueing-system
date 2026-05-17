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

        executeQuery("DELETE FROM bigdisplays WHERE display_id=$id;");
        executeQuery("DELETE FROM bigdisplayscounters WHERE bdc_bigdisplay=$id;");
        executeQuery("DELETE FROM bigdisplayservices WHERE bd_id=$id;");
        executeQuery("DELETE FROM bigdisplayforcounter WHERE bd_id=$id;");

        header("Location: " . basename($_SERVER['PHP_SELF']));
        break;
    case 'edit':
    case 'add':

        $id = 0;
        $displayName = '';
        $displayZone = 0;
        $displayNumber = 0;
        $displayType = 0;
        $gotoPlace = '';
        $arrowDir = 0;

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            
            if ($mode == 'edit') {
                if (!isset($_POST['id']) || empty($_POST['id'])) {
                    array_push($errorList, "id is not set!");
                } else {
                    $id = (int) $_POST['id'];
                }
            }

            // check values for errors!
            if (!isset($_POST['name']) || empty($_POST['name'])) {
                array_push($errorList, "bigdisplay name is required!");
            } elseif (strlen($_POST['name']) > 40) {
                array_push($errorList, "bigdisplay name is too long, maximum length is 40!");
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

            if (!isset($_POST['displaynumber']) || empty($_POST['displaynumber'])) {
                array_push($errorList, "display number is required!");
            } elseif (strlen($_POST['displaynumber']) > 2) {
                array_push($errorList, "display number is too big, maximum digits are 2!");
            } elseif (!is_numeric($_POST['displaynumber']) || (int) $_POST['displaynumber'] < 0) {
                array_push($errorList, "display number must be a positive number !");
            } else {
                $displayNumber = (int) trim($_POST['displaynumber']);
            }

            if (!isset($_POST['displaytype']) || empty($_POST['displaytype'])) {
                array_push($errorList, "display type is required!");
            } elseif (!getValue("SELECT bdtype_id FROM bigdisplaytypes WHERE bdtype_id=" . trim($_POST['displaytype']))) {
                array_push($errorList, "display type does NOT exist!");
            } else {
                $displayType = (int) trim($_POST['displaytype']);
            }

            if (strlen($_POST['goto']) > 40) {
                array_push($errorList, "goto place is too long, maximum length is 40!");
            } else {
                $gotoPlace = trim($_POST['goto']);
            }

            if (getArrowDirection($_POST['arrowDir']) == 'ERROR') {
                array_push($errorList, "Invalid arrow direction! - " . $_POST['arrowDir']);
                var_dump($_POST['arrowDir']);
            } else {
                $arrowDir = (int) trim($_POST['arrowDir']);
            }

            if ($mode == 'edit') { // post edit
                $name = trim($_POST['name']);
                if (getValue("SELECT display_name FROM bigdisplays WHERE display_id <> $id AND display_name='$name'")) {
                    array_push($errorList, "Display name is already used!");
                }

                if (getValue("SELECT count(*) FROM bigdisplays WHERE display_id <> $id AND display_number=" . trim($_POST['displaynumber'])) > 0) {
                    array_push($errorList, "You can't add the same display number twice!");
                }

                if (count($errorList) == 0) {
                    $query = "UPDATE bigdisplays 
                                     SET display_name='$displayName', 
                                        display_zone=$displayZone, 
                                        display_number=$displayNumber, 
                                        display_type=$displayType, 
                                        display_updated=1, 
                                        goto= '$gotoPlace', 
                                        arrow_dir = $arrowDir
                                     WHERE display_id=$id;";
                    if (executeQuery($query)) {
                        header("Location: " . basename($_SERVER['PHP_SELF']));
                    } else {
                        array_push($errorList, "SQL error" . $lastSQLError);
                    }
                }
            } else { // post add
                $name = trim($_POST['name']);
                if (getValue("SELECT display_id FROM bigdisplays WHERE display_name='$name'")) {
                    array_push($errorList, "Display name is already used!");
                }
                if (getValue("SELECT count(*) FROM bigdisplays WHERE display_number=" . trim($_POST['displaynumber'])) > 0) {
                    array_push($errorList, "You can't add the same display number twice!");
                }

                if (count($errorList) == 0) {
                    $query = "INSERT INTO bigdisplays 
                                        (display_number, 
                                        display_name, 
                                        display_zone, 
                                        display_updated, 
                                        display_type, 
                                        goto,
                                        arrow_dir)  
                                    VALUES
                                        ($displayNumber, 
                                        '$displayName', 
                                        $displayZone,0, 
                                        $displayType,  
                                        '$gotoPlace',
                                        $arrowDir);";

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
            $displayNumber = $_POST['displaynumber'];
            $displayType = $_POST['displaytype'];
            $gotoPlace = $_POST['goto'];
            $arrowDir = $_POST['arrowDir'];
        } else {

            // -- [ GET request
            $editmode = ($mode == 'edit' ? 1 : 0);
            $id = isset($_GET['id']) ? $_GET['id'] : 0;
            $displays = getRow("SELECT * FROM bigdisplays WHERE display_id=$id;");
            $displayName = ($editmode ? $displays['display_name'] : '');
            $displayZone = ($editmode ? $displays['display_zone'] : '');
            $displayNumber = ($editmode ? $displays['display_number'] : '');
            $displayType = ($editmode ? $displays['display_type'] : '');
            $gotoPlace = ($editmode ? $displays['goto'] : '');
            $arrowDir = ($editmode ? $displays['arrow_dir'] : '');
        }
}
