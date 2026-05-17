<?php

error_reporting(0);
//require_once("../../language.php");
require_once("../../api/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ajaxMode = (isset($_POST['ajaxMode']) ? $_POST['ajaxMode'] : '');
} else {
    $ajaxMode = (isset($_GET['ajaxMode']) ? $_GET['ajaxMode'] : '');
}

if (!empty($ajaxMode)) {

    $ajaxMode = strtolower($ajaxMode);

    //=====================================================================| POST requests
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $errorList = array();
        $form_data = array();
        $status = FALSE;
        $lastId = 0;

        $data = 0;

        switch ($ajaxMode) {
            case 'add': // add new row, return full data for review and print
                if (isset($_POST['category-id']) && $_POST['category-id'] > 0) {

                    //form fields- high chance of errors= check validity!
                    $clientName = '';
                    $phoneNumber = '';

                    // hidden fields or values[from a select element]- low chance of errors = check existance in DB
                    $ext_number = $_POST['sub_fones'];
                    $eventID = $_POST['event-id'];
                    $categoryID = $_POST['category-id'];
                    $subCategoryID = $_POST['subcategory-id'];
                    $clerkID = $_POST['clerk-id'];
                    $lang = $_POST['lang-id'];


                    if (!getValue("SELECT event_id FROM events WHERE event_id = $eventID;")) {
                        $eventID = 0;
                    }
                    if (!getValue("SELECT category_id FROM categories WHERE category_id = $categoryID;")) {
                        array_push($errorList, "Main category does not exists!");
                    }
                    if (!getValue("SELECT subcategory_id FROM subcategories WHERE subcategory_id = $subCategoryID;")) {
                        array_push($errorList, "Sub category does not exists!");
                    }
                    if (!getValue("SELECT clerk_id FROM clerks WHERE clerk_id = $clerkID;")) {
                        array_push($errorList, "Clerk does not exists - please refresh page or login again!");
                    }
                    if (!getValue("SELECT extension_id FROM extension_numbers WHERE extension_no = $ext_number;")) {
                        array_push($errorList, "extension number does not exists!");
                    }

                    $trimmedClientName = trim($_POST['client-name']);
                    if (!isset($trimmedClientName) || empty($trimmedClientName)) {
                        array_push($errorList, "Client name is required!");
                    } elseif (strlen($trimmedClientName) > 100) {
                        array_push($errorList, "Client name is too long, please use shorter name!");
                    } else {
                        $clientName = $trimmedClientName;
                    }

                    $trimmedPhoneNumber = trim($_POST['phone-number']);
                    if (!isset($trimmedPhoneNumber) || empty($trimmedPhoneNumber)) {
                        array_push($errorList, "Phone number is required!");
                    } elseif (!is_numeric($trimmedPhoneNumber)) {
                        array_push($errorList, "Phone number is invalid, only numbers are allowed!");
                    } elseif (strlen($trimmedPhoneNumber) < 10 || strlen($trimmedPhoneNumber) > 14) {
                        array_push($errorList, "Phone number is invalid, please check again!");
                    } else {
                        $phoneNumber = $trimmedPhoneNumber;
                    }

                    $serialData = getNewSerialData($categoryID);
                    $serialNo = $serialData['serialNo'];
                    $order = $serialData['order'];

                    if (getValue("SELECT serial_no FROM followups WHERE serial_no = '$serialNo';")) {
                        array_push($errorList, "Serial No. is already used for this ticket, please try editing instead!");
                    }

                    if (count($errorList) == 0) {

                        $query = "INSERT INTO followups 
                                (serial_no, day_order_no, event_id, client_name,
                                mobile_number, category_id, subcategory_id,
                                date_created, clerk_id, extension_no) 
                                values(
                                    '$serialNo', $order, $eventID, '$clientName',
                                    '$phoneNumber',$categoryID, $subCategoryID,
                                    NOW(), $clerkID, $ext_number);";

                        if (executeQuery($query)) {

                            $status = TRUE;
                            //$lang = getValue($query)
                            $data = getFollowupDataPreview($lastID, $lang);
                        } else {
                            array_push($errorList, "SQL Error!");
                            $status = FALSE;
                        }
                    }
                } else {
                    $status = FALSE;
                    array_push($errorList, "Main category does not exists!");
                }

                $form_data['errors'] = $errorList;
                $form_data['status'] = $status;
                $form_data['data'] = $data;

                echo json_encode($form_data);
                break;

            case 'update':// edit old row, return full data for review and print
                if (isset($_POST['followup-id']) && $_POST['followup-id'] > 0) {

                    //form fields- high chance of errors= check validity!
                    $clientName = '';
                    $phoneNumber = '';

                    // hidden fields or values[from a select element]- low chance of errors = check existance in DB though
                    $ext_number = $_POST['sub_fones'];
                    $followupID = $_POST['followup-id'];
                    $eventID = $_POST['event-id'];
                    $categoryID = $_POST['category-id'];
                    $subCategoryID = $_POST['subcategory-id'];
                    $clerkID = $_POST['clerk-id'];
                    $lang = $_POST['lang-id'];


                    if (!getValue("SELECT followup_id FROM followups WHERE followup_id = $followupID;")) {
                        array_push($errorList, "Followup record does not exists!");
                    }

                    if (!getValue("SELECT event_id FROM events WHERE event_id = $eventID;")) {
//                        array_push($errorList, "Called ticket does not exists!");
                        $eventID = 0;
                    }
                    if (!getValue("SELECT category_id FROM categories WHERE category_id = $categoryID;")) {
                        array_push($errorList, "Main category does not exists!");
                    }
                    if (!getValue("SELECT subcategory_id FROM subcategories WHERE subcategory_id = $subCategoryID;")) {
                        array_push($errorList, "Sub category does not exists!");
                    }
                    if (!getValue("SELECT clerk_id FROM clerks WHERE clerk_id = $clerkID;")) {
                        array_push($errorList, "Clerk does not exists - please refresh page or login again!");
                    }
                    if (!getValue("SELECT extension_id FROM extension_numbers WHERE extension_no = $ext_number;")) {
                        array_push($errorList, "extension number does not exists!");
                    }

                    $trimmedClientName = trim($_POST['client-name']);
                    if (!isset($trimmedClientName) || empty($trimmedClientName)) {
                        array_push($errorList, "Client name is required!");
                    } elseif (strlen($trimmedClientName) > 100) {
                        array_push($errorList, "Client name is too long, please use shorter name!");
                    } else {
                        $clientName = $trimmedClientName;
                    }

                    $trimmedPhoneNumber = trim($_POST['phone-number']);
                    if (!isset($trimmedPhoneNumber) || empty($trimmedPhoneNumber)) {
                        array_push($errorList, "Phone number is required!");
                    } elseif (!is_numeric($trimmedPhoneNumber)) {
                        array_push($errorList, "Phone number is invalid, only numbers are allowed!");
                    } elseif (strlen($trimmedPhoneNumber) < 10 || strlen($trimmedPhoneNumber) > 14) {
                        array_push($errorList, "Phone number is invalid, please check again!");
                    } else {
                        $phoneNumber = $trimmedPhoneNumber;
                    }

                    if (count($errorList) == 0) {

                        $query = "UPDATE followups  
                                SET 
                                    client_name = '$clientName',
                                    mobile_number = '$phoneNumber',
                                    category_id = $categoryID,
                                    subcategory_id = $subCategoryID,
                                    extension_no = $ext_number
                                WHERE followup_id = $followupID;";

                        if (executeQuery($query)) {

                            $status = TRUE;
                            $data = getFollowupDataPreview($followupID, $lang);
                        } else {
                            array_push($errorList, "SQL Error!");
                            $status = FALSE;
                        }
                    } else {
                        $status = FALSE;
                    }
                } else {
                    $status = FALSE;
                    array_push($errorList, "Followup record does not exists!");
                }

                $form_data['errors'] = $errorList;
                $form_data['status'] = $status;
                $form_data['data'] = $data;

                echo json_encode($form_data);

                break;
            case 'delete':
                if (isset($_POST['followup_id']) && $_POST['followup_id'] > 0) {

                    $followupID = $_POST['followup_id'];

                    if (!getValue("SELECT followup_id FROM followups WHERE followup_id = $followupID;")) {
                        array_push($errorList, "Followup record does not exists!");
                    }

                    if (count($errorList) == 0) {
                        $query = "DELETE FROM followups 
                            WHERE followup_id = $followupID;";

                        if (executeQuery($query)) {
                            $status = TRUE;
                            $data = array();
                            $data['followup_id'] = $followupID;
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
        }
    } else {
        //=====================================================================| GET requests 
//        $errorList = array();
//        $form_data = array();
//        $status = FALSE;
//        $lastId = 0;
//
//        $data = 0;

        switch ($ajaxMode) {
            case 'mark_done':
                if (isset($_GET['id']) && $_GET['id'] > 0) {
                    $followupID = $_GET['id'];
                    $query = "UPDATE followups SET date_done = NOW() WHERE followup_id = $followupID";

                    if (executeQuery($query)) {
                        $row = getRow("SELECT date_done, 
                                DATEDIFF(DATE(date_done), DATE(date_created)) AS 'total_days' 
                                FROM followups WHERE followup_id = $followupID;");

                        echo json_encode($row);
                    } else {
                        echo 0;
                    }
                } else {
                    echo 0;
                }
                break;
            case 'full_card':
                if (isset($_GET['id']) && $_GET['id'] > 0) {

                    $followupID = $_GET['id'];
                    $query = "SELECT * FROM followups WHERE followup_id = $followupID";

                    $row = getRow($query);

                    if ($row) {
                        echo json_encode($row);
                    } else {
                        echo 0;
                    }
                } else {
                    echo 0;
                }
                break;
        }
    }
} else {
    echo 'no ajaxMode';
}

//===============================================================|| Functions

function getNewSerialData($categoryID) {

    $serialNOPrefix = getValue("SELECT serial_no_ref FROM categories WHERE category_id=$categoryID;");

    $date = date('dmy'); // ddmmyy
    $order = str_pad(getOrder(), 3, '0', STR_PAD_LEFT);
    $serialNo = $serialNOPrefix . $date . $order;

    $arr = array();
    $arr['serialNo'] = $serialNo;
    $arr['order'] = $order;

    return $arr;
}

function getOrder() {

    $order = getValue("select IFNULL(max(day_order_no),0)+1 from followups where DATE(date_created)=DATE(NOW());");
    if (!($order % 1000)) {
        $order++;
    }

    return $order;
}

function getFollowupDataPreview($followupID, $lang) {
    $data = getRow("SELECT followups.*, DATEDIFF(DATE(date_done), DATE(date_created)) AS 'total_days',
                    clerks.clerk_name, texts.text_value AS 'category_name', subcategory_name, wait_time_days
                FROM followups, subcategories, clerks, texts 
                WHERE 
                followups.subcategory_id = subcategories.subcategory_id 
                AND texts.text_language = '$lang' 
                AND texts.text_key = (SELECT categories.category_key FROM categories WHERE category_id = followups.category_id) 
                AND followups.clerk_id = clerks.clerk_id 
                AND followup_id = $followupID;");

    return $data;
}
