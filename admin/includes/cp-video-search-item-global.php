<?php
$DB = GetDB();
$categories = $DB->FetchAll('SELECT `category_id`,`name` FROM `tbx_category`', array(), 'category_id');
$sponsors = $DB->FetchAll('SELECT `sponsor_id`,`name` FROM `tbx_sponsor`', array(), 'sponsor_id');
$rejections = $DB->FetchAll('SELECT `reason_id`,`short_name` FROM `tbx_reason` WHERE type=? ORDER BY `short_name`', array('Reject Video'));
$custom_schema = $DB->FetchAll('SELECT * FROM `tbx_video_custom_schema` ORDER BY `field_id`');
?>

