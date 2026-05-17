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
        executeQuery("DELETE FROM audios WHERE audio_id=$id;");
        header("Location: " . basename($_SERVER['PHP_SELF']));
        break;
    case 'edit':
    case 'add':
        $id = 0;
        $audioName = '';
        $audioPath = '';
        $audioLanguage = '';
        $audioGender = 0;

        if ($_SERVER["REQUEST_METHOD"] == "POST") { // POST
//            $errorList = array();
// check values for errors!
            if ($mode == 'edit') {
                if (!isset($_POST['id']) || empty($_POST['id'])) {
                    array_push($errorList, "id is not set!");
                } else {
                    $id = (int) $_POST['id'];
                }
            }


            if (!isset($_POST['name']) || empty($_POST['name'])) {
                array_push($errorList, "name is required!");
            } elseif (strlen($_POST['name']) > 20) {
                array_push($errorList, "audio name is too long, maximum length is 20!");
            } else {
                $audioName = trim($_POST['name']);
            }

            if (!isset($_POST['path']) || empty($_POST['path'])) {
                array_push($errorList, "path is required!");
            } elseif (strlen($_POST['path']) > 50) {
                array_push($errorList, "path is too long, maximum length is 20!");
            } else {
                $audioPath = trim($_POST['path']);
            }

            if (!isset($_POST['audiolang']) || empty($_POST['audiolang'])) {
                array_push($errorList, "audio language is required!");
            } else {
                $audioLanguage = trim($_POST['audiolang']);
            }

            if (!isset($_POST['gender']) || empty($_POST['gender'])) {
                array_push($errorList, "gender is required!");
            } elseif (!array_key_exists($_POST['gender'], $genders)) {
                array_push($errorList, "gender does NOT exist!");
            } else {
                $audioGender = (int) trim($_POST['gender']);
            }

            if ($mode == 'edit') { // POST - Edit
                if (count($errorList) == 0) {
                    $query = "UPDATE audios SET audio_name='$audioName',audio_path='$audioPath',audio_language='$audioLanguage', audio_gender=$audioGender WHERE audio_id=$id;";
                    if (executeQuery($query)) {
                        header("Location: " . basename($_SERVER['PHP_SELF']));
                    } else {
                        array_push($errorList, "SQL error" . $lastSQLError);
                    }
                }
            } else { // POST - Add
                if (count($errorList) == 0) {
                    $query = "INSERT INTO audios (audio_name,audio_path,audio_language, audio_gender) VALUES('$audioName','$audioPath','$audioLanguage', $audioGender);";
                    if (executeQuery($query)) {
                        header("Location: " . basename($_SERVER['PHP_SELF']));
                    } else {
                        array_push($errorList, "SQL error" . $lastSQLError);
                    }
                }
            }

            $editmode = ($mode == 'edit' ? 1 : 0);
            $id = isset($_POST['id']) ? $_POST['id'] : 0;
            $audioName = $_POST['name'];
            $audioPath = $_POST['path'];
            $audioLanguage = $_POST['audio'];
            $audioGender = $_POST['gender'];
        } else { // GET
            $editmode = ($mode == 'edit' ? 1 : 0);
            $id = isset($_GET['id']) ? $_GET['id'] : 0;
            $audios = getRow("SELECT * FROM audios WHERE audio_id=$id;");
            $audioName = ($editmode ? $audios['audio_name'] : '');
            $audioPath = ($editmode ? $audios['audio_path'] : '');
            $audioLanguage = ($editmode ? $audios['audio_language'] : '');
            $audioGender = ($editmode ? $audios['audio_gender'] : '');
        }
}