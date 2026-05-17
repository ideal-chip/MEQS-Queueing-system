<div class="well well-header"><?php echo getTextValue("addEditCounterCategories", $lang) ?></div>
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
        <tr>
            <td><?php echo getTextValue("counter", $lang) ?></td>
            <td>
                <select style='width:100%;' name='counter' id="counter" onchange="UpdateCatList();">
                    <?php
                    $counters = getColumn("SELECT counter_id FROM counters;");
                    foreach ($counters as $counter)
                        echo "<option value='$counter'" . ($ccCounter == $counter ? ' selected' : '') . ">" . getValue("SELECT counter_name FROM counters WHERE counter_id=$counter;") . "</option>";
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><?php echo getTextValue("category", $lang) ?></td>
            <td>
                <select style='width:100%;' name='category' >
                    <?php
                    $catListQuery = "SELECT category_id, category_key FROM categories;";
                    $categoryListInfo = getKeyValArray(getArrayAssoc($catListQuery), 'category_id', 'category_key');

                    foreach ($categoryListInfo as $categoryID => $categoryKey) {
                        $catName = $categoryKey . " - " . getTextValue($categoryKey, $lang);
                        ?>
                        <option value='<?php echo $categoryID ?>' <?php echo ($ccCategory == $categoryID ? ' selected' : '') ?>  > <?php echo $catName; ?> </option>
                        <?php
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan='2'>
                <div class="btn-group s-99">
                    <input type='hidden' name='mode' value='<?php echo $editmode ? "edit" : "add" ?>'>
                    <input type='hidden' name='id' value='<?php echo $id ?>'>
                    <div class="center-block s-100 well well-sm ">
                        <a href="?mode=list" class="btn btn-default btn-sm"><?php echo getTextValue("cancel", $lang) ?></a>
                        <input class="btn btn-primary btn-sm s-30" type='submit' value='<?php echo getTextValue($editmode ? "edit" : "add", $lang) ?>'>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</form>