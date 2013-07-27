{template file="global-header.tpl"}

    <div class="main-content" style="margin-top: 20px;">
      <span class="section-left">

        <div class="section-header">{"_Text:Recently Viewed Videos"}</div>
        <div class="section-content horizontal-layout" style="padding: 15px; padding-bottom: 0;">

          {videos
          var=$videos
          amount=4
          featured=false
          sort=date_last_view DESC}

          {foreach var=$video from=$videos}
          <span>
            <div class="video-container">
              <div style="position: relative;">
                <a href="{$g_config.base_uri}/index.php?r=video&amp;id={$video.video_id}">
                  <img src="{if $video.thumbnail}{$video.thumbnail}{else}{$g_config.no_preview}{/if}" class="video-thumb" title="{$video.title}" thumbs="{$video.num_thumbnails}" />
                </a>
                <img src="{$g_config.template_uri}/images/{$video.total_avg_rating|t_nearesthalf}-stars-shadow.png" class="stars" />
              </div>
              <a href="{$g_config.base_uri}/index.php?r=video&amp;id={$video.video_id}" title="{$video.title}">{$video.title|t_chop(30,'...')}</a>
            </div>
          </span>
          {/foreach}

        </div>

        <div class="section-header">{"_Text:Featured Videos"}</div>
        <div class="section-content">

          {videos
          var=$videos
          amount=5
          featured=true
          sort=RAND()}

          {foreach var=$video from=$videos}
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
          {/foreach}

        </div>

        <div class="section-header">{"_Text:New Videos"}</div>
        <div class="section-content">

          {videos
          var=$videos
          amount=5
          featured=false
          sort=date_added DESC}

          {foreach var=$video from=$videos}
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
          {/foreach}

        </div>
      </span>

      <span class="section-right">

        {nocache}
        {if $g_logged_in}
        {user var=$user username=$g_username}

        <div class="section-header">{"_Text:Overview"}</div>
        <div class="section-content">
          <div class="field">
            <span class="label nopad wider">{"_Text:Videos Watched"}:</span> {$user.total_videos_watched|t_tostring}
          </div>

          <div class="field">
            <span class="label nopad wider">{"_Text:Videos Uploaded"}:</span> {$user.total_videos_uploaded|t_tostring}
          </div>

          <div class="field">
            <span class="label nopad wider">{"_Text:Video Views"}:</span> {$user.total_video_views|t_tostring}
          </div>

          <div class="field">
            <span class="label nopad wider">{"_Text:Profile Views"}:</span> {$user.total_profile_views|t_tostring}
          </div>
        </div>
        {else}
        <div class="section-header">{"_Text:Account Login"}</div>
        <div class="section-content">
          <form action="{$g_config.base_uri}/user.php?r=login" method="post">
            <div class="field">
              <span class="label">{"_Label:Username"}:</span> <input type="text" name="username" size="20" />
            </div>
            <div class="field">
              <span class="label">{"_Label:Password"}:</span> <input type="password" name="password" size="20" />
            </div>
            <div class="field">
              <span class="label"></span> <input type="checkbox" name="remember" value="1"{if $g_form.remember} checked="checked"{/if} /> {"_Label:Remember Me"}
            </div>
            <div class="field">
              <span class="label"></span> <input type="submit" value="{"_Button:Login"}" />
            </div>
            <input type="hidden" name="r" value="login-submit" />
          </form>
        </div>
        {/if}
        {/nocache}

        <div class="section-header">{"_Text:Statistics"}</div>
        <div class="section-content">
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
        {template file="global-categories.tpl"}

      </span>
    </div>

{template file="global-footer.tpl"}