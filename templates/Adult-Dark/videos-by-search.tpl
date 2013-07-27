{template file="global-header.tpl"}

<script language="JavaScript" type="text/javascript">
$(function()
{
    $('select[name="c"] option[value="'+{$g_category_id}+'"]').attr('selected', 'selected');
});
</script>


    <div class="main-content page-content">
      <span class="section-left">

        {* ADJUST THIS IF YOU HAVE MODIFIED THE MYSQL MINIMUM FULLTEXT SEARCH STRING LENGTH *}
        {if strlen($g_term) < 4}
          <div class="message-error" style="margin-top: 20px;">
            {"_Text:Search string too short"}
          </div>
        {else}

        {videos
        var=$videos
        amount=50
        searchterm=$g_term
        category=$g_category_id
        paginate=true
        pagination=$pagination
        sort=date_added DESC}

        <div class="section-content section-videos">
          <div class="header">
            <span class="header-left"></span>
            <span class="header-right"></span>
            <span class="header-text">{"_Text:Videos"} {$pagination.start} - {$pagination.end} {"_Text:of"} {$pagination.total} {"_Text:Matching"}: {$g_term}</span>
          </div>

          {if empty($videos)}
          <div class="message-warning">
            {"_Text:No Videos Matched Your Search"}
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
          {template file="global-pagination.tpl" uri="index.php?r=search&term=$g_term&p="}
          {/if}
        </div>
      </span>

      <span class="section-right">
        {template file="global-search-terms.tpl"}
        {template file="global-tags.tpl"}
        {template file="global-categories.tpl"}
      </span>

    {/if}
    </div>

{template file="global-footer.tpl"}