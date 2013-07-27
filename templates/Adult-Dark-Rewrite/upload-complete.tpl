{template file="global-header.tpl" title="_Text:Upload a Video"}

    <div class="main-content page-content">
      <span class="section-whole">

        <div class="section-content">
          <div class="header">
            <span class="header-left"></span>
            <span class="header-right"></span>
            <span class="header-text">{"_Text:Upload a Video"}</span>
          </div>

          {"_Text:Video uploaded successfully"}

          {if $g_video.status == 'Queued'}
          {"_Text:Video will be converted"}
          {elseif $g_video.status == 'Pending'}
          {"_Text:Video will be reviewed"}
          {else}
            {if $g_video.is_private}
              {"_Text:Video is active and private"}
            {else}
              {"_Text:Video is now active"}
            {/if}
          {/if}

          {"_Text:Thanks for your upload"}

          <br />
          <br />

          <div class="text-center">
            <a href="{$g_config.base_uri}/upload/">Upload Another Video</a>
          </div>

        </div>

      </span>
    </div>

{template file="global-footer.tpl"}