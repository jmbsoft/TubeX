{category var=$category url=$g_category_url}
{template file="global-header.tpl" meta_description=$category.meta_description meta_keywords=$category.meta_keywords}

    <div class="main-content page-content">

    {if empty($category)}
      <div class="message-error" style="margin-top: 20px;">
        {"_Text:No such category"}
      </div>
    {else}

      <span class="section-left">

        {videos
        var=$videos
        amount=50
        category=$category.category_id
        paginate=true
        pagination=$pagination
        sort=date_added DESC}

        <div class="section-content section-videos">
          <div class="header">
            <span class="header-left"></span>
            <span class="header-right"></span>
            <span class="header-text">{"_Text:Videos"} {$pagination.start} - {$pagination.end} {"_Text:of"} {$pagination.total} {"_Text:In Category"}: {$category.name}</span>
          </div>

          {if empty($videos)}
          <div class="message-warning">
            {"_Text:No Videos In This Category"}
          </div>
          {else}

          {foreach var=$video from=$videos}
          <span class="video">
            <div class="video-container">
              <a href="{$g_config.base_uri}/index.php?r=video&amp;id={$video.video_id}">
                <img src="{if $video.thumbnail}{$video.thumbnail}{else}{$g_config.no_preview}{/if}" class="video-thumb" title="{$video.title}" thumbs="{$video.num_thumbnails}" />
              </a>
              <div>
                <img src="{$g_config.template_uri}/images/{$video.total_avg_rating|t_nearesthalf}-stars.png" class="stars" />
                <span class="fs80">{$video.duration|t_duration}</span>
              </div>
              <div>
                <a href="{$g_config.base_uri}/index.php?r=video&amp;id={$video.video_id}" title="{$video.title}">{$video.title|t_chop(35,'...')}</a>
              </div>
              <span class="fs80">{$video.total_num_views|t_tostring} {if $video.total_num_views == 1}{"_Text:view"}{else}{"_Text:views"}{/if}</span>
            </div>
          </span>
          {/foreach}

          {/if}

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