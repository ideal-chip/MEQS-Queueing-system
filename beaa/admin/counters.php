<?php
$prev = 16;

require_once './common/php_head.php';
//-------------------------------------------------------------< common vars >---
$title = getTextValue("counters", $lang);
$view = "./views/counters/";
$parent = "";
//-------------------------------------------------------------< other includes >---
include_once $view . 'process.php';


//-------------------------------------------------------------< data >---
?>

<!DOCTYPE html>
<html>
    <head>
        <?php include_once './common/head.php'; ?>

    </head>
    <body style="direction:<?php echo $dir ?>;">
        <?php include_once './common/nav.php'; ?>
        <?php include_once './common/header.php'; ?>

        <div class="container-full marg-bottom-50">
            <?php
            switch ($mode) {
                case 'add':
                case 'edit':
                    include_once $view . 'form.php';
                    break;
                case 'list':
                    include_once $view . 'list.php';
                    break;
                default :
                    include_once $view . 'list.php';
                    break;
            }
            ?>
        </div>
        <?php include_once './common/footer.php'; ?>

        <?php include_once './common/foot_scripts.php'; ?>
    </body>
</html>
