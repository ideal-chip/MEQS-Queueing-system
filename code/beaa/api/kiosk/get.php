<?php
error_reporting(0); 
require_once('../db.php');
$kioskID=$_GET['kiosk'];
$language=$_GET['language'];
$zoneID=getValue("select kiosk_zone from kiosks where kiosk_id=$kioskID;");
$arr=array();
// id = parent id
if(isset($_GET['id']))
{
        // get services for kiosk based on id [parent]
	if($_GET['id']!='languages')
	{
		$categories=getColumn("select category_id from categories where category_enabled=1 and category_parent=".$_GET['id'].";");
		for($i=0;$i<count($categories);$i++)
		{
			$row=getRow("select c1.category_id as 'ID',texts.text_value as 'Name',c1.category_data as 'Data',(select count(*) from categories c2 where c2.category_parent = ".$categories[$i].") as 'SubItems'
			FROM categories c1,kioskbuttons,texts
			WHERE c1.category_zone=$zoneID
			AND c1.category_key=texts.text_key
			AND texts.text_language='$language'
			AND c1.category_id=".$categories[$i].";");
			array_push($arr,$row);
		}
	}
	else // get languages where id == 'languages'
	{
		$languages=getColumn("SELECT DISTINCT text_language FROM texts;");
		for($i=0;$i<count($languages);$i++)
		{
			$row=getRow("SELECT CONCAT('_',text_language) as 'ID',text_value as 'Name' ,NULL as 'Data',0 as 'hasSubItems' FROM texts WHERE text_language='".$languages[$i]."' and text_key='languageName';");
			array_push($arr,$row);
		}
	}
}
else
{
	$categories=getColumn("select kb_category from kioskbuttons where kb_kiosk=$kioskID;");
	for($i=0;$i<count($categories);$i++)
	{
		$row=getRow("select c1.category_id as 'ID',texts.text_value as 'Name'
		FROM categories c1,kioskbuttons,texts
		WHERE c1.category_zone=$zoneID
		AND c1.category_id=".$categories[$i]."
		AND c1.category_key=texts.text_key
		AND texts.text_language='$language'
		AND kb_category=category_id;");
		array_push($arr,$row);
	}
}

//var_dump($arr);
echo json_encode($arr);
?>