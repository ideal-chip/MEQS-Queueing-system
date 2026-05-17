<?php
error_reporting(0);
require_once("../../language.php");

if (isset($_GET['categoryID'])) {
    
    $categoryID = $_GET['categoryID'];
    
//    $query = "SELECT subcategory_id, subcategory_name, wait_time_days
//            FROM subcategories WHERE main_category_id = $categoryID;";
    
    $query = "SELECT subcategory_id, subcategory_name
            FROM subcategories WHERE main_category_id = $categoryID;";
    $result = getArrayAssoc($query);
    
    if ($result) {
        echo json_encode($result);
    } else {
        echo 0;
    }
}else{
    echo 0;
}
