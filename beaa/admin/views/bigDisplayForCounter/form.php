<div class="well well-header"><?php echo getTextValue("addEditBigDisplayForCounter", $lang) ?></div>
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
            <td><?php echo getTextValue("bigDisplayName", $lang) ?></td>
            <td>
                <select style='width:100%;' name='bdid'>
                    <?php
                    $bigdisplays = getArray("SELECT display_id, display_name, display_number FROM bigdisplays;");
                    foreach ($bigdisplays as $bd) {
                        ?>
                        <option value='<?php echo $bd['display_id'] ?>' <?php echo ($bd['display_id'] == $displayID ? ' selected' : '') ?> > <?php echo $bd['display_number'] . "-" . $bd['display_name'] ?>  </option>
                        <?php
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><?php echo getTextValue("counter", $lang) ?></td>
            <td>
                <select style='width:100%;' name='counterid'>
                    <?php
                    $counters = getArray("SELECT counter_id, counter_name FROM counters;");
                    foreach ($counters as $counter) {
                        ?>
                        <option value='<?php echo $counter['counter_id'] ?>' <?php echo ($counter['counter_id'] == $CounterID ? ' selected' : '') ?> > <?php echo $counter['counter_name'] ?>  </option>
                        <?php
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><?php echo getTextValue("numberOftickets", $lang) ?></td>
            <td><input type='text' maxlength="2" name='quantity' value='<?php echo $quantity ?>'></td>
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