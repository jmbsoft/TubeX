          {assign var=$page_start code=max($pagination.page-3, 1)}
          {assign var=$page_end code=min($page_start+6, $pagination.pages)}
          {if $page_end - $page_start < 7 }{assign var=$page_start code=max(1, $page_end - 6)}{/if}

          {if $pagination.page == 1}
          <span>&lt;&lt;</span>
          <span>&lt;</span>
          {else}
          <span><a href="{$g_config.base_uri}/{$uri}/" class="pagination-link">&lt;&lt;</a></span>
          <span><a href="{$g_config.base_uri}/{$uri}/{$pagination.prev_page}/" class="pagination-link">&lt;</a></span>
          {/if}

          {range start=$page_start end=$page_end counter=$counter}
          {if $counter == $pagination.page}
          <span>{$counter}</span>
          {else}
          <span><a href="{$g_config.base_uri}/{$uri}/{$counter}/" class="pagination-link">{$counter}</a></span>
          {/if}
          {/range}


          {if $pagination.page == $pagination.pages}
          <span>&gt;</span>
          <span>&gt;&gt;</span>
          {else}
          <span><a href="{$g_config.base_uri}/{$uri}/{$pagination.next_page}/" class="pagination-link">&gt;</a></span>
          <span><a href="{$g_config.base_uri}/{$uri}/{$pagination.pages}/" class="pagination-link">&gt;&gt;</a></span>
          {/if}