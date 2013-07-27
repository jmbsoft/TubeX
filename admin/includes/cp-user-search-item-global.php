<?php
$DB = GetDB();
$user_levels = $DB->FetchAll('SELECT * FROM `tbx_user_level`', array(), 'user_level_id');
$custom_schema = $DB->FetchAll('SELECT * FROM `tbx_user_custom_schema` ORDER BY `field_id`');
$rejections = $DB->FetchAll('SELECT `reason_id`,`short_name` FROM `tbx_reason` WHERE type=? ORDER BY `short_name`', array('Reject User'));
?>

