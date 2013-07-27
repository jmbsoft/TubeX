<div id="player" style="width: 640px; height: 480px;"></div>

<div id="player-content">
  <div id="player-content-banner">
    {* ADD A BANNER HERE: {banner sponsor=$video.sponsor_id zone="Player"}*}
  </div>
  <div id="player-content-icons">
    <img src="{$g_config.template_uri}/images/player-resume.png" id="icon-resume" alt="{"_Text:Resume"}" title="{"_Text:Resume"}" class="click" />
    <img src="{$g_config.template_uri}/images/player-replay.png" id="icon-replay" alt="{"_Text:Replay"}" title="{"_Text:Replay"}" class="click" />
    <img src="{$g_config.template_uri}/images/player-email.png" id="icon-email" alt="{"_Text:Email"}" title="{"_Text:Email"}" class="click" />
    {if $video.allow_embedding}
    <img src="{$g_config.template_uri}/images/player-embed.png" id="icon-embed" alt="{"_Text:Embed"}" title="{"_Text:Embed"}" class="click" />
    {/if}
    <img src="{$g_config.template_uri}/images/player-share.png" id="icon-share" alt="{"_Text:Share"}" title="{"_Text:Share"}" class="click" />
  </div>
</div>

<script type="text/javascript" src="{$g_config.base_uri}/player/flowplayer/flowplayer.js"></script>
<script type="text/javascript">
$(function()
{
    $('#icon-resume').click(fpResume);
    $('#icon-replay').click(fpReplay);
    $('#icon-email').click(fpViralEmail);
    $('#icon-embed').click(fpViralEmbed);
    $('#icon-share').click(fpViralShare);
});

var player = flowplayer(
    'player',
    {
        src: '{$g_config.base_uri}/player/flowplayer/flowplayer.swf',
        wmode: 'transparent'
    },
    {
        canvas: {
            backgroundColor: '#000000',
            backgroundGradient: [0.0,0.0]
        },
        clip: {
            url: '{$g_config.base_url|urlencode}%2Floader.php%3Fu%3D{$g_clip.clip|urlencode}%26un%3D{nocache}{$g_username|urlencode}{/nocache}%26id%3D{$g_clip.clip_id|urlencode}%26pt%3Dflv',
            autoPlay: true,
            autoBuffering: true,
            scaling: 'fit',
            onPause: fpOnPause,
            onFinish: fpOnFinish,
            onBegin: fpContentHide,
            onStart: fpContentHide,
            onResume: fpContentHide
        },
        plugins: {
            controls: {
                url: '{$g_config.base_uri}/player/flowplayer/flowplayer.controls.swf',
                autoHide: false,
                backgroundColor: '#333333',
                backgroundGradient: [0.0,0.0]
            },
            viral: {
                url: '{$g_config.base_uri}/player/flowplayer/flowplayer.viralvideos.swf',
                {if !$video.allow_embedding}
                embed: false,
                {/if}
                share: {
                    description: '{$video.title|t_singlequotes}'
                }
            }
        }
    }
);

function fpOnFinish()
{
    $('#icon-replay').show();
    $('#icon-resume').hide();
    fpContentShow();
}

function fpOnPause()
{
    $('#icon-resume').show();
    $('#icon-replay').hide();
    fpContentShow();
}

function fpResume()
{
    player
    .resume();
}

function fpReplay()
{
    player
    .play();
}

function fpViralEmail()
{
    fpContentHide();
    fpViralShow();

    player
    .getPlugin('viral')
    .email();
}

function fpViralShare()
{
    fpContentHide();
    fpViralShow();

    player
    .getPlugin('viral')
    .share();
}

function fpViralEmbed()
{
    fpContentHide();
    fpViralShow();

    player
    .getPlugin('viral')
    .embed();
}

function fpViralShow()
{
    player
    .getPlugin('viral')
    .show();
}

function fpViralHide()
{
    player
    .getPlugin('viral')
    .hide();
}

function fpContentHide()
{
    $('#player-content')
    .hide();
}

function fpContentShow()
{
    fpViralHide();

    var position = $('#player').position();

    $('#player-content')
    .css({top: position.top + 'px', left: position.left + 'px'})
    .show();
}

function loadClip(href)
{
    player
    .stop()
    .play('{$g_config.base_url}/loader.php?pt=flv&un={nocache}{$g_username|urlencode}{/nocache}&id={$g_clip.clip_id|urlencode}&u=' + escape(href))
}
</script>
