        <div class="section-header">{"_Text:Popular Tags"}</div>
        <div class="section-content center tags">

          {tags var=$tags sort="frequency DESC" alphabetize=true amount=50 minscore=100 maxscore=200}

          {foreach var=$tag from=$tags}
          <a href="{$g_config.base_uri}/index.php?r=tag&amp;tag={$tag.tag|urlencode}" class="tag-{$tag.score}">{$tag.tag}</a>
          {/foreach}

        </div>