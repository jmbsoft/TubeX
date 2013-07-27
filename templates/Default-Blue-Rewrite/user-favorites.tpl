{template file="global-header.tpl"}

<script language="JavaScript" type="text/javascript">
$(function()
{
    // Remove favorite
    $('a.favorite-remove')
    .click(function()
           {
               if( confirm("{"_Text:Confirm remove favorite"}") )
               {
                   var video_id = $(this).attr('videoid');

                   $('#favorite-'+video_id+' .video-brief-details')
                   .addClass('largest red bold')
                   .html('{"_Text:REMOVED"}');

                   $('#favorite-'+video_id+' .video-brief-thumb')
                   .css({opacity: 0.1})
                   .find('a')
                   .removeAttr('href');

                   $.ajax({
                           url: $(this).attr('href'),
                           dataType: 'html',
                           cache: false,
                           timeout: 0
                          });

                   $(this).remove();
               }

               return false;
           });

});
</script>

    <div class="main-content" style="margin-top: 30px;">
      <span class="section-left">

        <div class="section-header">{"_Text:Favorites"}</div>
        <div class="section-content">

          {videos
           var=$videos
           amount=5
           username=$g_username
           favorites=true
           paginate=true
           pagination=$pagination
           sort="date_added DESC"}

          {if empty($videos)}

          <div class="message-warning">
            {"_Text:No Favorites"}
          </div>

          {else}

          <div class="largest center" style="font-weight: bold; border-bottom: 2px solid #afafaf;">
            {"_Text:Videos"} {$pagination.start} - {$pagination.end} {"_Text:of"} {$pagination.total}
          </div>

          {foreach var=$video from=$videos}
          <div class="video-brief" id="favorite-{$video.video_id}">
            <span class="video-brief-thumb">
              <a href="{$g_config.base_uri}/video/{$video.video_id}/{$video.title|t_urlify(5)}">
                <img src="{if $video.thumbnail}{$video.thumbnail}{else}{$g_config.no_preview}{/if}" class="video-thumb" title="{$video.title}" thumbs="{$video.num_thumbnails}" />
              </a>
            </span>
            <span class="video-brief-details">
              <a href="{$g_config.base_uri}/video/{$video.video_id}/{$video.title|t_urlify(5)}" class="large">{$video.title}</a><br />
              {$video.description|t_chop(80,'...')}
              <div class="video-brief-facets smallest">
                <span><img src="{$g_config.template_uri}/images/{$video.total_avg_rating|t_nearesthalf}-stars.png" /></span>
                <span>{$video.total_num_views|t_tostring} {"_Text:views"}</span>
                <span>{$video.date_added|t_age}</span>
                <span><a href="{$g_config.base_uri}/user/favorite-remove/{$video.video_id}/" class="favorite-remove" videoid="{$video.video_id}">{"_Text:Remove"}</a></span>
              </div>

            </span>
          </div>
          {/foreach}

          <div class="pagination">
            {template file="global-pagination.tpl" uri="user/favorites"}
          </div>

          {/if}

        </div>
      </span>

      <span class="section-right">

        {template file="user-menu.tpl"}

      </span>
    </div>

{template file="global-footer.tpl"}