<?php
if( !defined('TUBEX_CONTROL_PANEL') ) die('Invalid Access');

require_once('cp-global-header.php');
?>

<div class="centerer">
  <span class="centerer">

    <div class="fieldset" style="width: 500px;">
      <div class="legend">Enter MySQL Database Information</div>

      <div style="margin-bottom: 15px;">
        Please enter your MySQL database information in the fields below.
      </div>

      <?php if( isset($e) ): ?>
      <div class="message-error" style="margin-bottom: 15px;">
        <?php echo $e->getMessage(); ?><br />
        <?php if( method_exists($e, 'getExtras') ): ?><?php echo $e->getExtras(); ?><br /><?php endif; ?>
        Please double check your MySQL information and try again.
      </div>
      <?php endif; ?>

      <form method="post" action="install.php">

        <div class="field">
          <label>MySQL Username:</label>
          <span class="field-container">
            <input type="text" name="db_username" value="<?php echo Request::Get('db_username'); ?>" size="20" />
          </span>
        </div>

        <div class="field">
          <label>MySQL Password:</label>
          <span class="field-container">
            <input type="text" name="db_password" value="<?php echo Request::Get('db_password'); ?>" size="20" />
          </span>
        </div>

        <div class="field">
          <label>MySQL Database Name:</label>
          <span class="field-container">
            <input type="text" name="db_database" value="<?php echo Request::Get('db_database'); ?>" size="20" />
          </span>
        </div>

        <div class="field">
          <label>MySQL Hostname:</label>
          <span class="field-container">
            <input type="text" name="db_hostname" value="<?php echo Request::Get('db_hostname'); ?>" size="20" />
          </span>
        </div>

        <div class="field">
          <label></label>
          <span class="field-container">
            <input type="submit" value="Submit" />
          </span>
        </div>

      </form>

    </div>

  </span>
</div>

<?php require_once('cp-global-footer.php'); ?>