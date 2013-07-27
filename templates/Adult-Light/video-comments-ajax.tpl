  {comments var=$comments videoid=$g_video_id amount=20 page=$g_page_number pagination=$pagination sort="`date_commented` DESC"}

  {foreach var=$c from=$comments}
  <div style="margin: 8px; padding-bottom: 4px; border-bottom: 1px solid #666;">
    <div style="margin-bottom: 4px;">
      <a href="{$g_config.base_uri}/index.php?r=profile&amp;u={$c.username|urlencode}" target="_parent">{$c.username}</a> <span style="color: #afafaf;">({$c.date_commented|t_age})</span>
    </div>
    {$c.comment|htmlspecialchars|nl2br}
  </div>
  {/foreach}

  {if $pagination.total}
  <div class="pagination">
    {template file="global-pagination.tpl" uri="index.php?r=video-comments&d=ajax&id=$g_video_id&p="}
  </div>
  {else}
  <div class="message-warning">
    {"_Text:No comments submitted"}
  </div>
  {/if}