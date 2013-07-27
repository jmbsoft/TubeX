{template file="global-header.tpl"}

<script language="JavaScript" type="text/javascript">
$(function()
{
    $('select[name="c"] option[value="'+{$g_category_id}+'"]').attr('selected', 'selected');
});
</script>


    <div class="main-content" style="margin-top: 30px;">
    {* ADJUST THIS IF YOU HAVE MODIFIED THE MYSQL MINIMUM FULLTEXT SEARCH STRING LENGTH *}
    {if strlen($g_term) < 4}
      <div class="message-error" style="margin-bottom: 30px;">
        {"_Text:Search string too short"}
      </div>
    {else}
      <span class="section-left">

        {videos
        var=$videos
        amount=20
        searchterm=$g_term
        category=$g_category_id
        paginate=true
        pagination=$pagination
        sort=date_added DESC}

        {if $pagination.total > 0}
        <div class="section-header">{"_Text:Videos"} {$pagination.start} - {$pagination.end} {"_Text:of"} {$pagination.total} {"_Text:Matching"}: {$g_term}</div>
        <div class="section-content horizontal-layout" style="position: relative; padding: 15px;">

          {foreach var=$video from=$videos}
          <span>
            <div class="video-container small">
              <div style="position: relative;">
                <a href="{$g_config.base_uri}/video/{$video.video_id}/{$video.title|t_urlify(5)}">
                  <img src="{if $video.thumbnail}{$video.thumbnail}{else}{$g_config.no_preview}{/if}" class="video-thumb" title="{$video.title}" thumbs="{$video.num_thumbnails}" />
                </a>
                <img src="{$g_config.template_uri}/images/{$video.total_avg_rating|t_nearesthalf}-stars-shadow.png" class="stars" />
              </div>
              <a href="{$g_config.base_uri}/video/{$video.video_id}/{$video.title|t_urlify(5)}" title="{$video.title}">{$video.title|t_chop(30,'...')}</a><br />
              <span class="smallest">
                {$video.total_num_views|t_tostring} {"_Text:views"}<br />
                <a href="{$g_config.base_uri}/profile/{$video.username}/" class="normal">{$video.username}</a>
              </span>
            </div>
          </span>
          {/foreach}

        </div>

        <div class="pagination">
          {template file="global-pagination.tpl" uri="search/$g_term"}
        </div>
        {else}
        <div class="message-warning">{"_Text:No Videos Matched Your Search"}</div>
        {/if}
      </span>

      <span class="section-right">
        {template file="global-search-terms.tpl"}
        {template file="global-tags.tpl"}
        {template file="global-categories.tpl"}
      </span>

    {/if}
    </div>

{template file="global-footer.tpl"}