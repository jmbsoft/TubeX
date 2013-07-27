<div class="message-error">
  {"_Text:Please Fix"}
  <ul>
  {foreach var=$error from=$g_errors}
    <li>{$error}</li>
  {/foreach}
  </ul>
</div>


<script language="JavaScript" type="text/javascript">
$('#video-file').text('');
$('#progress').hide();
$('#form').css({visibility: 'visible'});
swfu.setButtonDisabled(false);
</script>