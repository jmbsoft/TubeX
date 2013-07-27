{template file="global-header.tpl"}

<script language="JavaScript" type="text/javascript">
$(function()
{
    // Select sort field
    $('select[name="s"] option[value="{$g_sorter}"]').attr('selected', 'selected');

    // Reset form
    $('input[value="{"_Button:Search"}"]')
    .click(function()
           {
               $('input[name="p"]').val(1);
           });


    // Delete video
    $('a.video-delete')
    .click(function()
           {
               if( confirm("{"_Text:Confirm delete video"}") )
               {
                   var video_id = $(this).attr('videoid');

                   $('#video-'+video_id+' .video-brief-details')
                   .addClass('largest red bold')
                   .html('{"_Text:DELETED"}');

                   $('#video-'+video_id+' .video-brief-thumb')
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

        <div class="section-header">{"_Text:My Videos"}</div>
        <div class="section-content">

          <div class="center" style="margin-bottom: 10px;">
          <form action="{$g_config.base_uri}/user/my-videos/" method="post" id="paginated-form">
            <b>{"_Text:Term"}:</b> <input type="text" name="t" value="{$g_my_videos_term}" size="40" />
            <b>{"_Text:Sort"}:</b>
            <select name="s" style="margin-top: 6px;">
              <option value="added">{"_Text:Date Added"}</option>
              <option value="title">{"_Text:Title"}</option>
              <option value="duration">{"_Text:Duration"}</option>
              <option value="rating">{"_Text:Rating"}</option>
              <option value="views">{"_Text:Views"}</option>
            </select>

            <input type="submit" value="{"_Button:Search"}" />
            <input type="hidden" name="r" value="my-videos" />
            <input type="hidden" name="p" value="{$g_page_number}" />
          </form>
          </div>

          {videos
           var=$videos
           amount=20
           username=$g_username
           searchterm=$g_my_videos_term
           emptysearch=true
           private=allow
           paginate=true
           pagination=$pagination
           sort=$g_videos_sorter}

          {if empty($videos)}

          <div class="message-warning">
            {"_Text:No Videos Matched Your Search"}
          </div>

          {else}

          <div class="largest center" style="font-weight: bold; border-bottom: 2px solid #afafaf;">
            {"_Text:Videos"} {$pagination.start} - {$pagination.end} {"_Text:of"} {$pagination.total}
          </div>

          {foreach var=$video from=$videos}
          <div class="video-brief" id="video-{$video.video_id}">
            <span class="video-brief-thumb">
              {if $video.is_private}
              <a href="{$g_config.base_uri}/private/{$video.video_id}/{$video.private_id}/">
              {else}
              <a href="{$g_config.base_uri}/video/{$video.video_id}/{$video.title|t_urlify(5)}">
              {/if}
              <img src="{if $video.thumbnail}{$video.thumbnail}{else}{$g_config.no_preview}{/if}" class="video-thumb" title="{$video.title}" thumbs="{$video.num_thumbnails}" /></a>
            </span>
            <span class="video-brief-details">
              {if $video.is_private}
              <a href="{$g_config.base_uri}/private/{$video.video_id}/{$video.private_id}/" class="large">
              {else}
              <a href="{$g_config.base_uri}/video/{$video.video_id}/{$video.title|t_urlify(5)}" class="large">
              {/if}
              {$video.title}</a><br />
              {$video.description|t_chop(80,'...')}
              <div class="video-brief-facets smallest">
                <span><img src="{$g_config.template_uri}/images/{$video.total_avg_rating|t_nearesthalf}-stars.png" /></span>
                <span>{$video.total_num_views|t_tostring} {"_Text:views"}</span>
                <span>{$video.date_added|t_age}</span>
                <br />
                <span><a href="{$g_config.base_uri}/user/video-edit/{$video.video_id}/">{"_Text:Edit"}</a></span>
                <span><a href="{$g_config.base_uri}/user/video-delete/{$video.video_id}/" class="video-delete" videoid="{$video.video_id}">{"_Text:Delete"}</a></span>
              </div>

            </span>
          </div>
          {/foreach}

          <div class="pagination">
            {template file="global-pagination-js.tpl"}
          </div>

          {/if}

        </div>
      </span>

      <span class="section-right">

        {template file="user-menu.tpl"}

      </span>
    </div>

{template file="global-footer.tpl"}