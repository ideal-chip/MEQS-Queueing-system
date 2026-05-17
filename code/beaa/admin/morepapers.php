<?php
$prev = 4;
require_once './common/php_head.php';
//-------------------------------------------------------------< common vars >---

$title = "pdf papers";
$view = "./views/morepapers/";
$parent = "";

//-------------------------------------------------------------< data >---

$acceptedFileTypes = "application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document";
$filesPath = "../files/";
$uploadsPath = "../uploads/pdf/";
$listtype = array(
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'pdf' => 'application/pdf');
//-----------------------------< functions >---

function human_filesize($bytes, $decimals = 2) {
    $size = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}

function checkType($file, $types) {
    
    $type = strtolower(pathinfo($file)['extension']);
    return array_key_exists($type, $types);
}

function getFileType($file){
    return strtolower(pathinfo($file)['extension']);
}

//-----------------------------< data >---
$items = scandir($uploadsPath);
$all_files = array();
$images = array();
$otherFiles = array();

foreach ($items as $item) {
    $full_path = $uploadsPath . $item;
    if (is_file($full_path)) {
        
        if (checkType($full_path, $listtype)) {
            
//            $item_name = str_replace('.pdf', '', $item);
            array_push($all_files, $item);
        }
    }
}

foreach ($all_files as $value) {
    if ($value == 'bigbg.jpg' || $value == 'head.png' || $value == 'logo.png' || $value == 'star.png') {
        array_push($images, $value);
    } else {
        array_push($otherFiles, $value);
    }
}

$totalSize = count($otherFiles);

//var_dump($items);
//var_dump($all_files);
//var_dump($images);
//var_dump($pdfs);
?>

<!DOCTYPE html>
<html>
    <head>
        <?php include_once './common/head.php'; ?>
    </head>
    <body style="direction:<?php echo $dir ?>;">
        <?php include_once './common/nav.php'; ?>
        <?php include_once './common/header.php'; ?>

        <div class="container-full">
            <div class="well well-header"><?php echo getTextValue("fileList", $lang) ?> </div>
            <div class="s-50 center-block well-sm row">
                <div class="col-md-5">
                    <form action="./views/morepapers/upload.php" enctype="multipart/form-data" method="POST">
                        <div class="input-group">
                            <input class="btn btn-success" type="file" name="uploaded" accept="<?php echo $acceptedFileTypes ?>" required style="margin: 0 auto;">
                            <input class="btn btn-default" type="submit" value="<?php echo getTextValue("upload", $lang) ?>">
                        </div>
                    </form>
                </div>
                <div class="col-md-7">
                    <span class="pad-10 bg-blue-2  inline-block round-10 text-whito marg-10 marg-v-20">total: <?php echo $totalSize ?></span>    
                </div>
            </div>
            <table class="table table-striped" style="direction: ltr;">
                <tr>
                    <th><?php echo getTextValue("delete", $lang) ?>  </th>
                    <th><?php echo getTextValue("fileName", $lang) ?>  </th>
                    <th><?php echo getTextValue("fileSize", $lang) ?>  </th>
                </tr>
                <?php
                foreach ($otherFiles as $other) {
//                    $file_pdf = $pdf . ".pdf";
                    ?>
                    <tr>
                        <td>
                            <img id='<?php echo $other ?>' 
                                 src='<?php echo $filesPath . "delete.png" ?>' 
                                 style='cursor:pointer;width:16px;height:16px;' 
                                 onclick='deleteFile(this.id)' >
                        </td>
                        <td>
                            <a href='<?php echo $uploadsPath . $other ?>'><?php echo $other ?></a>
                        </td>
                        <td><?php echo human_filesize(filesize($uploadsPath . $other)) ?>  </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <hr>
        </div>
        <?php include_once './common/footer.php'; ?>

        <?php include_once './common/foot_scripts.php'; ?>
        <script type="text/javascript">

            var lang_delete_question = "<?php echo getTextValue("deleteQuestion", $lang) . getTextValue("questionMark", $lang) ?>";

        </script>
        <script src="../js/morepapers.js" type="text/javascript"></script>
    </body>
</html>
