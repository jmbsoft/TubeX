{capture var=$title}{$g_sort|t_translate('Text')} {"_Text:Videos"} - {$g_timeframe|t_translate('Text')}{/capture}
{template file="global-header.tpl" title=$title}

    <div class="main-content" style="margin-top: 30px;">
      {if empty($g_videos_sorter)}
      <div class="message-error" style="margin-bottom: 30px;">
        {"_Text:Invalid video sorter"}
      </div>
      {else}

      <span class="section-left">

        {videos
        var=$videos
        amount=20
        paginate=true
        pagination=$pagination
        sort=$g_videos_sorter}

        <div class="section-header" style="max-height: 1.3em; overflow: visible;">
          <a {if !$g_loc_videos_popular}href="{$g_config.base_uri}/index.php?r=videos&amp;s=popular"{/if} class="section-header-tab">Popular</a>
          <a {if !$g_loc_videos_top_rated}href="{$g_config.base_uri}/index.php?r=videos&amp;s=top-rated"{/if} class="section-header-tab">Top Rated</a>
          <a {if !$g_loc_videos_most_rated}href="{$g_config.base_uri}/index.php?r=videos&amp;s=most-rated"{/if} class="section-header-tab">Most Rated</a>
          <a {if !$g_loc_videos_most_discussed}href="{$g_config.base_uri}/index.php?r=videos&amp;s=most-discussed"{/if} class="section-header-tab">Most Discussed</a>
          <a {if !$g_loc_videos_top_favorited}href="{$g_config.base_uri}/index.php?r=videos&amp;s=top-favorited"{/if} class="section-header-tab">Top Favorited</a>
        </div>

        <div class="section-content horizontal-layout" style="position: relative; padding: 15px; padding-top: 30px;">

          <div style="position: absolute; right: 10px; top: 6px;" class="small">
            <a {if !$g_loc_today}href="{$g_config.base_uri}/index.php?r=videos&amp;s={$g_sort|urlencode}&amp;t=today"{/if}>Today</a> &mdash;
            <a {if !$g_loc_week}href="{$g_config.base_uri}/index.php?r=videos&amp;s={$g_sort|urlencode}&amp;t=week"{/if}>This Week</a> &mdash;
            <a {if !$g_loc_month}href="{$g_config.base_uri}/index.php?r=videos&amp;s={$g_sort|urlencode}&amp;t=month"{/if}>This Month</a> &mdash;
            <a {if !$g_loc_all_time}href="{$g_config.base_uri}/index.php?r=videos&amp;s={$g_sort|urlencode}&amp;t=all-time"{/if}>All Time</a>
          </div>

          {foreach var=$video from=$videos}
          <span>
            <div class="video-container small">
              <div style="position: relative;">
                <a href="{$g_config.base_uri}/index.php?r=video&amp;id={$video.video_id}">
                  <img src="{if $video.thumbnail}{$video.thumbnail}{else}{$g_config.no_preview}{/if}" class="video-thumb" title="{$video.title}" thumbs="{$video.num_thumbnails}" />
                </a>
                <img src="{$g_config.template_uri}/images/{$video.total_avg_rating|t_nearesthalf}-stars-shadow.png" class="stars" />
              </div>
              <a href="{$g_config.base_uri}/index.php?r=video&amp;id={$video.video_id}" title="{$video.title}">{$video.title|t_chop(30,'...')}</a><br />
              <span class="smallest">
                {$video.total_num_views|t_tostring} {"_Text:views"}<br />
                <a href="{$g_config.base_uri}/index.php?r=profile&amp;u={$video.username|urlencode}" class="normal">{$video.username}</a>
              </span>
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