          {assign var=$page_start code=max($pagination.page-3, 1)}
          {assign var=$page_end code=min($page_start+6, $pagination.pages)}
          {if $page_end - $page_start < 7 }{assign var=$page_start code=max(1, $page_end - 6)}{/if}

          {if $pagination.page == 1}
          <span>&lt;&lt;</span>
          <span>&lt;</span>
          {else}
          <span><a href="" page="1">&lt;&lt;</a></span>
          <span><a href="" page="{$pagination.prev_page}">&lt;</a></span>
          {/if}

          {range start=$page_start end=$page_end counter=$counter}
          {if $counter == $pagination.page}
          <span>{$counter}</span>
          {else}
          <span><a href="" page="{$counter}">{$counter}</a></span>
          {/if}
          {/range}


          {if $pagination.page == $pagination.pages}
          <span>&gt;</span>
          <span>&gt;&gt;</span>
          {else}
          <span><a href="" page="{$pagination.next_page}">&gt;</a></span>
          <span><a href="" page="{$pagination.pages}">&gt;&gt;</a></span>
          {/if}


<script language="JavaScript" type="text/javascript">
$(function()
{
    $('a[page]')
    .click(function()
           {
               $('#paginated-form input[name="p"]').val($(this).attr('page'));
               $('#paginated-form').submit();
               return false;
           });
});
</script>