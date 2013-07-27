        <div id="dialog-help">
          <a href="docs/cp-queues.html" target="_blank"><img src="images/help-22x22.png" alt="Help" title="Help" border="0" /></a>
        </div>

          <?php $stats = ThumbQueue::LoadStats(true); ?>

          <?php echo ResizeableColumn('Queued Videos', $stats[ThumbQueue::STAT_QUEUED_ITEMS], true); ?>
          <?php echo ResizeableColumn('Estimated Time', $stats[ThumbQueue::STAT_ESTIMATED_TIME]); ?>
          <?php echo ResizeableColumn('Queue Processor', ThumbQueue::IsRunning() ? 'Running' : 'Stopped'); ?>
          <?php echo ResizeableColumn('Last Started', $stats[ThumbQueue::STAT_LAST_STARTED]); ?>
          <?php echo ResizeableColumn('Videos Processed', $stats[ThumbQueue::STAT_PROCESSED_ITEMS], true); ?>
          <?php echo ResizeableColumn('Failed', $stats[ThumbQueue::STAT_FAILED_ITEMS], true); ?>
          <?php echo ResizeableColumn('Average Time', $stats[ThumbQueue::STAT_AVERAGE_TIME]); ?>
          <?php echo ResizeableColumn('Average Wait', $stats[ThumbQueue::STAT_AVERAGE_WAIT]); ?>

<script language="JavaScript" type="text/javascript">
$(function()
{
    $('#dialog-button-start').attr('disabled', '<?php echo ThumbQueue::IsStartable() ? '' : 'disabled'; ?>');
    $('#dialog-button-stop').attr('disabled', '<?php echo ThumbQueue::IsRunning() ? '' : 'disabled'; ?>');
    $('#dialog-button-clear').attr('disabled', '<?php echo !ThumbQueue::IsRunning() ? '' : 'disabled'; ?>');
});
</script>