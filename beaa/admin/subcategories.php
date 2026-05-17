<?php
$prev = 16;

require_once './common/php_head.php';
//-------------------------------------------------------------< common vars >---
$title = getTextValue('subcategories', $lang);
$view = "./views/subcategories/";
$parent = "";

//-------------------------------------------------------------< langs >---


$dir = trim(getTextValue('dir', $lang));
$empty = getTextValue('empty', $lang);
$subService = getTextValue('subService', $lang);
$mainService = getTextValue('mainService', $lang);
$requiredPapers = getTextValue('requiredPapers', $lang);

$estimatdWaitTime = getTextValue("waitTime", $lang);

$add = getTextValue('add', $lang);
$edit = getTextValue('edit', $lang);
$update = getTextValue("update", $lang);
$delete = getTextValue('delete', $lang);
$save = getTextValue("save", $lang);
$cancel = getTextValue('cancel', $lang);

$deleteQuestion = getTextValue("deleteQuestion", $lang);
$questionMark = getTextValue("questionMark", $lang);
$YES = getTextValue("yes", $lang);
$NO = getTextValue("no", $lang);
$insideReport = getTextValue("insideReport", $lang);

//-------------------------------------------------------------< other includes >---
//include_once $view . 'process.php';


//-------------------------------------------------------------< data >---
//
if (isset($_GET['id']) && $_GET['id'] > 0) {
    $id = $_GET['id'];
} else {
    $id = getValue("SELECT category_id FROM categories ORDER BY category_id LIMIT 1;");
}

$zoneID = getValue("SELECT category_zone FROM categories WHERE category_id=$id;");
$subCateories = getArrayAssoc("SELECT * FROM subcategories WHERE main_category_id = $id;");

$categories = getArrayAssoc("SELECT category_id, text_value AS 'catName' FROM categories, texts WHERE category_key=text_key AND text_language='$lang' AND category_zone=$zoneID;");

?>

<!DOCTYPE html>
<html>
    <head>
        <?php include_once './common/head.php'; ?>
        <link href="../js/minified/themes/default.min.css" rel="stylesheet" type="text/css"/>

    </head>
    <body style="direction:<?php echo $dir ?>;">
        <?php include_once './common/nav.php'; ?>
        <?php include_once './common/header.php'; ?>

        <div class="container-full marg-bottom-50">
            <div class="well well-header"><?php echo $title ?></div>
            <div class="s-50 center-block">
                <form class="form-horizontal">
                    <div class="form-group form-group-sm">
                        <label for="category-id" class="col-lg-4 control-label"><?php echo $mainService ?></label>
                        <div class="col-lg-8">
                            <select onchange="refreshPage(this.value);" class="small pad-10 pad-h-30" name="category-id" id="category-id">
                                <?php
                                foreach ($categories as $categoryItem) {
                                    $selected = ($id == $categoryItem['category_id']) ? 'selected' : '';
                                    ?>
                                    <option value='<?php echo $categoryItem['category_id'] ?>' <?php echo $selected ?> ><?php echo $categoryItem['catName'] ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class=" pad-20 marg-10">
                <!--<div class="h5 alert bg-blue-deep font-bold text-whito pad-5"><?php echo $title ?> [ <span id="sub-size"><?php echo count($subCateories) ?></span>  ]</div>-->
                <div id="sub-cat-list" class="row">
                    <table class="s-90 table table-bordered table-striped table-close"> 
                        <thead> 
                            <tr class="bg-green-dark text-whito">
                                <th><?php echo $insideReport ?></th> 
                                <th><?php echo $subService ?></th> 
                                <th><?php echo $estimatdWaitTime ?></th> 
                                <th></th>
                            </tr> 
                        </thead> 
                        <tbody> 
                            <?php
                            if (count($subCateories)) {
                                foreach ($subCateories as $row) {
                                    $subCat_ID = $row['subcategory_id'];
                                    $reportBtnID = "repo_$subCat_ID";
                                    $inReport = $YES;
                                    if ($row['in_report'] == 0) {
                                        $inReport = $NO;
                                    }
                                    ?> 
                                    <tr id="row-<?php echo $subCat_ID ?>" class="border-btm border-gray"> 
                                        <td>
                                            <button id="<?php echo $reportBtnID ?>" onclick="FlipReport(<?php echo $subCat_ID ?>);" class="btn btn-default btn-xs no-marg marg-h-5"><?php echo $inReport ?></button>
                                        </td> 
                                        <td><?php echo $row['subcategory_name'] ?></td> 
                                        <td><?php echo $row['wait_time_days'] ?></td>
                                        <td>
                                            <button onclick="showForm('update', <?php echo $subCat_ID ?>);" type="button" class="btn btn-success btn-xs no-marg marg-h-5"><?php echo $edit ?></button> |
                                            <button onclick="showForm('delete', <?php echo $subCat_ID ?>);" type="button" class="btn btn-danger btn-xs no-marg marg-h-5"><?php echo $delete ?></button>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr class="empty-invert">
                                    <td colspan="3"><span><?php echo $empty ?></span></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody> 
                    </table>
                </div>
                <div class="container bg-white s-100">
                    <div>
                        <button id="btn-add" type="button" onclick="showForm('add');" class="btn btn-info btn-sm"><i class="fa fa-plus-square"></i> <?php echo $add ?></button> 
                    </div>
                    <div id="sub-modal" style="display: none;">
                        <div class=" text-dark marg-10">
                            <div id="form-con" class="pad-10">

                                <div id="ajax-error" style="display: none;" class="alert alert-danger s-60 pad-10 marg-v-10 center-block">
                                    <div class ="no-pad no-marg text-uppercase"><strong>Errors</strong></div>
                                    <ul id="ajax-error-list" class="list-unstyled small">
                                        <li class = "list-group-item"></li>
                                    </ul>
                                </div>
                                <!--<div id="ajax-ok" style="display: none;" class="alert alert-success s-60 pad-10 center-block small"></div>-->
                                <form id="sub-cat-form" class="form-horizontal" >
                                    <div class="form-group form-group-sm marg-v-20">
                                        <div class="">
                                            <input type='hidden' name='id' value='<?php echo $id ?>'>
                                            <input type='hidden' id="subcategory-id" name='subcategory-id' value=''>
                                            <input type='hidden' id="ajaxMode" name='ajaxMode' value='add'>
                                            <button type="reset" onclick="hideForm();" class="btn btn-default"><?php echo $cancel ?></button>
                                            <button id="btn-submit" type="button" onclick="SendSubCategory('sub-cat-form');" class="btn btn-primary"><?php echo $save ?></button>
                                        </div>
                                    </div>
                                    <div id="del-section" class="row " style="display: none;">
                                        <div class="alert alert-danger bg-red-warning pad-3 s-100 text-whito"><?php echo $deleteQuestion . $questionMark ?></div>
                                        <div class="pad-v-20 marg-v-20">
                                            <div class="col-md-6 ">
                                                <span class="inline-block pad-5"><?php echo $subService ?></span>
                                                <span id="sub-name-del" class="inline-block pad-5 round-10 bg-white-gray text-dark"></span>
                                            </div>
                                            <div class="col-md-6">
                                                <span class="inline-block pad-5"><?php echo $estimatdWaitTime ?></span>
                                                <span id="sub-waittime-del" class="inline-block pad-5 round-10 bg-white-gray text-dark"></span>
                                            </div>
                                        </div>
                                        <div class="marg-h-20 round-10-sh bg-danger pad-5">
                                            <label class="control-label"><?php echo $requiredPapers ?></label>
                                            <div id="req-papers-del" class="bg-white pad-5 round-10 h-350"></div>
                                        </div>
                                    </div>
                                    <div id="field-section" class="" style="display: none;">
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <label for="sub-waittime" class="col-lg-5 control-label"><?php echo $estimatdWaitTime ?></label>
                                                <div class="col-lg-7">
                                                    <input class="form-control form-ok" maxlength="3" name="sub-waittime" id="sub-waittime" placeholder="wait time" type="text">
                                                </div>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="sub-name" class="col-lg-5 control-label"><?php echo $subService ?></label>
                                                <div class="col-lg-7">
                                                    <input class="form-control form-ok" name="sub-name" id="sub-name" placeholder="name" type="text">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="marg-h-20 round-10-sh bg-primary pad-5">
                                            <label for="req-papers" class=" control-label"><?php echo $requiredPapers ?></label>
                                            <div class="h-350" >
                                                <textarea class="form-control form-ok"  name="req-papers" id="req-papers" placeholder="required papers" ></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <?php include_once './common/footer.php'; ?>

        <?php include_once './common/foot_scripts.php'; ?>

        <script src="../js/minified/jquery.sceditor.min.js" type="text/javascript"></script>
        <script src="../js/minified/jquery.sceditor.xhtml.min.js" type="text/javascript"></script>
        <script src="../js/minified/plugins/undo.js" type="text/javascript"></script>
        <script src="../js/minified/plugins/plaintext.js" type="text/javascript"></script>
        <script type="text/javascript">
                                                var text_update = "<?php echo $update ?>";
                                                var text_add = "<?php echo $add ?>";
                                                var text_delete = "<?php echo $delete ?>";
                                                var text_edit = "<?php echo $edit ?>";
                                                var categoryID = <?php echo $id ?>;
                                                
                                                var text_yes = "<?php echo $YES ?>";
                                                var text_no = "<?php echo $NO ?>";

                                                function refreshPage(id) {
                                                    location.replace("?id=" + id);
                                                }
        </script>
        <script src="../js/subcategories.js" type="text/javascript"></script>
    </body>
</html>
