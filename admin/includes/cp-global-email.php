    <div id="dialog-header" class="ui-widget-header ui-corner-all">
      <div id="dialog-close"></div>
      Send an E-mail to <?php echo $xtable->email->text; ?>
    </div>

    <form method="post" action="ajax.php">
      <div id="dialog-panel">
        <div style="padding: 8px;">

          <div class="field">
            <label class="short">Send To:</label>
            <span class="text-container">The <?php echo $matching->message; ?></span>
          </div>

          <div class="field">
            <label class="short">Subject:</label>
            <span class="field-container"><input type="text" size="60" name="subject" value="" /></span>
          </div>

          <div class="field">
            <label class="short">Message:</label>
            <span class="field-container"><textarea name="message" id="message" class="tinymce"></textarea></span>
          </div>

        </div>
      </div>

      <div id="dialog-buttons">
        <img src="images/activity-16x16.gif" height="16" width="16" border="0" title="Working..." />
        <input type="submit" id="button-save" value="Send E-mail" />
        <input type="button" id="dialog-button-cancel" value="Cancel" style="margin-left: 10px;" />
      </div>

      <input type="hidden" name="search" value="<?php echo Request::GetSafe('search'); ?>" />
      <input type="hidden" name="r" value="tbxGenericEmail(<?php echo $type; ?>)" />
    </form>