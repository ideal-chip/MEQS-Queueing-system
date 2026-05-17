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
        executeQuery("DELETE FROM clerks WHERE clerk_id=$id;");
        header("Location: " . basename($_SERVER['PHP_SELF']));
        break;
    case 'edit':
    case 'add':

        $id = 0;
        $clerkName = '';
        $clerkPassword = '';
        $clerkFullName = '';
        $clerkDesc = '';
        $clerkPhone = '';
        $clerkZone = 0;

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
                array_push($errorList, "Clerk Name is required!");
            } elseif (strlen($_POST['name']) > 20) {
                array_push($errorList, "Clerk Name is too long, maximum length is 20!");
            } elseif (!ctype_alnum($_POST['name'])) {
                array_push($errorList, "Clerk Name can only be numbers and/or letters - no spaces!");
            } else {
                $clerkName = trim($_POST['name']);
            }

            if (!isset($_POST['password']) || empty($_POST['password'])) {
                array_push($errorList, "Password is required!");
            } else {
                $clerkPassword = trim($_POST['password']);
            }

            if (strlen($_POST['fullname']) > 40) {
                array_push($errorList, "Full Name is too long, maximum length is 40!");
            } else {
                $clerkFullName = trim($_POST['fullname']);
            }

            if (strlen($_POST['desc']) > 40) {
                array_push($errorList, "Description is too long, maximum length is 40!");
            } else {
                $clerkDesc = trim($_POST['desc']);
            }

            if (strlen($_POST['phone']) > 15) {
                array_push($errorList, "Phone is too long, maximum length is 15!");
            } elseif (!empty($_POST['phone']) && !is_numeric($_POST['phone'])) {
                array_push($errorList, "Phone can only contain numbers!");
            } elseif (!empty($_POST['phone']) && strlen($_POST['phone']) < 7) {
                array_push($errorList, "Phone is too short, minimum length is 7!");
            } else {
                $clerkPhone = trim($_POST['phone']);
            }

            if (!isset($_POST['zone']) || empty($_POST['zone'])) {
                array_push($errorList, "zone is required!");
            } elseif (!getValue("SELECT zone_id FROM zones WHERE zone_id=" . trim($_POST['zone']))) {
                array_push($errorList, "zone does NOT exist!");
            } else {
                $clerkZone = (int) trim($_POST['zone']);
            }

            if ($mode == 'edit') { // post edit
                $name = trim($_POST['name']);
                if (getValue("SELECT clerk_id FROM clerks WHERE clerk_id <> $id AND clerk_name='$name'")) {
                    array_push($errorList, "Clerk Name is already used!");
                }

                if (count($errorList) == 0) {

                    $query = "UPDATE clerks SET "
                            . " clerk_name='$clerkName',"
                            . " clerk_password=IF(clerk_password<>'$clerkPassword',SHA2('$clerkPassword',256),'$clerkPassword'),"
                            . " clerk_fullname='$clerkFullName',"
                            . " clerk_desc='$clerkDesc',"
                            . " clerk_phone='$clerkPhone',"
                            . " clerk_zone=$clerkZone "
                            . "WHERE clerk_id=$id;";

                    if (executeQuery($query)) {
                        header("Location: " . basename($_SERVER['PHP_SELF']));
                    } else {
                        array_push($errorList, "SQL error" . $lastSQLError);
                    }
                }
            } else { // post add
                $name = trim($_POST['name']);
                if (getValue("SELECT clerk_id FROM clerks WHERE clerk_name='$name'")) {
                    array_push($errorList, "Clerk Name is already used!");
                }

                if (count($errorList) == 0) {

                    $query = "INSERT INTO clerks("
                            . "clerk_name,"
                            . "clerk_password,"
                            . "clerk_fullname,"
                            . "clerk_desc,"
                            . "clerk_phone,"
                            . "clerk_zone) VALUES("
                            . "'$clerkName',"
                            . "SHA2('$clerkPassword',256),"
                            . "'$clerkFullName',"
                            . "'$clerkDesc',"
                            . "'$clerkPhone',"
                            . "'$clerkZone');";

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

            $clerkName = $_POST['name'];
            $clerkPassword = $_POST['password'];
            $clerkFullName = $_POST['fullname'];
            $clerkDesc = $_POST['desc'];
            $clerkPhone = $_POST['phone'];
            $clerkZone = $_POST['zone'];
        } else {

            // -- [ GET request
            $editmode = ($mode == 'edit' ? 1 : 0);
            $id = isset($_GET['id']) ? $_GET['id'] : 0;
            $clerk = getRow("SELECT * FROM clerks WHERE clerk_id=$id;");
            $clerkName = ($editmode ? $clerk['clerk_name'] : '');
            $clerkPassword = ($editmode ? $clerk['clerk_password'] : '');
            $clerkFullName = ($editmode ? $clerk['clerk_fullname'] : '');
            $clerkDesc = ($editmode ? $clerk['clerk_desc'] : '');
            $clerkPhone = ($editmode ? $clerk['clerk_phone'] : '');
            $clerkZone = ($editmode ? $clerk['clerk_zone'] : '');
        }
}
