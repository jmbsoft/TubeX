{template file="global-header.tpl"}

    <div class="main-content page-content">
      <span class="section-left">

      <div class="section-content">
        <div class="header">
          <span class="header-left"></span>
          <span class="header-right"></span>
          <span class="header-text">{"_Text:Overview"}</span>
        </div>

        {user var=$user username=$g_username}

          <span style="display: inline-block; vertical-align: top; text-align: center;">
            <img src="{if $user.uri}{$user.uri}{else}{$g_config.template_uri}/images/avatar-150x120.png{/if}" style="max-width: 100px; max-height: 100px;" /><br />
            <a href="{$g_config.base_uri}/user.php?r=avatar" class="smallest">{"_Text:Change"}</a>
          </span>

          <span style="display: inline-block; vertical-align: top; margin-left: 10px;">
          <div style="font-size: 110%; margin-bottom: 4px;"><a href="{$g_config.base_uri}/index.php?r=profile&amp;u={$user.username|urlencode}">{$user.username}</a></div>
          <b>{"_Text:Videos Watched"}:</b> {$user.total_videos_watched}<br />
          <b>{"_Text:Videos Uploaded"}:</b> {$user.total_videos_uploaded}<br />
          <b>{"_Text:Video Views"}:</b> {$user.total_video_views}<br />
          <b>{"_Text:Profile Views"}:</b> {$user.total_profile_views}<br />
          <b>{"_Text:Favorites"}:</b> {$g_num_favorites}<br />
          <b>{"_Text:Comments Submitted"}:</b> {$user.total_comments_submitted}<br />
          </span>

        </div>

      </span>

      <span class="section-right">

        {template file="user-menu.tpl"}

      </span>
    </div>

{template file="global-footer.tpl"}