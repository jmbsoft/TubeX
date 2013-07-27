        <div class="section-sidebar-content">
          <div class="header-red">
            <span class="header-red-left"></span>
            <span class="header-red-right"></span>
            <span class="header-red-text">{"_Text:Categories"}</span>
          </div>

          {categories var=$categories}

          <div class="fs105">
          {foreach var=$c from=$categories}
            &rsaquo; <a href="{$g_config.base_uri}/category/{$c.url_name}">{$c.name}</a><br />
          {/foreach}
          </div>

        </div>