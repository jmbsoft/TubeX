    <object id="wmplayerax" classid="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6" type="video/x-ms-wmv" width="640" height="480">
      <param name="AutoStart" value="false">
      <param name="ShowTracker" value="true">
      <param name="ShowControls" value="true">
      <param name="ShowGotoBar" value="false">
      <param name="ShowDisplay" value="false">
      <param name="ShowStatusBar" value="false">
      <param name="AutoSize" value="false">
      <param name="StretchToFit" value="true">
      <param name="URL" value="{$g_clip.clip}">
      <object id="wmplayer" name="wmplayer" data="" type="video/x-ms-wmv" width="640" height="480">
        <param name="AutoStart" value="false">
        <param name="ShowTracker" value="true">
        <param name="ShowControls" value="true">
        <param name="ShowGotoBar" value="false">
        <param name="ShowDisplay" value="false">
        <param name="ShowStatusBar" value="false">
        <param name="AutoSize" value="false">
        <param name="StretchToFit" value="true">
        <param name="URL" value="{$g_clip.clip}">
      </object>
    </object>

<script language="JavaScript">
var player = document.getElementById($.browser.msie ? 'wmplayerax' : 'wmplayer');
function loadClip(href)
{
    player.controls.stop();
    player.URL = href;
    player.controls.play();
}
</script>