{nocache}{php} echo '<?xml version="1.0" ?>'; {/php}{/nocache}

<rss version="2.0">
  <channel>
    <title>{$g_config.site_name}</title>
    <description>{$g_config.meta_description}</description>
    <link>{$g_config.base_url}</link>

{videos
var=$videos
amount=25
paginate=true
pagination=$pagination
sort=date_added DESC}

{foreach var=$video from=$videos}
    <item>
      <title>{$video.title}</title>
      <link>{$g_config.base_url}/index.php?r=video&amp;id={$video.video_id}</link>
      <description>
      <![CDATA[
      <a href="{$g_config.base_url}/index.php?r=video&amp;id={$video.video_id}" target="_blank">
      <img src="{if $video.thumbnail}{$video.thumbnail}{else}{$g_config.no_preview}{/if}" class="video-thumb" title="{$video.title}" border="0" />
      </a>
      <br />
      {$video.description}<br />
      <b>{"_Text:Duration"}:</b> {$video.duration|t_duration}<br />
      <img src="{$g_config.template_uri}/images/{$video.total_avg_rating|t_nearesthalf}-stars.png" class="stars" />
      ]]>
      </description>
      <pubDate>{$video.date_added|t_datetime('D, d M Y H:i:s -0600')}</pubDate>
    </item>
{/foreach}

  </channel>
</rss>