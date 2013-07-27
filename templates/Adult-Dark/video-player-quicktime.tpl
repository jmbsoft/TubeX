    <object id="qtplayerax" width="640" height="480" codebase="http://www.apple.com/qtactivex/qtplugin.cab#version=7,3,0,0" classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B">
      <param name="src" value="{$g_config.base_uri}/loader.php?u={$g_clip.clip|urlencode}&un={nocache}{$g_username|urlencode}{/nocache}&id={$g_clip.clip_id|urlencode}"/>
      <param name="autostart" value="true"/>
      <param name="saveembedtags" value="true"/>
      <param name="postdomevents" value="true"/>
      <param name="EnableJavaScript" value="true"/>
      <param name="scale" value="aspect"/>

      <object id="qtplayer" name="qtplayer" width="640" height="480" type="video/quicktime" data="{$g_config.base_uri}/loader.php?u={$g_clip.clip|urlencode}&un={nocache}{$g_username|urlencode}{/nocache}&id={$g_clip.clip_id|urlencode}">
        <param name="autostart" value="true"/>
        <param name="saveembedtags" value="true"/>
        <param name="postdomevents" value="true"/>
        <param name="EnableJavaScript" value="true"/>
        <param name="scale" value="aspect"/>
      </object>
    </object>

<script language="JavaScript">
var player = document.getElementById($.browser.msie ? 'qtplayerax' : 'qtplayer');
function loadClip(href)
{
    player.Stop();
    player.SetURL('{$g_config.base_url}/loader.php?un={nocache}{$g_username|urlencode}{/nocache}&id={$g_clip.clip_id|urlencode}&u=' + escape(href));
}
</script>