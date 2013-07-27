    <div id="dialog-header" class="ui-widget-header ui-corner-all">
      <div id="dialog-close"></div>
      <?php echo (isset($editing) ? 'Update a User Level' : 'Add a User Level'); ?>
    </div>

    <form method="post" action="ajax.php">
      <div id="dialog-panel">
        <div style="padding: 8px;">

          <div id="dialog-help">
            <a href="docs/cp-user-level.html" target="_blank"><img src="images/help-22x22.png" alt="Help" title="Help" border="0" /></a>
          </div>

          <div class="field">
            <label>Name:</label>
            <span class="field-container"><input type="text" size="60" name="name" value="<?php echo Request::Get('name'); ?>" /></span>
          </div>

          <div class="field">
            <label>View Limit:</label>
            <span class="field-container"><input type="text" size="60" name="daily_view_limit" value="<?php echo Request::Get('daily_view_limit'); ?>" /></span>
          </div>

          <div class="field">
            <label>Bandwidth Limit:</label>
            <span class="field-container"><input type="text" size="60" name="daily_bandwidth_limit" value="<?php echo Request::Get('daily_bandwidth_limit'); ?>" /></span>
          </div>

          <div class="field">
            <label></label>
            <span class="field-container">
              <div class="checkbox">
                <input type="hidden" name="is_guest" value="<?php echo Request::Get('is_guest'); ?>" />
                Use for guests
              </div>
            </span>
          </div>

          <div class="field">
            <label></label>
            <span class="field-container">
              <div class="checkbox">
                <input type="hidden" name="is_default" value="<?php echo Request::Get('is_default'); ?>" />
                Use as default
              </div>
            </span>
          </div>

        </div>
      </div>

      <div id="dialog-buttons">
        <img src="images/activity-16x16.gif" height="16" width="16" border="0" title="Working..." />
        <input type="submit" id="button-save" value="<?php echo (isset($editing) ? 'Save Changes' : 'Add User Level') ?>" />
        <input type="button" id="dialog-button-cancel" value="Cancel" style="margin-left: 10px;" />
      </div>

      <input type="hidden" name="r" value="<?php echo (isset($editing) ? 'tbxGenericEdit' : 'tbxGenericAdd') ?>(user-level)" />
      <input type="hidden" name="user_level_id" value="<?php echo Request::Get('user_level_id'); ?>" />
    </form>