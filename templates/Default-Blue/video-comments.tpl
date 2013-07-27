{template file="global-header.tpl"}

  {video var=$video videoid=$g_video_id}
  {comments var=$comments videoid=$g_video_id amount=100 page=$g_page_number pagination=$pagination sort="`date_commented` DESC"}

    <div class="main-content" style="margin-top: 30px;">

      <div class="section-header">{"_Text:Comments"} {$pagination.start} - {$pagination.end} {"_Text:of"} {$pagination.total}</div>
      <div class="section-content">

        <div class="video-brief">
          <span class="video-brief-thumb">
            <a href="{$g_config.base_uri}/index.php?r=video&amp;id={$video.video_id}">
              <img src="{if $video.thumbnail}{$video.thumbnail}{else}{$g_config.no_preview}{/if}" class="video-thumb" title="{$video.title}" thumbs="{$video.num_thumbnails}" />
            </a>
          </span>
          <span class="video-brief-details">
            <a href="{$g_config.base_uri}/index.php?r=video&amp;id={$video.video_id}" class="large">{$video.title}</a><br />
            {$video.description|t_chop(80,'...')}
            <div class="video-brief-facets smallest">
              <span><img src="{$g_config.template_uri}/images/{$video.total_avg_rating|t_nearesthalf}-stars.png" /></span>
              <span>{$video.total_num_views|t_tostring} {"_Text:views"}</span>
              <span><a href="{$g_config.base_uri}/index.php?r=profile&amp;u={$video.username|urlencode}" class="normal">{$video.username}</a></span>
            </div>
          </span>
        </div>

        <div style="margin: 4px 0px; border-bottom: 2px solid #afafaf;"></div>

        {foreach var=$c from=$comments}
        <div style="margin: 8px; padding-bottom: 4px; border-bottom: 1px solid #efefef;">
          <div style="margin-bottom: 4px;">
            <a href="{$g_config.base_uri}/index.php?r=profile&amp;u={$c.username|urlencode}" target="_parent">{$c.username}</a> <span style="color: #afafaf;">({$c.date_commented|t_age})</span>
          </div>
          {$c.comment|htmlspecialchars|nl2br}
        </div>
        {/foreach}

        <div class="pagination">
          {if $pagination.total}
          {template file="global-pagination.tpl" uri="index.php?r=video-comments&d=&id=$g_video_id&p="}
          {/if}
        </div>

      </div>
    </div>

{template file="global-footer.tpl"}