    <div id="dialog-header" class="ui-widget-header ui-corner-all">
      <div id="dialog-close"></div>
      <?php echo (isset($editing) ? 'Update a Video Comment' : 'Add a Video Comment'); ?>
    </div>

    <form method="post" action="ajax.php">
      <div id="dialog-panel">
        <div style="padding: 8px;">

          <div id="dialog-help">
            <a href="docs/cp-video-comment.html" target="_blank"><img src="images/help-22x22.png" alt="Help" title="Help" border="0" /></a>
          </div>

          <div class="field">
            <label>Comment:</label>
            <span class="field-container"><textarea name="comment" rows="20" cols="90"><?php echo Request::Get('comment'); ?></textarea></span>
          </div>

        </div>
      </div>

      <div id="dialog-buttons">
        <img src="images/activity-16x16.gif" height="16" width="16" border="0" title="Working..." />
        <input type="submit" id="button-save" value="<?php echo (isset($editing) ? 'Save Changes' : 'Add Video Comment') ?>" />
        <input type="button" id="dialog-button-cancel" value="Cancel" style="margin-left: 10px;" />
      </div>

      <input type="hidden" name="r" value="<?php echo (isset($editing) ? 'tbxGenericEdit' : 'tbxGenericAdd') ?>(video-comment)" />
      <input type="hidden" name="comment_id" value="<?php echo Request::Get('comment_id'); ?>" />
      <input type="hidden" name="video_id" value="<?php echo Request::Get('video_id'); ?>" />
    </form>