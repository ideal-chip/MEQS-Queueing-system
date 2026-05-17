<?php

error_reporting(0);
require_once("../../../language.php");
//require_once("../../api/db.php");

$errorList = array();
$form_data = array();
$status = FALSE;
$lastId = 0;
$data = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ajaxMode = (isset($_POST['ajaxMode']) ? $_POST['ajaxMode'] : '');
} else {
    $ajaxMode = (isset($_GET['ajaxMode']) ? $_GET['ajaxMode'] : '');
}

if (!empty($ajaxMode)) {

    $ajaxMode = strtolower($ajaxMode);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {


        switch ($ajaxMode) {
            case 'add':
                if (isset($_POST['id']) && $_POST['id'] > 0) {

                    $id = $_POST['id'];
                    $subName = '';
                    $subWaitDays = '';
                    $subPapers = '';

                    if (!getValue("SELECT category_id FROM categories WHERE category_id = $id;")) {
                        array_push($errorList, "Main category does not exists!");
                    }

                    $trimmedName = trim($_POST['sub-name']);
                    if (!isset($trimmedName) || empty($trimmedName)) {
                        array_push($errorList, "Name is required!");
                    } elseif (getValue("SELECT subcategory_id FROM subcategories WHERE subcategory_name='$trimmedName'")) {
                        array_push($errorList, "Name is already used!");
                    } else {
                        $subName = $trimmedName;
                    }

                    $trimmedDays = trim($_POST['sub-waittime']);
                    if (!isset($trimmedDays) || empty($trimmedDays)) {
                        array_push($errorList, "Wait time is required!");
                    } elseif (strlen($trimmedDays) > 3) {
                        array_push($errorList, "Wait time is too big, maximum digits are 3!");
                    } elseif (!is_numeric($trimmedDays) || (int) $trimmedDays < 0) {
                        array_push($errorList, "Wait time must be a positive number!");
                    } else {
                        $subWaitDays = (int) $trimmedDays;
                    }

                    $subPapers = $_POST['req-papers'];

                    if (count($errorList) == 0) {
                        $query = "INSERT INTO subcategories (subcategory_name, wait_time_days, papers, main_category_id) values(
                            '$subName',
                            '$subWaitDays',
                            '$subPapers',
                            '$id'
                        );";

                        if (executeQuery($query)) {
                            $status = TRUE;
                            $data = getRow("SELECT * FROM subcategories WHERE subcategory_id = $lastID;");
                        } else {
                            array_push($errorList, "SQL Error!");
                            $status = FALSE;
                        }
                    } else {
                        $status = FALSE;
                    }

                    $form_data['errors'] = $errorList;
                    $form_data['status'] = $status;
                    $form_data['data'] = $data;

                    echo json_encode($form_data, JSON_UNESCAPED_UNICODE);
                } else {
                    echo 0;
                }
                break;
            case 'update':
                if (isset($_POST['id']) && $_POST['id'] > 0) {

                    $id = $_POST['id'];
                    $subID = $_POST['subcategory-id'];
                    $subName = '';
                    $subWaitDays = '';
                    $subPapers = '';

                    if (!getValue("SELECT category_id FROM categories WHERE category_id = $id;")) {
                        array_push($errorList, "Main category does not exists!");
                    }

                    if (!getValue("SELECT subcategory_id FROM subcategories WHERE subcategory_id = $subID;")) {
                        array_push($errorList, "Sub category does not exists!");
                    }

                    $trimmedName = trim($_POST['sub-name']);
                    if (!isset($trimmedName) || empty($trimmedName)) {
                        array_push($errorList, "Name is required!");
                    } elseif (getValue("SELECT subcategory_id FROM subcategories WHERE subcategory_id <> $subID AND subcategory_name='$trimmedName'")) {
                        array_push($errorList, "Name is already used!");
                    } else {
                        $subName = $trimmedName;
                    }

                    $trimmedDays = trim($_POST['sub-waittime']);
                    if (!isset($trimmedDays) || empty($trimmedDays)) {
                        array_push($errorList, "Wait time is required!");
                    } elseif (strlen($trimmedDays) > 3) {
                        array_push($errorList, "Wait time is too big, maximum digits are 3!");
                    } elseif (!is_numeric($trimmedDays) || (int) $trimmedDays < 0) {
                        array_push($errorList, "Wait time must be a positive number!");
                    } else {
                        $subWaitDays = (int) $trimmedDays;
                    }

                    $subPapers = $_POST['req-papers'];

                    if (count($errorList) == 0) {
                        $query = "UPDATE subcategories SET 
                            subcategory_name = '$subName',
                            wait_time_days = '$subWaitDays', 
                            papers = '$subPapers',
                            main_category_id = '$id'
                            WHERE subcategory_id = $subID;";

                        if (executeQuery($query)) {
                            $status = TRUE;
                            $data = getRow("SELECT * FROM subcategories WHERE subcategory_id = $subID;");
                        } else {
                            array_push($errorList, "SQL Error!");
                            $status = FALSE;
                        }
                    } else {
                        $status = FALSE;
                    }

                    $form_data['errors'] = $errorList;
                    $form_data['status'] = $status;
                    $form_data['data'] = $data;

                    echo json_encode($form_data);
                } else {
                    echo 0;
                }
                break;
            case 'delete':
                if (isset($_POST['subcategory-id']) && $_POST['subcategory-id'] > 0) {

                    $subID = $_POST['subcategory-id'];

                    if (!getValue("SELECT subcategory_id FROM subcategories WHERE subcategory_id = $subID;")) {
                        array_push($errorList, "Sub category does not exists!");
                    }

                    if (count($errorList) == 0) {
                        $query = "DELETE FROM subcategories 
                            WHERE subcategory_id = $subID;";

                        if (executeQuery($query)) {
                            $status = TRUE;
                            $data = array();
                            $data['subcategory_id'] = $subID;
                        } else {
                            array_push($errorList, "SQL Error!");
                            $status = FALSE;
                        }
                    } else {
                        $status = FALSE;
                    }

                    $form_data['errors'] = $errorList;
                    $form_data['status'] = $status;
                    $form_data['data'] = $data;

                    echo json_encode($form_data);
                } else {
                    echo 0;
                }
                break;
            case 'report':
                if (isset($_POST['subcategory']) && $_POST['subcategory'] > 0) {
                    $subID = $_POST['subcategory'];
                    
                    $query = "UPDATE `subcategories` 
                             SET `in_report` = !(in_report) 
                             WHERE subcategory_id = $subID";
                    
                    if (executeQuery($query)) {
                        echo getValue("SELECT in_report FROM subcategories WHERE subcategory_id = $subID;");
                    }else{
                        echo -2;
                    }
                } else {
                    echo -1;
                }
                break;
        }
    } else {
        if (isset($_GET['subcategory']) && $_GET['subcategory'] > 0) {
            $subID = $_GET['subcategory'];
            $query = "SELECT * FROM subcategories WHERE subcategory_id = $subID;";

            $row = getRow($query);

            if ($row) {
                echo json_encode($row);
            } else {
                echo 0;
            }
        } else {
            echo 0;
        }
    }
} else {
    echo 0;
}
