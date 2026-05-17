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
        executeQuery("DELETE FROM users WHERE user_id=$id;");
        header("Location: " . basename($_SERVER['PHP_SELF']));
        break;
    case 'edit':
    case 'add':

        $id = 0;
        $userName = '';
        $userPassword = '';
        $userFullName = '';
        $userDesc = '';
        $userPhone = '';

        $userPriv = 0;
        $userPrivileges = 0;

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
                array_push($errorList, "Username is required!");
            } elseif (strlen($_POST['name']) > 20) {
                array_push($errorList, "Username is too long, maximum length is 20!");
            } elseif (!ctype_alnum($_POST['name'])) {
                array_push($errorList, "Username can only be numbers and/or letters - no spaces!");
            } else {
                $userName = trim($_POST['name']);
            }

            if (!isset($_POST['password']) || empty($_POST['password'])) {
                array_push($errorList, "Password is required!");
            } else {
                $userPassword = trim($_POST['password']);
            }

            if (strlen($_POST['fullname']) > 40) {
                array_push($errorList, "Full Name is too long, maximum length is 40!");
            } else {
                $userFullName = trim($_POST['fullname']);
            }

            if (strlen($_POST['desc']) > 40) {
                array_push($errorList, "Description is too long, maximum length is 40!");
            } else {
                $userDesc = trim($_POST['desc']);
            }

            if (strlen($_POST['phone']) > 15) {
                array_push($errorList, "Phone is too long, maximum length is 15!");
            } elseif (!empty($_POST['phone']) && !is_numeric($_POST['phone'])) {
                array_push($errorList, "Phone can only contain numbers!");
            } elseif (!empty($_POST['phone']) && strlen($_POST['phone']) < 7) {
                array_push($errorList, "Phone is too short, minimum length is 7!");
            } else {
                $userPhone = trim($_POST['phone']);
            }

            $userPriv = $_POST['privileges'];
            $userPrivileges = 0;
            for ($i = 0; $i < count($userPriv); $i++) {
                $userPrivileges += $userPriv[$i];
            }

            if ($mode == 'edit') { // post edit
                $name = trim($_POST['name']);
                if (getValue("SELECT user_id FROM users WHERE user_id <> $id AND user_name='$name'")) {
                    array_push($errorList, "Username is already used!");
                }

                if (count($errorList) == 0) {

                    $query = "UPDATE users SET "
                            . "user_name='$userName',"
                            . "user_password=IF(user_password<>'$userPassword',SHA2('$userPassword',256),'$userPassword'),"
                            . "user_privileges=$userPrivileges,"
                            . "user_fullname='$userFullName',"
                            . "user_desc='$userDesc',"
                            . "user_phone='$userPhone' "
                            . "WHERE user_id=$id;";

                    if (executeQuery($query)) {
                        header("Location: " . basename($_SERVER['PHP_SELF']));
                    } else {
                        array_push($errorList, "SQL error" . $lastSQLError);
                    }
                }
            } else { // post add
                $name = trim($_POST['name']);
                if (getValue("SELECT user_id FROM users WHERE user_name='$name'")) {
                    array_push($errorList, "Username is already used!");
                }

                if (count($errorList) == 0) {

                    $query = "INSERT INTO users("
                            . "user_name,"
                            . "user_password,"
                            . "user_privileges,"
                            . "user_fullname,"
                            . "user_desc,"
                            . "user_phone) VALUES("
                            . "'$userName',"
                            . "SHA2('$userPassword',256),"
                            . "$userPrivileges,"
                            . "'$userFullName',"
                            . "'$userDesc',"
                            . "'$userPhone');";

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

            $userName = $_POST['name'];
            $userPassword = $_POST['password'];
            $userPriv = $_POST['privileges'];
            $userPrivileges = 0;
            for ($i = 0; $i < count($userPriv); $i++) {
                $userPrivileges += $userPriv[$i];
            }
            $userFullName = $_POST['fullname'];
            $userDesc = $_POST['desc'];
            $userPhone = $_POST['phone'];
        } else {

            // -- [ GET request
            $editmode = ($mode == 'edit' ? 1 : 0);
            $id = isset($_GET['id']) ? $_GET['id'] : 0;
            $user = getRow("SELECT * FROM users WHERE user_id=$id;");
            $userName = ($editmode ? $user['user_name'] : '');
            $userPassword = ($editmode ? $user['user_password'] : '');
            $userPrivileges = ($editmode ? $user['user_privileges'] : '');
            $userFullName = ($editmode ? $user['user_fullname'] : '');
            $userDesc = ($editmode ? $user['user_desc'] : '');
            $userPhone = ($editmode ? $user['user_phone'] : '');
        }
}
