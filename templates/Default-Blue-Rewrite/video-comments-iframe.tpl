<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title>{$g_config.site_name}</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="{$g_config.template_uri}/style.css" />
  </head>
  <body style="margin: 0 5px;">

  {comments var=$comments videoid=$g_video_id amount=20 page=$g_page_number pagination=$pagination sort="`date_commented` DESC"}

  {foreach var=$c from=$comments}
  <div style="margin: 8px; padding-bottom: 4px; border-bottom: 1px solid #efefef;">
    <div style="margin-bottom: 4px;">
      <a href="{$g_config.base_uri}/profile/{$c.username}/" target="_parent">{$c.username}</a> <span style="color: #afafaf;">({$c.date_commented|t_age})</span>
    </div>
    {$c.comment|htmlspecialchars|nl2br}
  </div>
  {/foreach}

  {if $pagination.total}
  <div class="pagination">
    {template file="global-pagination.tpl" uri="video-comments-iframe/$g_video_id"}
  </div>
  {else}
  <div class="message-warning">
    {"_Text:No comments submitted"}
  </div>
  {/if}

  </body>
</html>