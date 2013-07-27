    <div id="dialog-header" class="ui-widget-header ui-corner-all">
      <div id="dialog-close"></div>
      E-mail Template Search and Replace
    </div>

    <form method="post" action="ajax.php">
      <div id="dialog-panel">
        <div style="padding: 8px;">

          <div class="field">
            <label class="short">Templates:</label>
            <span class="field-container">
              <?php
              $templates = Dir::ReadFiles(TEMPLATES_DIR, '~^email~');
              asort($templates);
              ?>
              <select name="templates[]" id="templates" multiple="multiple" size="10">
                <?php echo Form_Field::OptionsSimple($templates); ?>
              </select>
            </span>
          </div>

          <div class="field">
            <label class="short">Search For:</label>
            <span class="field-container"><textarea name="search" id="search" rows="5" cols="110"></textarea></span>
          </div>

          <div class="field">
            <label class="short">Replace With:</label>
            <span class="field-container"><textarea name="replace" id="replace" rows="5" cols="110"></textarea></span>
          </div>

        </div>
      </div>

      <div id="dialog-buttons">
        <img src="images/activity-16x16.gif" height="16" width="16" border="0" title="Working..." />
        <input type="submit" id="button-save" value="Apply Changes" />
        <input type="button" id="dialog-button-cancel" value="Cancel" style="margin-left: 10px;" />
      </div>

      <input type="hidden" name="r" value="tbxEmailTemplateSearchReplace" />
    </form>

    <script language="JavaScript" type="text/javascript">
    $('#dialog-content form')
    .ajaxForm({success: function(data)
                        {
                            dialogButtonEnable();
                            dialogSuccess(data, $('#dialog-content form'), true);
                        },
               beforeSubmit: function()
                             {
                                 dialogButtonDisable();
                             }});
    </script>