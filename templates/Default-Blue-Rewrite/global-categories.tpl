        <div class="section-header">{"_Text:Categories"}</div>
        <div class="section-content" style="max-height: 40.5em; overflow: auto;">
          {categories var=$categories}

          {foreach var=$c from=$categories}
          &rsaquo; <a href="{$g_config.base_uri}/category/{$c.url_name}">{$c.name}</a><br />
          {/foreach}
        </div>