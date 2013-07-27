<?php
if( !defined('TUBEX_CONTROL_PANEL') ) die('Invalid Access');

require_once('cp-global-header.php');
?>

<div class="centerer">
  <div style="font-size: 20pt; font-weight: bold; margin-bottom: 20px;">
    INSTALLATION PRE-TESTS FAILED
  </div>

  <span class="centerer">
    <div class="message-error" style="margin-bottom: 30px;">
      <span style="font-size: 14pt;">The following issues have been found.  Please fix these and then reload this page.</span>

      <ul>
        <?php foreach( $errors as $error ): ?>
        <li><?php echo $error; ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

  </span>
</div>

<?php require_once('cp-global-footer.php'); ?>