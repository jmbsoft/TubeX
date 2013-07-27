        <div class="section-sidebar-content text-center tags">
          <div class="header-red">
            <span class="header-red-left"></span>
            <span class="header-red-right"></span>
            <span class="header-red-text">{"_Text:Popular Search Terms"}</span>
          </div>

          {searchterms var=$terms sort="frequency DESC" alphabetize=true amount=50 minscore=100 maxscore=200}

          {foreach var=$term from=$terms}
          <a href="{$g_config.base_uri}/search/{$term.term|urlencode}/" class="tag-{$term.score}">{$term.term}</a>
          {/foreach}

        </div>