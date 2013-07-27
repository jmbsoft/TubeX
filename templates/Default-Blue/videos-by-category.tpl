{category var=$category url=$g_category_url}
{template file="global-header.tpl" meta_description=$category.meta_description meta_keywords=$category.meta_keywords}

    <div class="main-content" style="margin-top: 30px;">

    {if empty($category)}
      <div class="message-error" style="margin-bottom: 30px;">
        {"_Text:No such category"}
      </div>
    {else}

      <span class="section-left">

        {videos
        var=$videos
        amount=20
        category=$category.category_id
        paginate=true
        pagination=$pagination
        sort=date_added DESC}

        <div class="section-header">{"_Text:Videos"} {$pagination.start} - {$pagination.end} {"_Text:of"} {$pagination.total} {"_Text:In Category"}: {$category.name}</div>
        <div class="section-content horizontal-layout" style="position: relative; padding: 15px;">

          {foreach var=$video from=$videos}
          <span>
            <div class="video-container small">
              <div style="position: relative;">
                <a href="{$g_config.base_uri}/index.php?r=video&amp;id={$video.video_id}">
                  <img src="{if $video.thumbnail}{$video.thumbnail}{else}{$g_config.no_preview}{/if}" class="video-thumb" title="{$video.title}" thumbs="{$video.num_thumbnails}" />
                </a>
                <img src="{$g_config.template_uri}/images/{$video.total_avg_rating|t_nearesthalf}-stars-shadow.png" class="stars" />
              </div>
              <a href="{$g_config.base_uri}/index.php?r=video&amp;id={$video.video_id}" title="{$video.title}">{$video.title|t_chop(30,'...')}</a><br />
              <span class="smallest">
                {$video.total_num_views|t_tostring} {"_Text:views"}<br />
                <a href="{$g_config.base_uri}/index.php?r=profile&amp;u={$video.username|urlencode}" class="normal">{$video.username}</a>
              </span>
            </div>
          </span>
          {/foreach}

        </div>

        <div class="pagination">
          {if $pagination.total}
          {template file="global-pagination.tpl" uri="index.php?r=category&c=$category.url_name&p="}
          {/if}
        </div>
      </span>

      <span class="section-right">
        {template file="global-tags.tpl"}
        {template file="global-categories.tpl"}
      </span>

    {/if} {* END if empty($category) *}
    </div>

{template file="global-footer.tpl"}