{template file="global-header.tpl" title="_Text:Upload a Video"}

    <div class="main-content" style="margin-top: 30px;">
      <span class="section-whole">

        <div class="section-header">{"_Text:Upload a Video"}</div>
        <div class="section-content">

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

          <div style="text-align: center;">
          <a href="{$g_config.base_uri}/upload/">Upload Another Video</a>
          </div>

        </div>

      </span>
    </div>

{template file="global-footer.tpl"}