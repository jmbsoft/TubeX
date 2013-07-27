    <div id="dialog-header" class="ui-widget-header ui-corner-all">
      <div id="dialog-close"></div>
      Bulk Edit <?php echo $xnaming->textUpperPlural; ?>
    </div>


    <!-- START MASTER DIV FOR UPDATES -->
    <div class="field update-fields" id="update-master">
      <label class="short">Field:</label>
      <span class="field-container">
        <select name="field[]" class="field">
          <?php echo Form_Field::Options($fields, null, 'column', 'label'); ?>
        </select>

        <label class="inline">Action:</label>
        <select name="action[]" id="action">
          <option value="<?php echo BulkEdit::ACTION_SET ?>"><?php echo BulkEdit::ACTION_SET ?></option>
          <option value="<?php echo BulkEdit::ACTION_APPEND ?>"><?php echo BulkEdit::ACTION_APPEND ?></option>
          <option value="<?php echo BulkEdit::ACTION_PREPEND ?>"><?php echo BulkEdit::ACTION_PREPEND ?></option>
          <option value="<?php echo BulkEdit::ACTION_ADD ?>"><?php echo BulkEdit::ACTION_ADD ?></option>
          <option value="<?php echo BulkEdit::ACTION_SUBTRACT ?>"><?php echo BulkEdit::ACTION_SUBTRACT ?></option>
          <option value="<?php echo BulkEdit::ACTION_INCREMENT ?>"><?php echo BulkEdit::ACTION_INCREMENT ?></option>
          <option value="<?php echo BulkEdit::ACTION_DECREMENT ?>"><?php echo BulkEdit::ACTION_DECREMENT ?></option>
          <option value="<?php echo BulkEdit::ACTION_REPLACE ?>"><?php echo BulkEdit::ACTION_REPLACE ?></option>
          <option value="<?php echo BulkEdit::ACTION_TRIM ?>"><?php echo BulkEdit::ACTION_TRIM ?></option>
          <option value="<?php echo BulkEdit::ACTION_CLEAR ?>"><?php echo BulkEdit::ACTION_CLEAR ?></option>
          <option value="<?php echo BulkEdit::ACTION_TRUNCATE ?>"><?php echo BulkEdit::ACTION_TRUNCATE ?></option>
          <option value="<?php echo BulkEdit::ACTION_UPPERCASE_ALL ?>"><?php echo BulkEdit::ACTION_UPPERCASE_ALL ?></option>
          <option value="<?php echo BulkEdit::ACTION_UPPERCASE_FIRST ?>"><?php echo BulkEdit::ACTION_UPPERCASE_FIRST ?></option>
          <option value="<?php echo BulkEdit::ACTION_LOWERCASE_ALL ?>"><?php echo BulkEdit::ACTION_LOWERCASE_ALL ?></option>
          <option value="<?php echo BulkEdit::ACTION_RAW_SQL ?>"><?php echo BulkEdit::ACTION_RAW_SQL ?></option>
        </select>

        <label class="inline">Value:</label>
        <input type="text" name="value[]" class="value" value="" size="30" />


        <span style="display: inline-block; vertical-align: top; padding-top: 0.2em; margin-left: 10px;">
          <img src="images/add-16x16.png" border="0" class="clickable update-add" title="Add Another Change" />
          <img src="images/remove-16x16.png" border="0" class="clickable update-remove" title="Remove This Change" style="margin-left: 8px;" />
        </span>
      </span>
    </div>
    <!-- END MASTER DIV FOR UPDATES -->


    <form method="post" action="ajax.php">
      <div id="dialog-panel">
        <div style="padding: 8px;">

          <div class="field">
            <label class="short">Apply To:</label>
            <span class="text-container">The <?php echo $matching->message; ?></span>
          </div>

        </div>
      </div>

      <div id="dialog-buttons">
        <img src="images/activity-16x16.gif" height="16" width="16" border="0" title="Working..." />
        <input type="submit" id="button-save" value="Save Changes" />
        <input type="button" id="dialog-button-cancel" value="Cancel" style="margin-left: 10px;" />
      </div>

      <input type="hidden" name="search" value="<?php echo Request::GetSafe('search'); ?>" />
      <input type="hidden" name="r" value="tbxGenericBulkEdit(<?php echo $type; ?>)" />
    </form>
