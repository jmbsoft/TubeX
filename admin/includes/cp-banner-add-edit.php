    <div id="dialog-header" class="ui-widget-header ui-corner-all">
      <div id="dialog-close"></div>
      <?php echo (isset($editing) ? 'Update a Banner' : 'Add a Banner'); ?>
    </div>

    <form method="post" action="ajax.php" enctype="multipart/form-data">

      <div id="dialog-panel">
        <div style="padding: 8px;">

          <div id="dialog-help">
            <a href="docs/cp-banner.html" target="_blank"><img src="images/help-22x22.png" alt="Help" title="Help" border="0" /></a>
          </div>

          <div class="field">
            <label>Sponsor:</label>
            <span class="field-container">
              <select name="sponsor_id">
                <option value="">-- None --</option>
                <?php
                $DB = GetDB();
                $sponsors = $DB->FetchAll('SELECT `sponsor_id`,`name` FROM `tbx_sponsor` ORDER BY `name`');
                echo Form_Field::Options($sponsors, Request::Get('sponsor_id'), 'sponsor_id', 'name');
                ?>
              </select>
            </span>
          </div>

          <div class="field">
            <label>Zone:</label>
            <span class="field-container"><input type="text" size="40" name="zone" value="<?php echo Request::Get('zone'); ?>" acomplete="tbx_banner.zone" /></span>
          </div>

          <div class="field">
            <label>Tags:</label>
            <span class="field-container"><input type="text" size="100" name="tags" value="<?php echo Request::Get('tags'); ?>" /></span>
          </div>

          <div class="field">
            <label>Times Displayed:</label>
            <span class="field-container"><input type="text" size="5" name="times_displayed" value="<?php echo Request::Get('times_displayed'); ?>" /></span>
          </div>

          <div class="field">
            <label>Upload Content:</label>
            <span class="field-container">
              <input type="file" name="upload_file" size="50" />
            </span>
          </div>

          <div class="field">
            <label>Banner HTML:</label>
            <span class="field-container">
              <textarea name="banner_html" rows="8" style="width: 600px;"><?php echo Request::Get('banner_html'); ?></textarea>
            </span>
          </div>

        </div>
      </div>

      <div id="dialog-buttons">
        <img src="images/activity-16x16.gif" height="16" width="16" border="0" title="Working..." />
        <input type="submit" id="button-save" value="<?php echo (isset($editing) ? 'Save Changes' : 'Add Banner') ?>" />
        <input type="button" id="dialog-button-cancel" value="Cancel" style="margin-left: 10px;" />
      </div>

      <input type="hidden" name="r" value="<?php echo (isset($editing) ? 'tbxGenericEdit' : 'tbxGenericAdd') ?>(banner)" />
      <input type="hidden" name="banner_id" value="<?php echo Request::Get('banner_id'); ?>" />
    </form>