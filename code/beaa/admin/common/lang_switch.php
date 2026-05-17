<div class="pad-h-v text-center">
    <?php
    foreach ($languages as $language) {
        ?>
    <a class="btn btn-default btn-xs s-90" href="javascript:void(0)" onclick="updateLang('<?php echo $language ?>')" >
            <?php echo getTextValue('languageName', $language) ?>  
        </a>
        <?php
    }
    ?>
</div>
<!--<div class="lang-con-sm">
    <div class="btn-inline"><input type="button" id="admin" value="<?php echo getTextValue('adminOnly', $lang) ?>" class="btn btn-xs lang-radio pressed"> </div>
    <div class="btn-inline"><input type="button" id="all" value="<?php echo getTextValue('allSystem', $lang) ?>" class="btn btn-xs lang-radio"></div>
</div>-->