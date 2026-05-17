<?php
$prev = 16;

require_once './common/php_head.php';
//-------------------------------------------------------------< common vars >---
$title = getTextValue("counterscategories", $lang);
$view = "./views/countersCategories/";
$parent = "";
//-------------------------------------------------------------< other includes >---
include_once $view . 'process.php';
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

        <script type="text/javascript">

            function resizeIframe(obj) {
                obj.style.height = (parseInt(obj.contentWindow.document.body.scrollHeight) + 150) + 'px';
            }

            function UpdateCatList() {
                var counter = document.getElementById('counter').value;
            }


            $titleMore = 'See more';
            $titleLess = 'See less';

            $(".moreBtn").click(function ()
            {
                $item = $(this).closest('.cats-item');
                $sec = $item.find(".cats-content");

                if ($sec.css("display") == 'none') {

                    $item.find(".cats-content").slideDown('fast');
                    //$(this).children("img").attr("src", $srcLess);
                    $(this).attr("title", $titleLess);
                    resizeIframe($('#page'));
                } else {

                    $item.find(".cats-content").slideUp('fast');
                    //$(this).children("img").attr("src", $srcMore);
                    $(this).attr("title", $titleMore);
                }

            });
        </script>

    </body>
</html>
