<?php
$DB = GetDB();
$sponsors = $DB->FetchAll('SELECT `sponsor_id`,`name` FROM `tbx_sponsor`', array(), 'sponsor_id');
?>