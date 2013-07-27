    <div id="dialog-header" class="ui-widget-header ui-corner-all">
      <div id="dialog-close"></div>
      Video Flagged Reasons
    </div>

    <form method="post" action="ajax.php">
      <div id="dialog-panel">
        <div style="padding: 8px;">

          <?php
          $DB = GetDB();
          $video = $DB->Row('SELECT * FROM `tbx_video` WHERE `video_id`=?', array(Request::Get('id')));
          $result = $DB->Query('SELECT COUNT(*) AS `times`,`description` FROM `tbx_video_flagged` JOIN `tbx_reason` USING (`reason_id`) WHERE `video_id`=? GROUP BY `tbx_reason`.`reason_id` ORDER BY `times` DESC', array(Request::Get('id')));
          ?>
          <div class="message-warning text-center" style="<?php echo $DB->NumRows($result) > 0 ? 'display: none;' : ''; ?>">No flag reasons have been logged for this video since the last clearing</div>
          <?php
          if( $DB->NumRows($result) > 0 )
          {
          ?>
          <div class="reason-container reason-container-header">
            <span>Times</span>
            <span>Reason</span>
          </div>
          <?php
              while( $reason = $DB->NextRow($result) )
              {
          ?>
          <div class="reason-container">
            <span><?php echo NumberFormatInteger($reason['times']); ?></span>
            <span><?php echo $reason['description']; ?></span>
          </div>
          <?php
              }
          }
          ?>

        </div>
      </div>

      <div id="dialog-buttons">
        <img src="images/activity-16x16.gif" height="16" width="16" border="0" title="Working..." />
        <input type="submit" value="Clear Reasons" r="tbxVideoReasonFlaggedClear" />
        <input type="button" value="Delete Video" title="Delete" style="margin-left: 10px;" />
        <?php if( $video['status'] == STATUS_ACTIVE ): ?>
        <input type="button" value="Disable Video" title="Disable" style="margin-left: 10px;" />
        <?php endif; ?>
        <input type="button" id="dialog-button-cancel" value="Close" style="margin-left: 10px;" />
      </div>

      <input type="hidden" name="video_id" value="<?php echo Request::Get('id'); ?>" />
      <input type="hidden" name="r" value="" />

    </form>

<script language="JavaScript">
$('#dialog-content input[type="button"]')
.click(function()
       {
           var video_id = $('#dialog-content input[name="video_id"]').val();

           switch($(this).val())
           {
               case 'Delete Video':
                   $('#search-results-tbody tr#' + video_id + ' img[title="Delete"]').click();
                   break;

               case 'Disable Video':
                   $('#search-results-tbody tr#' + video_id + ' img[title="Disable"]').click();
                   break;
           }
       });

$('#dialog-content input[type="submit"]')
.mousedown(function()
           {
               $('#dialog-content input[name="r"]').val($(this).attr('r'));
           });

$('#dialog-content form').ajaxForm({success: function(data)
                                             {
                                                 dialogButtonEnable();

                                                 switch($('#dialog-content input[name="r"]').val())
                                                 {
                                                     case 'tbxVideoReasonFlaggedClear':
                                                        $('#dialog-content .message-warning').show();
                                                        $('#dialog-content .reason-container').hide();
                                                        break;
                                                 }
                                             },
                                    beforeSubmit: function(data, $form, options)
                                                  {
                                                      dialogButtonDisable();
                                                  }});
</script>