    <div id="dialog-header" class="ui-widget-header ui-corner-all">
      <div id="dialog-close"></div>
      <?php echo (isset($editing) ? 'Update a Sponsor' : 'Add a Sponsor'); ?>
    </div>

    <form method="post" action="ajax.php">
      <div id="dialog-panel">
        <div style="padding: 8px;">

          <div class="fieldset">
            <div class="legend">Default Fields</div>

            <div id="dialog-help">
              <a href="docs/cp-sponsor.html" target="_blank"><img src="images/help-22x22.png" alt="Help" title="Help" border="0" /></a>
            </div>

            <div class="field">
              <label>Sponsor Name:</label>
              <span class="field-container"><input type="text" size="60" name="name" value="<?php echo Request::Get('name'); ?>" /></span>
            </div>

            <div class="field">
              <label>Sponsor URL:</label>
              <span class="field-container"><input type="text" size="60" name="url" value="<?php echo Request::Get('url'); ?>" /></span>
            </div>

            <div class="field">
              <label>2257 URL:</label>
              <span class="field-container"><input type="text" size="60" name="us2257_url" value="<?php echo Request::Get('us2257_url'); ?>" /></span>
            </div>

          </div>

          <div class="fieldset">
            <div class="legend">Custom Fields</div>

            <?php echo Form_Field::GenerateFromCustom('sponsor'); ?>

          </div>

        </div>
      </div>

      <div id="dialog-buttons">
        <img src="images/activity-16x16.gif" height="16" width="16" border="0" title="Working..." />
        <input type="submit" id="button-save" value="<?php echo (isset($editing) ? 'Save Changes' : 'Add Sponsor') ?>" />
        <input type="button" id="dialog-button-cancel" value="Cancel" style="margin-left: 10px;" />
      </div>

      <input type="hidden" name="r" value="<?php echo (isset($editing) ? 'tbxGenericEdit' : 'tbxGenericAdd') ?>(sponsor)" />
      <input type="hidden" name="sponsor_id" value="<?php echo Request::Get('sponsor_id'); ?>" />
    </form>