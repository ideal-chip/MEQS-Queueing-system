<?php
$prev = 1;

require_once './common/php_head.php';
//-------------------------------------------------------------< common vars >---
$title = getTextValue('ticketprinting', $lang);
$view = "./views/ticketentry/";
$parent = "";
$error = '';
$message = '';
$result = '';
//-------------------------------------------------------------< other includes >---
include_once $view . 'process.php';
//-------------------------------------------------------------< data >---
$categories = getArrayAssoc("SELECT category_id, category_key, text_value AS 'category_name'
        FROM categories, texts 
        WHERE category_key=text_key 
        AND text_language='$lang'
        AND category_zone=(SELECT category_zone FROM categories ORDER BY category_id LIMIT 1);");
?>

<!DOCTYPE html>
<html>
    <head>
        <?php include_once './common/head.php'; ?>

    </head>
    <body style="direction:<?php echo $dir ?>;">
        <?php include_once './common/nav.php'; ?>
        <?php include_once './common/header.php'; ?>

        <div class="container marg-bottom-50">
            <div class="well well-header"><?php echo $title ?></div>

            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo $title ?></h3></div>
                <div class="panel-body">
                    <span id="error-p" class="alert alert-danger"><?php echo $error ?></span>
                    <span id="result-p" class="alert alert-success"><?php echo $result ?></span>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="form-group form-inline">
                            <input type="text" autocomplete="on" autofocus placeholder="<?php echo getTextValue("numberoftickets", $lang) ?>" name="ticketqty" required maxlength="4" class="form-control" />
                            <select name="ticketcategory" class="form-control" >
                                <optgroup label="<?php echo getTextValue("categories", $lang) ?>">
                                    <?php
                                    foreach ($categories as $Row) {
//                                        $selected = ($Row['category_id'] == 3 ? "selected" : "");
                                       ?>
                                        <option value="<?php echo $Row['category_id'] ?>" >[ <?php echo $Row['category_key'] . " ] " . $Row['category_name'] ?></option>
                                        <?php 
                                    }
                                    ?>
                                </optgroup>
                            </select>
                            <button class="btn btn-primary" type="submit" name="submit" ><?php echo getTextValue("enter", $lang) ?></button>
                        </div>
                    </form>
                    <div >
                        <span id="message-p" class="alert alert-info"><?php echo $message; ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php include_once './common/footer.php'; ?>

        <?php include_once './common/foot_scripts.php'; ?>
        <script type="text/javascript">
            var error;
            var result;
            var message;
            init();
            function init() {
                error = document.getElementById('error-p');
                result = document.getElementById('result-p');
                message = document.getElementById('message-p');
                //alert("er " + error.innerHTML + "m " + message + "r " + result);
                if (error.innerHTML) {
                    error.style.display = 'block';
                }
                if (result.innerHTML) {
                    result.style.display = 'block';
                }
                if (message.innerHTML) {
                    message.style.display = 'block';
                }
            }
        </script>
    </body>
</html>
