    <div id="dialog-header" class="ui-widget-header ui-corner-all">
      <div id="dialog-close"></div>
      <?php echo (isset($editing) ? 'Update a Category' : 'Add a Category'); ?>
    </div>

    <form method="post" action="ajax.php">
      <div id="dialog-panel">
        <div style="padding: 8px;">

          <div class="fieldset">
            <div class="legend">Default Fields</div>

            <div id="dialog-help">
              <a href="docs/cp-category.html" target="_blank"><img src="images/help-22x22.png" alt="Help" title="Help" border="0" /></a>
            </div>

            <div class="field">
              <label>Name:</label>
              <span class="field-container">
                <input type="text" size="60" name="name" value="<?php echo Request::Get('name'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>URL Name:</label>
              <span class="field-container"><input type="text" size="60" name="url_name" value="<?php echo Request::Get('url_name'); ?>" /></span>
            </div>

            <div class="field">
              <label>Description:</label>
              <span class="field-container">
                <textarea name="description" rows="5" cols="80"><?php echo Request::Get('description'); ?></textarea>
              </span>
            </div>

            <div class="field">
              <label>Meta Description:</label>
              <span class="field-container"><input type="text" size="90" name="meta_description" value="<?php echo Request::Get('meta_description'); ?>" /></span>
            </div>

            <div class="field">
              <label>Meta Keywords:</label>
              <span class="field-container"><input type="text" size="90" name="meta_keywords" value="<?php echo Request::Get('meta_keywords'); ?>" /></span>
            </div>

            <div class="field">
              <label>Auto Category Term:</label>
              <span class="field-container"><input type="text" size="90" name="auto_category_term" value="<?php echo Request::Get('auto_category_term'); ?>" /></span>
            </div>

            <?php
            if( isset($editing) && Request::Get('image_id') ):
                $image = $DB->Row('SELECT * FROM `tbx_upload` WHERE `upload_id`=?', array(Request::Get('image_id')));
            ?>
            <div class="field">
              <label>Existing Image:</label>
              <span class="field-container">
                <div class="checkbox" style="display: block; margin-bottom: 5px;">
                  <input type="hidden" name="remove_image" value="0" />
                  Remove Image
                </div>
                <img src="<?php echo String::HtmlSpecialChars($image['uri']); ?>" class="avatar" />
              </span>
            </div>
            <?php endif; ?>

            <div class="field">
              <label>Upload Image:</label>
              <span class="field-container">
                <input type="file" size="50" name="image_file" /><br />
                <span class="small">JPG, GIF, or PNG image</span>
              </span>
            </div>

          </div>

          <div class="fieldset">
            <div class="legend">Custom Fields</div>

            <?php echo Form_Field::GenerateFromCustom('category'); ?>

          </div>

        </div>
      </div>

      <div id="dialog-buttons">
        <img src="images/activity-16x16.gif" height="16" width="16" border="0" title="Working..." />
        <input type="submit" id="button-save" value="<?php echo (isset($editing) ? 'Save Changes' : 'Add Category') ?>" />
        <input type="button" id="dialog-button-cancel" value="Cancel" style="margin-left: 10px;" />
      </div>

      <input type="hidden" name="r" value="<?php echo (isset($editing) ? 'tbxGenericEdit' : 'tbxGenericAdd') ?>(category)" />
      <input type="hidden" name="category_id" value="<?php echo Request::Get('category_id'); ?>" />
    </form>

<script language="JavaScript" type="text/javascript">
$(function()
{
    $('input[name="name"]')
    .change(function()
            {
                var suggested = $(this).val().replace(/[&]/g, 'and').replace(/[^a-z0-9]/gi, '-').replace(/-+/gi, '-').toLowerCase();
                $('input[name="url_name"]').attr('acomplete', '#' + suggested).unautocomplete().autocomplete();
            });
});
</script>