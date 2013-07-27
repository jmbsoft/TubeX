{capture var=$title}{$g_sort|t_translate('Text')} {"_Text:Videos"} - {$g_timeframe|t_translate('Text')}{/capture}
{template file="global-header.tpl" title=$title}

    <div class="main-content page-content">
      {if empty($g_videos_sorter)}
      <div class="message-error" style="margin-bottom: 20px;">
        {"_Text:Invalid video sorter"}
      </div>
      {else}

      <div class="section-content-no-header text-center fs130">
        <a {if !$g_loc_videos_popular}href="{$g_config.base_uri}/index.php?r=videos&amp;s=popular"{/if}>{"_Text:Popular"}</a> &mdash;
        <a {if !$g_loc_videos_top_rated}href="{$g_config.base_uri}/index.php?r=videos&amp;s=top-rated"{/if}>{"_Text:Top Rated"}</a> &mdash;
        <a {if !$g_loc_videos_most_rated}href="{$g_config.base_uri}/index.php?r=videos&amp;s=most-rated"{/if}>{"_Text:Most Rated"}</a> &mdash;
        <a {if !$g_loc_videos_most_discussed}href="{$g_config.base_uri}/index.php?r=videos&amp;s=most-discussed"{/if}>{"_Text:Most Discussed"}</a> &mdash;
        <a {if !$g_loc_videos_top_favorited}href="{$g_config.base_uri}/index.php?r=videos&amp;s=top-favorited"{/if}>{"_Text:Top Favorited"}</a>
      </div>

      <span class="section-left">

        {videos
        var=$videos
        amount=50
        paginate=true
        pagination=$pagination
        sort=$g_videos_sorter}

        <div class="section-content section-videos">
          <div class="header">
            <span class="header-left"></span>
            <span class="header-right"></span>
            <span class="header-text">{$g_sort|t_translate('Text')} {"_Text:Videos"} &mdash; {$g_timeframe|t_translate('Text')}</span>
          </div>

          <div style="position: absolute; right: 10px; top: 6px;" class="fs80">
            <a {if !$g_loc_today}href="{$g_config.base_uri}/index.php?r=videos&amp;s={$g_sort|urlencode}&amp;t=today"{/if}>{"_Text:Today"}</a> &mdash;
            <a {if !$g_loc_week}href="{$g_config.base_uri}/index.php?r=videos&amp;s={$g_sort|urlencode}&amp;t=week"{/if}>{"_Text:This Week"}</a> &mdash;
            <a {if !$g_loc_month}href="{$g_config.base_uri}/index.php?r=videos&amp;s={$g_sort|urlencode}&amp;t=month"{/if}>{"_Text:This Month"}</a> &mdash;
            <a {if !$g_loc_all_time}href="{$g_config.base_uri}/index.php?r=videos&amp;s={$g_sort|urlencode}&amp;t=all-time"{/if}>{"_Text:All Time"}</a>
          </div>

          {foreach var=$video from=$videos}
          <span class="video">
            <div class="video-container">
              <a href="{$g_config.base_uri}/index.php?r=video&amp;id={$video.video_id}">
                <img src="{if $video.thumbnail}{$video.thumbnail}{else}{$g_config.no_preview}{/if}" class="video-thumb" title="{$video.title}" thumbs="{$video.num_thumbnails}" />
              </a>
              <div>
                <img src="{$g_config.template_uri}/images/{$video.total_avg_rating|t_nearesthalf}-stars.png" class="stars" />
                <span class="fs80">{$video.duration|t_duration}</span>
              </div>
              <div>
                <a href="{$g_config.base_uri}/index.php?r=video&amp;id={$video.video_id}" title="{$video.title}">{$video.title|t_chop(35,'...')}</a>
              </div>
              <span class="fs80">{$video.total_num_views|t_tostring} {if $video.total_num_views == 1}{"_Text:view"}{else}{"_Text:views"}{/if}</span>
            </div>
          </span>
          {/foreach}

        </div>

        <div class="pagination">
          {if $pagination.total}
          {template file="global-pagination.tpl" uri="index.php?r=videos&s=$g_sort&t=$g_timeframe&p="}
          {/if}
        </div>
      </span>

      <span class="section-right">
        {template file="global-tags.tpl"}
        {template file="global-categories.tpl"}
      </span>

      {/if}
    </div>

{template file="global-footer.tpl"}