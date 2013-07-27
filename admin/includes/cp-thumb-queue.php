    <div id="dialog-header" class="ui-widget-header ui-corner-all">
      <div id="dialog-close"></div>
      Thumbnail Generation Queue
    </div>


    <div id="dialog-panel">
      <div style="padding: 8px;">

        <?php include_once('cp-thumb-queue-stats.php'); ?>

      </div>
    </div>

    <div id="cq-updating" style="float: left; display: none; margin-top: 4px;">
      <img src="images/activity-22x22.gif"  style="vertical-align: middle;" /> <span style="vertical-align: middle;">Updating...</span>
    </div>

    <div id="dialog-buttons">
      <input type="button" id="dialog-button-start" value="Start" style="margin-left: 10px;" disabled="disabled" />
      <input type="button" id="dialog-button-stop" value="Stop" style="margin-left: 10px;" disabled="disabled" />
      <input type="button" id="dialog-button-clear" value="Clear Queue" style="margin-left: 10px;" disabled="disabled" />
      <input type="button" id="dialog-button-cancel" value="Close" style="margin-left: 10px;" />
    </div>

<script language="JavaScript" type="text/javascript">
$(function()
{
    var cqinterval = setInterval(function()
                                {
                                    $('#cq-updating').show();
                                    $('#dialog-panel > div').load('ajax.php', {r: 'tbxThumbQueueStats'}, function() { $('#cq-updating').hide(); });
                                },
                                5000);

    $('#dialog')
    .bind('closing',
          function()
          {
              clearInterval(cqinterval);
              $(this).unbind('closing');
          });

    $('#dialog-button-start')
    .click(function()
           {
               $('#dialog-button-start, #dialog-button-clear').attr('disabled', 'disabled');
               $.ajax({data: 'r=tbxThumbQueueStart'});
           });

    $('#dialog-button-stop')
    .click(function()
           {
               $.ajax({data: 'r=tbxThumbQueueStop'});
           });

    $('#dialog-button-clear')
    .click(function()
           {
               $.ajax({data: 'r=tbxThumbQueueClear'});
           });
});
</script>
