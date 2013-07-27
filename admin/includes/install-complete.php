<?php
if( !defined('TUBEX_CONTROL_PANEL') ) die('Invalid Access');

require_once('cp-global-header.php');
?>

<div class="centerer">
  <span class="centerer">

    <div class="fieldset">
      <div class="legend">Installation Complete</div>

      The software installation has been completed successfully. Use the information below to login to the control panel.

      <br />
      <br />

      <b>Control Panel URL:</b> <a href="<?php echo $control_panel_url; ?>" onclick="return confirm('Have you written down your username and password?')"><?php echo $control_panel_url; ?></a><br />
      <b>Username:</b> administrator<br />
      <b>Password:</b> <?php echo htmlspecialchars($password); ?>

    </div>

  </span>
</div>

<?php require_once('cp-global-footer.php'); ?>