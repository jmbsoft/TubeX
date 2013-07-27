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

if( !preg_match('~/admin$~', realpath(dirname(__FILE__))) )
{
    echo "This file must be located in the admin directory of your TubeX installation";
    exit;
}

define('TUBEX_CONTROL_PANEL', true);
require_once('includes/cp-global.php');

if( $_SERVER['REQUEST_METHOD'] == 'POST' )
{
    $DB = GetDB();

    $reset = true;
    $password = RandomPassword();
    $cp_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/index.php";

    $DB->Update('DELETE FROM `tbx_administrator` WHERE `username`=?', array('administrator'));
    $DB->Update('INSERT INTO `tbx_administrator` VALUES (?,?,?,?,?,?)',
                array('administrator',
                      sha1($password),
                      'webmaster@' . preg_replace('~^www\.~', '', $_SERVER['HTTP_HOST']),
                      'Administrator',
                      'Superuser',
                      0));
}

$fp = fopen(__FILE__, 'r');
fseek($fp, __COMPILER_HALT_OFFSET__);
eval(stream_get_contents($fp));
fclose($fp);

__halt_compiler();?>
<html>
<head>
  <title>Reset TubeX Control Panel Access</title>
</head>
<body>

<h1>Reset TubeX Control Panel Access</h1>

<?php if( !isset($reset) ): ?>
<form action="reset-access.php" method="post">
  Pressing the button below will restore the default control panel administrator account.
  <br /><br />
  <input type="submit" value="Reset Access" />
</form>
<?php else: ?>
The default control panel administrator account has been restored. Use the information below to login to the control panel.
<br />
<span style="color: red; font-weight: bold;">Be sure to remove this file from your server after you write down your username and password</span>
<br /><br />
<b>Control Panel URL:</b> <a href="<?php echo htmlspecialchars($cp_url); ?>"><?php echo htmlspecialchars($cp_url); ?></a><br />
<b>Username:</b> administrator<br />
<b>Password:</b> <?php echo htmlspecialchars($password); ?>
<?php endif; ?>


</body>
</html>
