
<div class="well well-header"><?php echo getTextValue("addEditAudios", $lang) ?></div>
<?php
if (count($errorList) > 0) {
    ?>
    <div class = "s-50 center-block no-pad">
        <div class = "alert alert-danger no-pad no-marg text-uppercase"><strong>Errors</strong></div>
        <ul id = "error-list" class = "list-unstyled alert alert-danger">
            <?php
            foreach ($errorList as $error) {
                ?> 
                <li class = "error-item"><?php echo $error ?></li>
                <?php
            }
            ?>
        </ul>
    </div>
    <?php
}
?>

<form method='POST' action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" >

    <table class="table">
        <!--<tr><th colspan='2'><?php echo getTextValue("addEditAudios", $lang) ?></th></tr>-->
        <tr>
            <td><?php echo getTextValue("audioName", $lang) ?></td>
            <td><input type='text' name='name' value='<?php echo $audioName ?>'></td>
        </tr>
        <tr>
            <td><?php echo getTextValue("audioPath", $lang) ?></td>
            <td><input type='text' name='path' value='<?php echo $audioPath ?>' style='text-align:left; direction:ltr;'></td>
        </tr>
        <tr>
            <td><?php echo getTextValue("audioLanguage", $lang) ?></td>
            <td>
                <select style='width:100%;' name='audiolang'>
                    <?php
                    $languages = getColumn("SELECT DISTINCT text_language FROM texts;");
                    foreach ($languages as $language) {
                        ?>
                        <option value ='<?php echo $language ?>' <?php echo ($audioLanguage == $language ? ' selected' : '') ?> > <?php echo getTextValue("languageName", $language) ?>  </option>
                        <?php
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><?php echo getTextValue("gender", $lang) ?></td>
            <td>
                <select style='width:100%;' name='gender'>
                    <?php
                    //$genders = array('female','male');

                    foreach ($genders as $index => $genderValue) {
                        ?>
                        <option value='<?php echo $index ?>' <?php echo ($index == $audioGender ? 'selected' : '') ?> ><?php echo $genderValue ?></option>
                        <?php
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan='2'>
                <input type='hidden' name='mode' value='<?php echo $editmode ? "edit" : "add" ?>'>
                <input type='hidden' name='id' value='<?php echo $id ?>'>
                <div class="center-block s-100 well well-sm ">
                    <a href="?mode=list" class="btn btn-default btn-sm"><?php echo getTextValue("cancel", $lang) ?></a>
                    <input class="btn btn-primary btn-sm s-30" type='submit' value='<?php echo getTextValue($editmode ? "edit" : "add", $lang) ?>'>
                </div>

            </td>
        </tr>
    </table>
</form>
