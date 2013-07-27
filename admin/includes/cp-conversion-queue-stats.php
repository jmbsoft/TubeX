        <div id="dialog-help">
          <a href="docs/cp-queues.html" target="_blank"><img src="images/help-22x22.png" alt="Help" title="Help" border="0" /></a>
        </div>

          <?php $stats = ConversionQueue::LoadStats(true); ?>

          <?php echo ResizeableColumn('Queued Videos', $stats[ConversionQueue::STAT_QUEUED_ITEMS], true); ?>
          <?php echo ResizeableColumn('Estimated Time', $stats[ConversionQueue::STAT_ESTIMATED_TIME]); ?>
          <?php echo ResizeableColumn('Queue Processor', ConversionQueue::IsRunning() ? 'Running' : 'Stopped'); ?>
          <?php echo ResizeableColumn('Last Started', $stats[ConversionQueue::STAT_LAST_STARTED]); ?>
          <?php echo ResizeableColumn('Videos Processed', $stats[ConversionQueue::STAT_PROCESSED_ITEMS], true); ?>
          <?php echo ResizeableColumn('Failed', $stats[ConversionQueue::STAT_FAILED_ITEMS], true); ?>
          <?php echo ResizeableColumn('Average Time', $stats[ConversionQueue::STAT_AVERAGE_TIME]); ?>
          <?php echo ResizeableColumn('Average Wait', $stats[ConversionQueue::STAT_AVERAGE_WAIT]); ?>


<script language="JavaScript" type="text/javascript">
$(function()
{
    $('#dialog-button-start').attr('disabled', '<?php echo ConversionQueue::IsStartable() ? '' : 'disabled'; ?>');
    $('#dialog-button-stop').attr('disabled', '<?php echo ConversionQueue::IsRunning() ? '' : 'disabled'; ?>');
    $('#dialog-button-clear').attr('disabled', '<?php echo !ConversionQueue::IsRunning() ? '' : 'disabled'; ?>');
});
</script>