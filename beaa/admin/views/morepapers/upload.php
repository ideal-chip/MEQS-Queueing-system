<?php

$uploadsPath = "../../../uploads/pdf/";

$listtype = array(
    '.doc' => 'application/msword',
    '.docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    '.pdf' => 'application/pdf');

if (isset($_FILES['uploaded'])) {
    if (!$_FILES['uploaded']['error']) {

        // make sure the file is a pdf
        $file_type = $_FILES['uploaded']['type'];

        if ($file_type == $listtype['.doc'] || $file_type == $listtype['.docx'] || $file_type == $listtype['.pdf']) {
            
            if (!file_exists($uploadsPath . $_FILES['uploaded']['name'])) {
                if (copy($_FILES['uploaded']['tmp_name'], $uploadsPath . $_FILES['uploaded']['name'])) {
                    header("Location: " . $_SERVER['HTTP_REFERER']);
                } else {
                    echo "File Upload Failed<br>";
                }
            } else {
                echo "File " . $_FILES['uploaded']['name'] . " Exists<br>";
            }
            
        } else {
            die("Uploaded failed: file must be pdf!");
        }
    } else {
        echo "File Upload Error<br>Code: " . $_FILES['uploaded']['error'];
    }
} else {
    echo "Uploaded failed! <br>";
}
?>
