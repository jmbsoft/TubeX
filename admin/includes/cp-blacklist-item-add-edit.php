    <div id="dialog-header" class="ui-widget-header ui-corner-all">
      <div id="dialog-close"></div>
      <?php echo (isset($editing) ? 'Update a Blacklist Item' : 'Add a Blacklist Item'); ?>
    </div>

    <form method="post" action="ajax.php">
      <div id="dialog-panel">
        <div style="padding: 8px;">

          <div id="dialog-help">
            <a href="docs/cp-blacklist.html" target="_blank"><img src="images/help-22x22.png" alt="Help" title="Help" border="0" /></a>
          </div>

          <div class="field">
            <label>Type:</label>
            <span class="field-container">
              <select name="type">
                <?php
                $opts = array('User IP','E-mail','Domain/URL','Domain IP','Word','HTML','HTTP Header','DNS Server');
                echo Form_Field::OptionsSimple($opts, Request::Get('type'));
                ?>
              </select>
            </span>
          </div>

          <div class="field">
            <label></label>
            <span class="field-container">
              <div class="checkbox">
                <input type="hidden" name="regex" value="<?php echo Request::Get('regex'); ?>" />
                Regular expression
              </div>
            </span>
          </div>

          <div class="field">
            <label>Value:</label>
            <span class="field-container"><input type="text" size="60" name="value" value="<?php echo Request::Get('value'); ?>" /></span>
          </div>

          <div class="field">
            <label>Reason:</label>
            <span class="field-container"><input type="text" size="60" name="reason" value="<?php echo Request::Get('reason'); ?>" /></span>
          </div>


        </div>
      </div>

      <div id="dialog-buttons">
        <img src="images/activity-16x16.gif" height="16" width="16" border="0" title="Working..." />
        <input type="submit" id="button-save" value="<?php echo (isset($editing) ? 'Save Changes' : 'Add Blacklist Item') ?>" />
        <input type="button" id="dialog-button-cancel" value="Cancel" style="margin-left: 10px;" />
      </div>

      <input type="hidden" name="r" value="<?php echo (isset($editing) ? 'tbxGenericEdit' : 'tbxGenericAdd') ?>(blacklist-item)" />
      <input type="hidden" name="blacklist_id" value="<?php echo Request::Get('blacklist_id'); ?>" />
    </form>