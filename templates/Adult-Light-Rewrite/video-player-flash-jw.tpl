<div id="player" style="width: 640px; height: 480px;"></div>

<div id="player-content">
  <div id="player-content-banner">
    {* ADD A BANNER HERE: {banner sponsor=$video.sponsor_id zone="Player"}*}
  </div>
  <div id="player-content-icons">
    <img src="{$g_config.template_uri}/images/player-resume.png" id="icon-resume" alt="{"_Text:Resume"}" title="{"_Text:Resume"}" class="click" />
    <img src="{$g_config.template_uri}/images/player-replay.png" id="icon-replay" alt="{"_Text:Replay"}" title="{"_Text:Replay"}" class="click" />
  </div>
</div>

<script type="text/javascript" src="{$g_config.base_uri}/player/jwplayer/jwplayer.js"></script>
<script type="text/javascript">
$(function()
{
    $('#icon-resume').click(jwResume);
    $('#icon-replay').click(jwReplay);
});


jwplayer('player').setup({
    flashplayer: '{$g_config.base_uri}/player/jwplayer/player.swf',
    id: 'jwplayer',
    width: '640',
    height: '480',
    skin: '{$g_config.base_uri}/player/jwplayer/glow.zip',
    file: '{$g_config.base_url}/loader.php?u={$g_clip.clip|urlencode}&un={nocache}{$g_username|urlencode}{/nocache}&id={$g_clip.clip_id|urlencode}&pt=flv',
    provider: 'video',
    events:
    {
        onPause: jwOnPause,
        onPlay: jwContentHide,
        onComplete: jwOnFinish
    }
});


function jwResume()
{
    jwplayer()
    .play(true);
}

function jwReplay()
{
    jwplayer()
    .playlistNext();
}

function jwOnFinish()
{
    $('#icon-replay').show();
    $('#icon-resume').hide();
    jwContentShow();
}

function jwOnPause()
{
    $('#icon-resume').show();
    $('#icon-replay').hide();
    jwContentShow();
}

function jwContentHide()
{
    $('#player-content')
    .hide();
}

function jwContentShow()
{
    var position = $('#player').position();

    $('#player-content')
    .css({top: position.top + 'px', left: position.left + 'px'})
    .show();
}

function loadClip(href)
{
    var playlist = jwplayer().getPlaylist();

    playlist[0].file = '{$g_config.base_url}/loader.php?u=' + escape(href) + '&un={nocache}{$g_username|urlencode}{/nocache}&id={$g_clip.clip_id|urlencode}&pt=flv';

    jwplayer().load(playlist).play(true);
}
</script>