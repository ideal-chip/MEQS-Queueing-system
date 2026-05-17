<?php

error_reporting(0);
require_once '../../language.php';

if (isset($_GET['method']) && !empty($_GET['method'])) {

    $method = strtolower($_GET['method']);

    switch ($method) {
        case 'row':
            if (isset($_GET['id']) && $_GET['id'] > 0) {

                $followupID = $_GET['id'];
                $query = "SELECT client_name, mobile_number, category_id, subcategory_id,
                            followup_id, event_id, clerk_id, extension_no
                            FROM followups
                            WHERE followup_id = $followupID;";
                $row = getRow($query);

                if ($row) {
                    $categoryID = $row['category_id'];
                    $subcategories = getArrayAssoc("SELECT subcategory_id, subcategory_name
                                                    FROM subcategories WHERE main_category_id = $categoryID;");

                    $data = array();
                    $data['row'] = $row;
                    $data['subcategories'] = $subcategories;
                    echo json_encode($data);
                } else {
                    //echo 'SQL error';
                    echo 0;
                }
            } else {
//                echo 'invalid id';
                echo 0;
            }
            break;
        case 'getlist':
            if (isset($_GET['clerkId']) && $_GET['clerkId'] > 0) {

                $lang = $_GET['langId'];
                $clerkID = $_GET['clerkId'];

                $max = getRequestVal('max', '10', 'get');
                $page = getRequestVal('page', '1', 'get');
                $offset = $max * ($page - 1);

//                $dateQuery = "";
                $dateQuery = "AND DATE(followups.date_created) = DATE(NOW())";
                $onDate = getRequestVal('ondate', 0, 'get');
                if ($onDate) {
                    // change based on date
                    $dateQuery = "AND DATE(followups.date_created) = '$onDate'";
                }

                $query = "SELECT followup_id, serial_no, client_name, mobile_number, followups.date_created, 
	 IFNULL((SELECT subcategories.subcategory_name FROM subcategories WHERE subcategories.subcategory_id = followups.subcategory_id), '')AS 'subcategory_name',
	 clerks.clerk_name, texts.text_value AS 'category_name' 
                            FROM  clerks, texts, followups
                                INNER JOIN(SELECT followup_id FROM followups 
                                ORDER BY followups.date_created DESC
                                LIMIT $max OFFSET $offset) AS RESULT USING(followup_id)
                            WHERE
                            texts.text_language = '$lang'
                            AND texts.text_key = (SELECT categories.category_key FROM categories WHERE category_id = followups.category_id) 
                            AND followups.clerk_id = clerks.clerk_id 
                            AND followups.clerk_id = $clerkID
                            $dateQuery;";

                $list = getArrayAssoc($query);
                $total = getValue("SELECT COUNT(*) FROM followups WHERE clerk_id = $clerkID $dateQuery;");
                $size = ceil($total / $max);

                $data = array();
                $data['list'] = $list;
                $data['total'] = $total;
                $data['size'] = $size;

//                var_dump($data);
//                var_dump($page);
//                var_dump($list);
                echo json_encode($data);
            } else {
                echo 0;
            }
            break;
        case 'getpapers':
            if (isset($_GET['id']) && $_GET['id'] > 0) {

                $subCategoryID = $_GET['id'];
                $query = "SELECT papers FROM subcategories WHERE subcategory_id = $subCategoryID;";

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

        case 'getpreview':
            if (isset($_GET['id']) && $_GET['id'] > 0) {

                $lang = $_GET['langId'];
                $followupID = $_GET['id'];

                $data = getRow("SELECT followup_id, serial_no, client_name, 
                    mobile_number, followups.date_created, subcategory_name, followups.extension_no,
                    clerks.clerk_name, texts.text_value AS 'category_name', wait_time_days
                FROM followups, subcategories, clerks, texts 
                WHERE 
                followups.subcategory_id = subcategories.subcategory_id 
                AND texts.text_language = '$lang' 
                AND texts.text_key = (SELECT categories.category_key FROM categories WHERE category_id = followups.category_id) 
                AND followups.clerk_id = clerks.clerk_id 
                AND followup_id = $followupID;");


                if ($data) {
                    echo json_encode($data);
                } else {
                    echo 0;
                }
            } else {
                echo 0;
            }
            break;

        case 'getsubcategories':
            if (isset($_GET['categoryID'])) {

                $categoryID = $_GET['categoryID'];

                $query = "SELECT subcategory_id, subcategory_name
                            FROM subcategories WHERE main_category_id = $categoryID;";
                $result = getArrayAssoc($query);

                if ($result) {
                    echo json_encode($result);
                } else {
                    echo 0;
                }
            } else {
                echo 0;
            }
            break;
        case 'getextlist':

                $query = "SELECT * FROM extension_numbers";
                $result = getArrayAssoc($query);

                if ($result) {
                    echo json_encode($result);
                } else {
                    echo 0;
                }
            
            break;
    }
} else {
//    echo 'no method specified';
    echo 0;
}

