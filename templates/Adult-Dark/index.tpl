{template file="global-header.tpl"}

    <div class="main-content page-content">
      <span class="section-left">

        <div class="section-content section-videos">
          <div class="header">
            <span class="header-left"></span>
            <span class="header-right"></span>
            <span class="header-text">{"_Text:Recently Viewed Videos"}</span>
          </div>

          {videos
          var=$videos
          amount=10
          featured=false
          sort=date_last_view DESC}

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

        <div class="section-content section-videos">
          <div class="header">
            <span class="header-left"></span>
            <span class="header-right"></span>
            <span class="header-text">{"_Text:New Videos"}</span>
          </div>

          {videos
          var=$videos
          amount=50
          featured=false
          sort=date_added DESC}

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

      </span>

      <span class="section-right">

        {template file="global-categories.tpl"}

        <div class="section-sidebar-content">
          <div class="header-red">
            <span class="header-red-left"></span>
            <span class="header-red-right"></span>
            <span class="header-red-text">{"_Text:Statistics"}</span>
          </div>

          {stats var=$stats}
          <div class="field">
            <span class="label nopad">{"_Text:Users"}:</span> {$stats.users|t_tostring}
          </div>
          <div class="field">
            <span class="label nopad">{"_Text:Videos"}:</span> {$stats.videos|t_tostring}
          </div>
          <div class="field">
            <span class="label nopad">{"_Text:Categories"}:</span> {$stats.categories|t_tostring}
          </div>
          <div class="field">
            <span class="label nopad">{"_Text:Comments"}:</span> {$stats.comments|t_tostring}
          </div>
        </div>

        {template file="global-tags.tpl"}


      </span>
    </div>

{template file="global-footer.tpl"}