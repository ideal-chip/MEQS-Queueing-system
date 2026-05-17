<?php

function getBigDisplayType($id) {
    return getValue("SELECT bdtype_name FROM bigdisplaytypes WHERE bdtype_id = $id;");
}

$ARROW_TOTAL_SIZE = 5;
function getArrowDirection($val){
    $dir = 'ERROR';
    $value = intval($val);
    switch ($value) {
        case 0:
            $dir = 'NONE';
            break;
        case 1:
            $dir = 'LEFT';
            break;
        case 2:
            $dir = 'RIGHT';
            break;
        case 3:
            $dir = 'UP';
            break;
        case 4:
            $dir = 'DOWN';
            break;
        default:
            break;
    }
    return $dir;
}

?>
