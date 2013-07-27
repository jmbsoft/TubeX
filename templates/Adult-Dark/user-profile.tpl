{template file="global-header.tpl"}
{user var=$user username=$g_username}

    <div class="main-content page-content">

    {if empty($user)}
      <div class="message-error" style="margin-bottom: 30px;">
        {"_Text:No such user"}
      </div>
    {else}

      <span class="section-left">

        <div class="section-content">
          <div class="header">
            <span class="header-left"></span>
            <span class="header-right"></span>
            <span class="header-text">{"_Text:User Profile"} &mdash; {$user.username}</span>
          </div>

          <span class="profile-fields">
          <span class="field">{"_Text:Name"}:</span> <span class="value">{$user.name}</span><br />
          <span class="field">{"_Text:Signup"}:</span> <span class="value">{$user.date_created|t_age}</span><br />
          <span class="field">{"_Text:Last Login"}:</span> <span class="value">{$user.date_last_login|t_age}</span><br />
          <span class="field">{"_Text:Gender"}:</span> <span class="value">{$user.gender|t_translate("Text")}</span><br />
          <span class="field">{"_Text:Birthday"}:</span> <span class="value">{$user.date_birth|t_date("F jS")}</span><br />
          <span class="field">{"_Text:Relationship"}:</span> <span class="value">{$user.relationship|t_translate("Text")}</span><br />
          <span class="field">{"_Text:About"}:</span> <span class="value">{$user.about}</span><br />
          <span class="field">{"_Text:Website"}:</span> <span class="value"><a href="{$user.website_url}" target="_blank">{$user.website_url}</a></span><br />
          <span class="field">{"_Text:Hometown"}:</span> <span class="value">{$user.hometown}</span><br />
          <span class="field">{"_Text:City"}:</span> <span class="value">{$user.current_city}</span><br />
          <span class="field">{"_Text:Country"}:</span> <span class="value">{$user.current_country}</span><br />
          <span class="field">{"_Text:Occupations"}:</span> <span class="value">{$user.occupations}</span><br />
          <span class="field">{"_Text:Companies"}:</span> <span class="value">{$user.companies}</span><br />
          <span class="field">{"_Text:Schools"}:</span> <span class="value">{$user.schools}</span><br />
          <span class="field">{"_Text:Hobbies"}:</span> <span class="value">{$user.hobbies}</span><br />
          <span class="field">{"_Text:Movies"}:</span> <span class="value">{$user.movies}</span><br />
          <span class="field">{"_Text:Music"}:</span> <span class="value">{$user.music}</span><br />
          <span class="field">{"_Text:Books"}:</span> <span class="value">{$user.books}</span>
          </span>

        </div>


        <div class="section-content">
          <div class="header">
            <span class="header-left"></span>
            <span class="header-right"></span>
            <span class="header-text">{"_Text:Newest Videos"}</span>
          </div>

          {videos
          var=$videos
          amount=5
          username=$user.username
          sort=date_added DESC}

          {if empty($videos)}

          <div class="message-warning">
            {"_Text:This user has not yet uploaded any videos"}
          </div>

          {else}

          {foreach var=$video from=$videos}
          <div class="video-brief">
            <span class="video-brief-thumb">
              <a href="{$g_config.base_uri}/index.php?r=video&amp;id={$video.video_id}">
                <img src="{if $video.thumbnail}{$video.thumbnail}{else}{$g_config.no_preview}{/if}" class="video-thumb" title="{$video.title}" thumbs="{$video.num_thumbnails}" />
              </a>
            </span>
            <span class="video-brief-details">
              <a href="{$g_config.base_uri}/index.php?r=video&amp;id={$video.video_id}" class="fs110">{$video.title}</a><br />
              {$video.description|t_chop(80,'...')}
              <div class="video-brief-facets fs80">
                <span><img src="{$g_config.template_uri}/images/{$video.total_avg_rating|t_nearesthalf}-stars.png" /></span>
                <span>{$video.total_num_views|t_tostring} {"_Text:views"}</span>
                <span>{$video.date_added|t_age}</span>
              </div>
            </span>
          </div>
          {/foreach}

          {/if}

        </div>
      </span>

      <span class="section-right">

        <div class="section-sidebar-content text-center">
          <div class="header-red">
            <span class="header-red-left"></span>
            <span class="header-red-right"></span>
            <span class="header-red-text">{"_Text:Avatar"}</span>
          </div>

          <img src="{if $user.uri}{$user.uri}{else}{$g_config.template_uri}/images/avatar-150x120.png{/if}" class="profile-avatar" />

        </div>

      </span>

    {/if} {* END if empty($user) *}
    </div>

{template file="global-footer.tpl"}