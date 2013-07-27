    <div id="dialog-header" class="ui-widget-header ui-corner-all">
      <div id="dialog-close"></div>
      <?php echo (isset($editing) ? 'Update a Reason' : 'Add a Reason'); ?>
    </div>

    <form method="post" action="ajax.php">
      <div id="dialog-panel">
        <div style="padding: 8px;">

          <div id="dialog-help">
            <a href="docs/cp-reason.html" target="_blank"><img src="images/help-22x22.png" alt="Help" title="Help" border="0" /></a>
          </div>

          <div class="field">
            <label>Type:</label>
            <span class="field-container">
              <select name="type">
                <?php
                $opts = array('Reject User','Reject Video','Flag','Feature');
                echo Form_Field::OptionsSimple($opts, Request::Get('type'));
                ?>
              </select>
            </span>
          </div>

          <div class="field">
            <label>Short Name:</label>
            <span class="field-container"><input type="text" size="60" name="short_name" value="<?php echo Request::Get('short_name'); ?>" /></span>
          </div>

          <div class="field">
            <label>Description:</label>
            <span class="field-container"><textarea name="description" rows="4" cols="80"><?php echo Request::Get('description'); ?></textarea></span>
          </div>


        </div>
      </div>

      <div id="dialog-buttons">
        <img src="images/activity-16x16.gif" height="16" width="16" border="0" title="Working..." />
        <input type="submit" id="button-save" value="<?php echo (isset($editing) ? 'Save Changes' : 'Add Reason') ?>" />
        <input type="button" id="dialog-button-cancel" value="Cancel" style="margin-left: 10px;" />
      </div>

      <input type="hidden" name="r" value="<?php echo (isset($editing) ? 'tbxGenericEdit' : 'tbxGenericAdd') ?>(reason)" />
      <input type="hidden" name="" value="<?php echo Request::Get(''); ?>" />
    </form>