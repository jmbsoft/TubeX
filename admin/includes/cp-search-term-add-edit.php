    <div id="dialog-header" class="ui-widget-header ui-corner-all">
      <div id="dialog-close"></div>
      <?php echo (isset($editing) ? 'Update a Search Term' : 'Add a Search Term'); ?>
    </div>

    <form method="post" action="ajax.php">
      <div id="dialog-panel">
        <div style="padding: 8px;">

          <div id="dialog-help">
            <a href="docs/cp-search-term.html" target="_blank"><img src="images/help-22x22.png" alt="Help" title="Help" border="0" /></a>
          </div>

          <div class="field">
            <label>Search Term:</label>
            <span class="field-container"><input type="text" size="60" name="term" value="<?php echo Request::Get('term'); ?>" /></span>
          </div>

          <div class="field">
            <label>Frequency:</label>
            <span class="field-container"><input type="text" size="5" name="frequency" value="<?php echo Request::Get('frequency'); ?>" /></span>
          </div>


        </div>
      </div>

      <div id="dialog-buttons">
        <img src="images/activity-16x16.gif" height="16" width="16" border="0" title="Working..." />
        <input type="submit" id="button-save" value="<?php echo (isset($editing) ? 'Save Changes' : 'Add Search Term') ?>" />
        <input type="button" id="dialog-button-cancel" value="Cancel" style="margin-left: 10px;" />
      </div>

      <input type="hidden" name="r" value="<?php echo (isset($editing) ? 'tbxGenericEdit' : 'tbxGenericAdd') ?>(search-term)" />
      <input type="hidden" name="term_id" value="<?php echo Request::Get('term_id'); ?>" />
    </form>