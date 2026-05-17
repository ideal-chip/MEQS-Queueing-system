<?php
$prev = 128;

require_once './common/php_head.php';
//-------------------------------------------------------------< common vars >---
$view = "./views/languages/";
$parent = "";

$title = getTextValue('languages', $lang);

$setDefaultLanguage = getTextValue('setDefaultLanguage', $lang);

//-------------------------------------------------------------< other includes >---
//include_once $view . 'process.php';
//-------------------------------------------------------------< data >---

if (isset($_GET['removelanguage'])) {
    executeQuery("DELETE FROM texts WHERE text_language='" . $_GET['removelanguage'] . "';");
    header("Location: " . basename($_SERVER['PHP_SELF']));
}
if (isset($_GET['setdefault'])) {
    executeQuery("UPDATE settings SET set_value='" . $_GET['setdefault'] . "' WHERE set_key='defaultLanguage';");
    header("Location: " . basename($_SERVER['PHP_SELF']));
}
if (isset($_GET['update'])) {
    $keys = array_keys($_POST);
    $editLanguage = $_GET['editlanguage'];

    $langQuery = "REPLACE INTO texts(text_language,text_key,text_value) VALUES ";
    foreach ($keys as $key) {
        $langQuery = $langQuery . "('$editLanguage','$key','" . trim($_POST[$key]) . "'),";
    }
    $langQuery = substr($langQuery, 0, strlen($langQuery) - 1) . ";";

    executeQuery($langQuery);

    header("Location: " . basename($_SERVER['PHP_SELF']) . "?editlanguage=" . $_GET['editlanguage']);
}

$languages = getColumn("SELECT DISTINCT text_language FROM texts;");
$editLanguage = trim(isset($_GET['editlanguage']) ? $_GET['editlanguage'] : $languages[0]);

$langExists = 0;
foreach ($languages as $language) {
    if ($language == $editLanguage) {
        $langExists = 1;
        break;
    }
}

$defLang = getValue("SELECT set_value FROM settings WHERE set_key='defaultLanguage';");

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
            <div class="well well-header"><?php echo getTextValue("languages", $lang) ?></div>
            <div class="bg-white-gray round-10 pad-10 marg-v-10 s-30 center-block">
                <a href="javascript:void(0)" class="btn btn-default btn-sm " onclick="setDefault('<?php echo $editLanguage ?>');"><?php echo $setDefaultLanguage ?></a>
                <img src='<?php echo $filesPath . "add.png" ?> ' 
                     title='<?php echo getTextValue("add", $lang) ?> ' 
                     alt='<?php echo getTextValue("add", $lang) ?> ' 
                     style='vertical-align:middle;cursor:pointer;' 
                     onclick='addNewLang();'> 
                <img src='<?php echo $filesPath . "delete.png" ?> ' 
                     title='<?php echo getTextValue("delete", $lang) ?> ' 
                     alt='<?php echo getTextValue("delete", $lang) ?>' 
                     style='vertical-align:middle;cursor:pointer;' 
                     onclick='if (confirm("<?php echo getTextValue("deleteQuestion", $lang) . " (" . getTextValue('languageName', $editLanguage) . ")" . getTextValue('questionMark', $lang) ?>"))
                                 location.replace("?removelanguage=<?php echo $editLanguage ?>");'>
            </div>
            <span><?php echo getTextValue("selectLanguage", $lang); ?></span>
            <select class="s-20" class="form-control form-control-sm" onchange="updateEditLang(this);">
                <?php
                foreach ($languages as $language) {
                    $styleSelected = ($language == $editLanguage ? "style='font-weight:bold;'" : "");
                    ?>
                    <option value="<?php echo $language ?>" <?php echo $styleSelected ?> <?php echo ($language == $editLanguage ? " selected" : "") ?> > 
                        <?php echo getTextValue('languageName', $language) ?>  
                    </option>
                    <?php
                }

                if (!$langExists) {
                    ?>
                    <option selected><?php echo $editLanguage ?>  </option>
                    <?php
                }
                ?>
            </select>
            
            <br><br>
            <form method='POST' action='?editlanguage=<?php echo $editLanguage ?>&update=1' >
                <table class='table s-80' border='0' style='margin:auto;text-align:right;'>
                    <tr>
                        <td colspan='2' class='sec-hd' style='text-align:center;'><?php echo getTextValue("categories", $lang) ?>  </td>
                    </tr>
                    <?php
                    $categoriesKeys = getColumn("SELECT category_key FROM categories;");
                    foreach ($categoriesKeys as $catKey) {
                        ?>
                        <tr>
                            <td class='s-20'><?php echo $catKey ?></td>
                            <td ><input type='text' name='<?php echo $catKey ?>' value='<?php echo trim(getValue("SELECT text_value FROM texts WHERE text_key='$catKey' AND text_language='$editLanguage';")) ?>'></td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td colspan='2' class='sec-hd' style='text-align:center;'><?php echo getTextValue("messages", $lang) ?> </td>
                    </tr>
                    <?php
                    for ($i = 0; $i < getSetting("bigdisplayMessageCount"); $i++) {
                        ?>
                        <tr>
                            <td>Message <?php echo ($i + 1) ?>  </td>
                            <td><input type = 'text' name = 'message<?php echo $i ?>' value = '<?php echo trim(getValue("SELECT text_value FROM texts WHERE text_key='message$i' AND text_language='$editLanguage';")) ?>'></td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td colspan='2' class='sec-hd' style='text-align:center;'><?php echo getTextValue("feedbacks", $lang) ?> </td>
                    </tr>
                    <?php
                    for ($i = 0; $i < 5; $i++) {
                        ?>
                        <tr>
                            <td><?php echo getTextValue("feedbackQuestionTxt", $lang) ?> <?php echo ($i + 1) ?>  </td>
                            <td><input type = 'text' name='fb<?php echo $i ?>' value='<?php echo trim(getValue("SELECT text_value FROM texts WHERE text_key='fb$i' AND text_language='$editLanguage';")) ?>'></td>
                        </tr>
                        <?php
                    }
                    ?>

                </table>
                <div class="center-block marg-v-10">
                    <input  class='btn btn-primary pad-h-30' type='submit' value='<?php echo getTextValue('edit', $lang) ?>'>
                </div>
                <div class="sec-hd"><?php echo getTextValue("words", $lang) ?></div>
                <div class="form-inline">
                    <?php
                    if ($file = fopen($filesPath . "keys.txt", "r")) {
                        while (!feof($file)) {
                            $key = trim(fgets($file));
                            $value = trim(getValue("SELECT text_value FROM texts WHERE text_key='$key' AND text_language='$editLanguage';"));
                            ?>
                            <div class="form-group lang-box">
                                <div><?php echo $key ?></div>
                                <div>
                                    <input type='text' name='<?php echo $key ?>' value='<?php echo $value ?>'></div>
                            </div>
                            <?php
                        }
                        fclose($file);
                    }
                    ?>
                </div>
                <div class="center-block marg-v-10">
                    <input  class='btn btn-primary pad-h-30' type='submit' value='<?php echo getTextValue('edit', $lang) ?>'>
                </div>
            </form>
        </div>
        <?php include_once './common/footer.php'; ?>

        <?php include_once './common/foot_scripts.php'; ?>
        <script type="text/javascript">

            function updateEditLang(id) {
                //alert(id.value);
                var newLocation = id.value;
                location.replace("?editlanguage=" + newLocation);
            }

            function addNewLang() {
                var newLang = prompt("<?php echo getTextValue('enterNewLanguage', $lang) ?> " + "ex: ar, en, de, it");
                if (newLang != null && newLang != "") {
                    location.replace("?editlanguage=" + newLang);
                }
            }

            //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|| update language
            var langXML = new XMLHttpRequest();
            var language = '';

            function setDefault(lang) {
                langXML.open('GET', '../api/update.php?type=language&id=1&lang=' + lang);
                langXML.send();
                language = lang;
            }

            langXML.onreadystatechange = function () {
                if (langXML.status == 200 && langXML.readyState == 4) {
                    if (parseInt(langXML.responseText) == 1) {
                        refreshAll();
                    }
                }
            }

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|| refresh all big displays

            var refreshAllXML = new XMLHttpRequest();

            refreshAllXML.onreadystatechange = function () {

                if (refreshAllXML.readyState == 4 && refreshAllXML.status == 200) {
                    if (parseInt(refreshAllXML.responseText) == 1) {
                        location.replace("?language=" + language);
                    }
                }
            };

            function refreshAll() {
                refreshAllXML.open('GET', '../api/update.php?type=allsystem&id=1');
                refreshAllXML.send();
            }
        </script>
    </body>
</html>
