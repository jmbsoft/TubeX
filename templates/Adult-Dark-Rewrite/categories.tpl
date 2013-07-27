{template file="global-header.tpl"}

    <div class="main-content page-content">
      <span class="section-left">

        <div class="section-content section-videos">
          <div class="header">
            <span class="header-left"></span>
            <span class="header-right"></span>
            <span class="header-text">{"_Text:Categories"}</span>
          </div>

          {categories var=$categories}

          {foreach var=$category from=$categories counter=$counter}
          <span class="video">
            <div class="video-container">
              <a href="{$g_config.base_uri}/category/{$category.url_name}/">
                <img src="{if $category.uri}{$category.uri}{else}{$g_config.no_preview}{/if}" class="video-thumb" title="{$category.name}" />
              </a>
              <div class="text-center">
                <a href="{$g_config.base_uri}/category/{$category.url_name}/" class="fs110">{$category.name}</a> <span class="fs90" style="color: #999;">({$category.num_videos})</span><br />
              </div>
            </div>
          </span>
          {/foreach}

        </div>
      </span>

      <span class="section-right">
        {template file="global-tags.tpl"}
      </span>
    </div>

{template file="global-footer.tpl"}