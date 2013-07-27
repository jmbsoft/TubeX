        <div class="section-header">{"_Text:Popular Search Terms"}</div>
        <div class="section-content center tags">

          {searchterms var=$terms sort="frequency DESC" alphabetize=true amount=50 minscore=100 maxscore=200}

          {foreach var=$term from=$terms}
          <a href="{$g_config.base_uri}/search/{$term.term|urlencode}/" class="tag-{$term.score}">{$term.term}</a>
          {/foreach}

        </div>