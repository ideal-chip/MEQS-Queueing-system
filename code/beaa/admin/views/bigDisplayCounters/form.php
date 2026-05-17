
<div class="well well-header"><?php echo getTextValue("addEditBigDisplayCounters", $lang) ?></div>
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

                                                                                                            <table class="table">                                                                                                                                                                                                                          <!--<tr><th colspan='2'><?php echo getTextValue("addEditBigDisplayCounters", $lang) ?></th></tr>-->
        <tr><td><?php echo getTextValue("bigDisplay", $lang) ?></td>
            <td>
                <select style='width:100%;' name='bigdisplay'>
                    <?php
                    $bigdiss = getColumn("SELECT display_id FROM bigdisplays;");
                    foreach ($bigdiss as $bigdis)
                        echo "<option value='$bigdis'" . ($bigdis == $bigdisplay ? ' selected' : '') . ">" . getValue("SELECT display_name FROM bigdisplays WHERE display_id=$bigdis;") . "</option>";
                    ?>
                </select>
            </td>
        </tr>
        <tr><td><?php echo getTextValue("counter", $lang) ?></td>
            <td>
                <select style='width:100%;' name='counter'>
                    <?php
                    $counters = getColumn("SELECT counter_id FROM counters;");
                    foreach ($counters as $myCounter)
                        echo "<option value='$myCounter'" . ($myCounter == $counter ? ' selected' : '') . ">" . getValue("SELECT counter_name FROM counters WHERE counter_id=$myCounter;") . "</option>";
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