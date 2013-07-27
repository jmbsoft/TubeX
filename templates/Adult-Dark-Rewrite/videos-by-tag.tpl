{template file="global-header.tpl"}

    <div class="main-content page-content">
      <span class="section-left">

        {videos
        var=$videos
        amount=50
        tags=$g_tag
        paginate=true
        pagination=$pagination
        sort=date_added DESC}

        <div class="section-content section-videos">
          <div class="header">
            <span class="header-left"></span>
            <span class="header-right"></span>
            <span class="header-text">{"_Text:Videos"} {$pagination.start} - {$pagination.end} {"_Text:of"} {$pagination.total} {"_Text:Tagged With"}: {$g_tag}</span>
          </div>

          {if empty($videos)}
          <div class="message-warning">
            {"_Text:No videos with that tag"}
          </div>
          {else}

          {foreach var=$video from=$videos}
          <span class="video">
            <div class="video-container">
              <a href="{$g_config.base_uri}/video/{$video.video_id}/{$video.title|t_urlify(7)}">
                <img src="{if $video.thumbnail}{$video.thumbnail}{else}{$g_config.no_preview}{/if}" class="video-thumb" title="{$video.title}" thumbs="{$video.num_thumbnails}" />
              </a>
              <div>
                <img src="{$g_config.template_uri}/images/{$video.total_avg_rating|t_nearesthalf}-stars.png" class="stars" />
                <span class="fs80">{$video.duration|t_duration}</span>
              </div>
              <div>
                <a href="{$g_config.base_uri}/video/{$video.video_id}/{$video.title|t_urlify(7)}" title="{$video.title}">{$video.title|t_chop(35,'...')}</a>
              </div>
              <span class="fs80">{$video.total_num_views|t_tostring} {if $video.total_num_views == 1}{"_Text:view"}{else}{"_Text:views"}{/if}</span>
            </div>
          </span>
          {/foreach}

          {/if}

        </div>

        <div class="pagination">
          {if $pagination.total}
          {template file="global-pagination.tpl" uri="tag/$g_tag"}
          {/if}
        </div>
      </span>

      <span class="section-right">
        {template file="global-tags.tpl"}
        {template file="global-categories.tpl"}
      </span>
    </div>

{template file="global-footer.tpl"}