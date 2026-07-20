<?php
$audioList = getArray("SELECT * FROM audios;");


$gender = getTextValue("gender", $lang);
$audioName = getTextValue("audioName", $lang);
$audios = getTextValue("audios", $lang);
$audioPath = getTextValue("audioPath", $lang);
$audioLanguage = getTextValue("audioLanguage", $lang);
$dir = trim(getTextValue('dir', $lang));
$update = getTextValue("update", $lang);
$clear = getTextValue('clear', $lang);
$active = getTextValue('active', $lang);
$inactive = getTextValue('inactive', $lang);
$useSimpleNotification = getTextValue('useSimpleNotification', $lang);

$shortBeep = getSetting('audioShortBeep');
if ($shortBeep == 'active') {
    $btnClass = 'btn-success';
    $btnValue = 1;
    $btnText = $active;
} else {
    $btnClass = 'btn-danger';
    $btnValue = 0;
    $btnText = $inactive;
}
?>

<div class="well well-header"><?php echo $audios ?></div>
<div class="s-50 pad-v-10 center-block bg-white-gray round-10-sh">
    <div class="inline-block pad-h-10"><?php echo $useSimpleNotification ?></div>
    <button id="short-beep" class="btn btn-sm <?php echo $btnClass ?>" value="<?php echo $btnValue ?>"><?php echo $btnText ?></button>
</div>
<div class="s-80 center-block pad-v-10">
    <table class="table table-striped s-100" >
        <tr>
            <th></th>
            <th ><?php echo $audioName ?></th>
            <th ><?php echo $audioPath ?></th>
            <th ><?php echo $audioLanguage ?></th>
            <th ><?php echo $gender ?></th>
        <tr>
            <?php
            foreach ($audioList as $row) {
                ?>
            <tr>
                <td >
                    <img style='width:16px;height:16px;cursor:pointer' 
                         src='<?php echo $filesPath . "/delete.png" ?>' 
                         onclick='if (confirm("<?php echo getTextValue("deleteQuestion", $lang) . getTextValue("questionMark", $lang) ?>"))
                                         location.replace("<?php echo "?mode=delete&id=" . $row['audio_id'] ?>")'>
                </td>
                <td >
                    <a href='<?php echo "?mode=edit&id=" . $row['audio_id'] ?>'><?php echo $row['audio_name'] ?>  </a>
                </td>
                <td style='text-align:left;direction:ltr;'><?php echo $row['audio_path'] ?></td>
                <td ><?php echo getTextValue("languageName", $row['audio_language']) ?> </td>
                <td ><?php echo $genders[$row['audio_gender']] ?>  </td>
            </tr>
            <?php
        }
        ?>
    </table>  
</div>
<a href='?mode=add'>
    <img src='<?php echo $filesPath . "/add.png" ?>' title='<?php echo getTextValue("add", $lang) ?>'>
</a>

<script type="text/javascript">
    var lang_active = '<?php echo $active ?>';
    var lang_inactive = '<?php echo $inactive ?>';

</script>

