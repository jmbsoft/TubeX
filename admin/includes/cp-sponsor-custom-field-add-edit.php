    <div id="dialog-header" class="ui-widget-header ui-corner-all">
      <div id="dialog-close"></div>
      <?php echo (isset($editing) ? 'Update a Custom Sponsor Field' : 'Add a Custom Sponsor Field'); ?>
    </div>

    <form method="post" action="ajax.php">
      <div id="dialog-panel">
        <div style="padding: 8px;">

          <div id="dialog-help">
            <a href="docs/cp-sponsor-custom-field.html" target="_blank"><img src="images/help-22x22.png" alt="Help" title="Help" border="0" /></a>
          </div>

          <div class="field">
            <label>Name:</label>
            <?php if( isset($editing) ): ?>
            <span class="text-container">
              <?php echo Request::Get('name'); ?>
              <input type="hidden" name="name" value="<?php echo Request::Get('name'); ?>" />
            </span>
            <?php else: ?>
            <span class="field-container">
              <input type="text" size="30" name="name" value="<?php echo Request::Get('name'); ?>" />
            </span>
            <?php endif; ?>
          </div>

          <div class="field">
            <label>Field Type:</label>
            <span class="field-container">
              <select name="type" id="type">
                <?php
                $opts = array('Text','Textarea','Checkbox','Select');
                echo Form_Field::OptionsSimple($opts, Request::Get('type'));
                ?>
              </select>
            </span>
          </div>

          <div class="field" id="field-options" style="display: none;">
            <label>Options:</label>
            <span class="field-container"><input type="text" size="60" name="options" value="<?php echo Request::Get('options'); ?>" /></span>
          </div>

          <div class="field">
            <label>Label:</label>
            <span class="field-container"><input type="text" size="40" name="label" value="<?php echo Request::Get('label'); ?>" /></span>
          </div>

          <div class="field">
            <label>Tag Attributes:</label>
            <span class="field-container"><input type="text" size="60" name="tag_attributes" value="<?php echo Request::Get('tag_attributes'); ?>" /></span>
          </div>

          <div class="field">
            <label>Validators:</label>
            <span class="field-container" id="validators">
            </span>
          </div>


        </div>
      </div>

      <div id="dialog-buttons">
        <img src="images/activity-16x16.gif" height="16" width="16" border="0" title="Working..." />
        <input type="submit" id="button-save" value="<?php echo (isset($editing) ? 'Save Changes' : 'Add Custom Sponsor Field') ?>" />
        <input type="button" id="dialog-button-cancel" value="Cancel" style="margin-left: 10px;" />
      </div>

      <input type="hidden" name="r" value="<?php echo (isset($editing) ? 'tbxGenericEdit' : 'tbxGenericAdd') ?>(sponsor-custom-field)" />
      <input type="hidden" name="field_id" value="<?php echo Request::Get('field_id'); ?>" />
    </form>

    <div id="validator-master" style="margin: 3px 0; display: none;">
      <select name="validator[type][]">
      <?php
      $reflect = new ReflectionClass('Validator_Type');
      echo Form_Field::Options(array_flip($reflect->getConstants()));
      ?>
      </select>
      <input type="text" name="validator[message][]" size="40" defaultvalue="Error message" class="defaultvalue" value="Error message" />
      <input type="text" name="validator[extras][]" size="10" defaultvalue="Extras" class="defaultvalue" value="Extras" />
      <img src="images/add-16x16.png" class="clickable vertical-middle" style="padding-left: 6px;" title="Add" />
      <img src="images/remove-16x16.png" class="clickable vertical-middle" style="padding-left: 6px;" title="Remove" />
    </div>

    <?php IncludeJavascript(BASE_DIR . '/admin/js/cp-custom-field-add.js'); ?>

    <?php
    if( isset($editing) ):
    ?>
    <script language="JavaScript" type="text/javascript">
    var validators = <?php echo json_encode(unserialize($original['validators'])); ?>;
    </script>
    <?php
    IncludeJavascript(BASE_DIR . '/admin/js/cp-custom-field-edit.js');
    endif;
    ?>