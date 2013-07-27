        <div class="section-sidebar-content text-center tags">
          <div class="header-red">
            <span class="header-red-left"></span>
            <span class="header-red-right"></span>
            <span class="header-red-text">{"_Text:Popular Tags"}</span>
          </div>

          {tags var=$tags sort="frequency DESC" alphabetize=true amount=50 minscore=100 maxscore=200}

          {foreach var=$tag from=$tags}
          <a href="{$g_config.base_uri}/index.php?r=tag&amp;tag={$tag.tag|urlencode}" class="tag-{$tag.score}">{$tag.tag}</a>
          {/foreach}

        </div>