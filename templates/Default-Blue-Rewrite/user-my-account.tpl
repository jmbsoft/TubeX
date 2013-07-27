{template file="global-header.tpl"}

    <div class="main-content" style="margin-top: 30px;">
      <span class="section-left">

        {user var=$user username=$g_username}

        <div class="section-header">{"_Text:Overview"}</div>
        <div class="section-content">

          <span style="display: inline-block; vertical-align: top; text-align: center;">
            <img src="{if $user.uri}{$user.uri}{else}{$g_config.template_uri}/images/avatar-150x120.png{/if}" style="max-width: 100px; max-height: 100px;" /><br />
            <a href="{$g_config.base_uri}/user/avatar/" class="smallest">{"_Text:Change"}</a>
          </span>

          <span style="display: inline-block; vertical-align: top; margin-left: 10px;">
          <div style="font-size: 110%; margin-bottom: 4px;"><a href="{$g_config.base_uri}/profile/{$user.username}/">{$user.username}</a></div>
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