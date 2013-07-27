<?php
// Copyright 2011 JMB Software, Inc.
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//    http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.

require_once('includes/global.php');


Request::Setup();

$page_num = empty($_GET['p']) ? 1 : $_GET['p'];
$template = preg_replace('~[^a-z0-9\-]~i', '', $_GET['t']);

$t = new Template(true, Config::Get('cache_custom'));
$t->Assign('g_config', Config::GetAll());
$t->Assign('g_logged_in', isset($_COOKIE[LOGIN_COOKIE]));

if( isset($_COOKIE[LOGIN_COOKIE]) )
{
    parse_str($_COOKIE[LOGIN_COOKIE], $cookie);
    $t->Assign('g_username', $cookie['username']);
}

$t->Assign('g_page_number', $page_num);
$t->Assign('g_get_vars', $_GET);

$t->Display('custom-' . $template . '.tpl', $template . $page_num . serialize($_GET));

?>