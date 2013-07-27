<div id="wmvsilverplayer"></div>
<script type='text/javascript' src="{$g_config.base_uri}/player/wmvplayer/silverlight.js"></script>
<script type='text/javascript' src="{$g_config.base_uri}/player/wmvplayer/wmvplayer.js"></script>
<script type="text/javascript">
var element = document.getElementById('wmvsilverplayer');
var xaml = '{$g_config.base_uri}/player/wmvplayer/wmvplayer.xaml';
new jeroenwijering.Player(
    element,
    xaml,
    {
        file: '{$g_config.base_uri}/loader.php?u={$g_clip.clip|urlencode}&un={nocache}{$g_username|urlencode}{/nocache}&id={$g_clip.clip_id|urlencode}&pt=wmv',
        width: '640',
        height: '480',
        autostart: 'true'
    }
);

function loadClip(href)
{
    $('#wmvsilverplayer')
    .empty();

    new jeroenwijering.Player(
        element,
        xaml,
        {
            file: '{$g_config.base_uri}/loader.php?un={nocache}{$g_username|urlencode}{/nocache}&id={$g_clip.clip_id|urlencode}&pt=wmv&u=' + escape(href),
            width: '640',
            height: '480',
            autostart: 'true'
        }
    );
}
</script>