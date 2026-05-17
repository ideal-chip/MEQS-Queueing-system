<?php
$parent = "/idealqv3/admin/";
$links = array();
$links['home'] = createLink('home', 'index', $parent);

$links['flow'] = createLink(getTextValue('flow', $lang), 'flow', $parent);
$links['followups'] = createLink(getTextValue('followupCards', $lang), 'followups', $parent);
$links['feedbacks'] = createLink(getTextValue('feedbacks', $lang), 'feedbacks', $parent);

function renderLink($link) {
    $icon = '';
    if (!empty($link->icon)) {
        $icon = "<i class='$link->icon'></i>";
    }
    echo "<a href=" . $link->fullLink() . " class='' > $link->name $icon</a>";
}
