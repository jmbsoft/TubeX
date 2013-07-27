<div style="font-size: 130%">
{"_Text:Video uploaded successfully"}

{if $g_video.status == 'Queued'}
{"_Text:Video will be converted"}
{elseif $g_video.status == 'Pending'}
{"_Text:Video will be reviewed"}
{else}
  {if $g_video.is_private}
    {"_Text:Video is active and private"}
  {else}
    {"_Text:Video is now active"}
  {/if}
{/if}

{"_Text:Thanks for your upload"}

<br />
<br />

<div style="text-align: center;">
<a href="{$g_config.base_uri}/upload.php">Upload Another Video</a>
</div>
</div>

<script language="JavaScript" type="text/javascript">
$('#form').remove();
</script>