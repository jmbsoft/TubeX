<?php
if( !defined('TUBEX_CONTROL_PANEL') ) die('Invalid Access');
require_once('cp-global-header.php');
?>


<?php if( isset($bad_browser) && $bad_browser ): ?>
<div class="dialog-alert" style="margin-top: 30px;">
Unsupported web browser detected!
</div>

<div class="text-center bold">The control panel interface requires a modern web browser.<br />
Firefox 3+ is recommended, however you can also use Safari 4+ or Internet Explorer 8+.</div>

<?php else: ?>


<?php if( !$is_firefox ): ?>
<div class="text-center bold larger" style="position: relative; top: 10px; font-size: 120%;">
<a href="http://www.getfirefox.com/" target="_blank"><img src="images/firefox-32x32.png" title="Get Firefox" style="vertical-align: middle;" border="0" /></a>
Firefox 3+ is recommended for the control panel interface!
</div>
<?php endif; ?>

<form method="post" action="index.php">

  <div class="fieldset centered" style="margin-top: 3em; width: 30em;">
    <div class="legend">TubeX Control Panel Login</div>

      <img src="images/login-64x64.png" border="0" width="64" height="64" alt="Login" id="login-key" />

      <div class="field">
        <label>Username:</label>
        <span class="field-container">
          <input type="text" name="<?php echo Authenticate::FIELD_USERNAME ?>" size="20" />
        </span>
      </div>

      <div class="field">
        <label>Password:</label>
        <span class="field-container">
          <input type="password" name="<?php echo Authenticate::FIELD_PASSWORD ?>" size="20" />
        </span>
      </div>

      <div class="field">
        <label></label>
        <span class="field-container">
          <div class="checkbox">
            <input type="hidden" name="<?php echo Authenticate::FIELD_REMEMBER ?>" />
            Remember Me
          </div>
        </span>
      </div>

      <div class="field">
        <label>&nbsp;</label>
        <span class="field-container"><input type="submit" value="Login" /></span>
      </div>
    </div>

  <input type="hidden" name="r" value="tbxIndexShow" />
</form>

<?php endif; ?>

<?php require_once('cp-global-footer.php'); ?>