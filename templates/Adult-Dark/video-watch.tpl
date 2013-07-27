{video var=$video videoid=$g_video_id}
{template file="global-header.tpl" video_watch="true" title=$video.title meta_description=$video.title meta_keywords=$video.tags}

<script language="JavaScript">
{nocache}
var logged_in = {if $g_logged_in}true{else}false{/if};
{/nocache}
var guest_rating = '{$g_config.flag_guest_ratings}';
var login_to_rate = '{"_Text:Login to rate"}';
var video_id = {$g_video_id};
var base_uri = '{$g_config.base_uri}';
var template_uri = '{$g_config.template_uri}';
var star_text = new Array('{"_Text:1 Star"}',
                          '{"_Text:2 Stars"}',
                          '{"_Text:3 Stars"}',
                          '{"_Text:4 Stars"}',
                          '{"_Text:5 Stars"}');
</script>

{category var=$category id=$video.category_id}

{if $video.sponsor_id}
{sponsor var=$sponsor sponsorid=$video.sponsor_id}
{/if}

    <div class="main-content page-content">

    {if empty($video)}
      <div class="message-error" style="margin-bottom: 30px;">
        {"_Text:No such video"}
      </div>
    {else}
      <span class="section-left">

        <div class="section-content section-videos">
          <div class="header">
            <span class="header-left"></span>
            <span class="header-right"></span>
            <span class="header-text">{$video.title|t_chop(60,'...')}</span>
          </div>

          <div class="video-player-container" style="display: inline-block; margin-bottom: 30px;">
            {clips var=$clips videoid=$g_video_id}
            {player clips=$clips flv="video-player-flash.tpl" wmv="video-player-silverlight.tpl" qt="video-player-quicktime.tpl" other="video-player-other.tpl"}

            <div style="position: relative; text-align: left;">
              {if $video.allow_ratings}
              <div class="rater-div" stars="1"></div>
              <div class="rater-div" stars="2" style="left: 19.6px;"></div>
              <div class="rater-div" stars="3" style="left: 39.2px;"></div>
              <div class="rater-div" stars="4" style="left: 58.8px;"></div>
              <div class="rater-div" stars="5" style="left: 78.4px;"></div>
              {/if}
              <img src="{$g_config.template_uri}/images/{$video.total_avg_rating|t_nearesthalf}-stars-large.png"
                   osrc="{$g_config.template_uri}/images/{$video.total_avg_rating|t_nearesthalf}-stars-large.png" id="rater-stars" width="98" height="21" />
              <span id="rater-text"></span>
              <span class="bold" style="position: absolute; right: 0; top: 0.3em">{$video.total_num_views|t_tostring} {if $video.total_num_views == 1}{"_Text:view"}{else}{"_Text:views"}{/if}</span>
            </div>
          </div>

          <div id="panel-rating-message"></div>

          {if count($clips) > 1}
          <div id="clips" class="text-center">
            <b>Clips From This Video:</b><br />
            {foreach var=$clip from=$clips}
              <img src="{if $clip.thumbnail}{$clip.thumbnail}{else}{$g_config.no_preview}{/if}" href="{$clip.clip}" />
            {/foreach}
          </div>
          {/if}


          <div style="padding: 8px 4px;">
            {$video.description}
          </div>

          <div style="margin-bottom: 4px;">
            <span style="display: inline-block; width: 5em; text-align: right; font-weight: bold;">{"_Label:Category"}:</span>
            <span style="display: inline-block;"><a href="{$g_config.base_uri}/index.php?r=category&amp;c={$category.url_name|urlencode}">{$category.name}</a></span>
          </div>

          <div style="margin-bottom: 8px;">
            <span style="display: inline-block; width: 5em; text-align: right; font-weight: bold; vertical-align: top;">{"_Label:Tags"}:</span>
            <span style="display: inline-block; width: 75%;">
              {assign var=$tags code=explode(' ', $video.tags)}
              {foreach var=$tag from=$tags}
              <a href="{$g_config.base_uri}/index.php?r=tag&amp;tag={$tag|urlencode}">{$tag}</a>
              {/foreach}
            </span>
          </div>
        </div>

        <div class="section-content section-videos">
          <div class="header">
            <span class="header-left"></span>
            <span class="header-right"></span>
            <span class="header-text">{"_Text:Comments and Statistics"}</span>
          </div>

          <div style="padding: 4px; padding-bottom: 0px; border-bottom: 2px solid #999; margin-bottom: 4px;" class="large bold">
            {"_Text:Statistics"}
          </div>

          <div id="info-panel"></div>

          <table style="padding: 6px; margin-bottom: 20px;" width="100%">
            <tr>
              <td style="width: 33%;">
                <b>{"_Text:Added"}:</b> {$video.date_added|t_datetime}
              </td>
              <td style="width: 33%;">
                <b>{"_Text:Views"}:</b> {$video.total_num_views|t_tostring}
              </td>
              <td style="width: 33%;">
                <b>{"_Text:Ratings"}:</b> {$video.total_num_ratings|t_tostring}
              </td>
            </tr>
            <tr>
              <td style="width: 33%;">
                <b>{"_Text:Comments"}:</b> {$video.total_num_comments|t_tostring}
              </td>
              <td style="width: 33%;">
                <b>{"_Text:Favorited"}:</b> {$video.total_num_favorited|t_tostring}
                {nocache}
                {if $g_logged_in}
                <span class="fs80">
                  (<a href="" id="link-fav-add">{"_Text:Add"}</a>,
                  <a href="" id="link-fav-remove">{"_Text:Remove"}</a>)
                </span>
                {/if}
                {/nocache}
              </td>
              <td style="width: 33%;">
              </td>
            </tr>
            <tr>
              <td colspan="3">
                {ratings var=$ratings amount=4 videoid=$video.video_id sort="`date_rated` DESC"}

                {if !empty($ratings)}
                <div class="bold" style="margin-bottom: 4px; border-top: 1px solid #666; margin-top: 4px; padding-top: 4px;">{"_Text:Recently Rated"}</div>

                {foreach var=$r from=$ratings}
                <span style="display: inline-block; width: 24%; margin-left: 8px;" class="fs80">
                  <img src="{$g_config.template_uri}/images/{$r.rating}-stars.png" /><br />
                  <a href="{$g_config.base_uri}/index.php?r=profile&amp;u={$r.username|urlencode}" class="normal">{$r.username}</a>
                </span>
                {/foreach}
                {/if}

              </td>
          </table>

          <div style="padding: 4px; padding-bottom: 0px; border-bottom: 2px solid #999; margin-bottom: 4px;" class="large bold">
            {"_Text:Comments"} {nocache}{if $g_logged_in}<span class="fs80">(<a href="" id="link-comment-add">{"_Text:Add Yours"}</a>)</span>{/if}{/nocache}
          </div>

          {nocache}
          <div id="comment-form" style="display: none; padding-bottom: 8px; border-bottom: 1px solid #666; margin-left: 20px;">
            <div id="comment-info-panel"></div>
            {if $g_logged_in}
            <b>{"_Label:Comment"}:</b><br />
            <textarea id="comment-text" rows="5" cols="60"></textarea><br />
            <span class="fs80">{"_Text:Comment restriction",$g_config.comment_max_length} {"_Text:Current length"}: <span id="comment-length"></span></span><br />
            {if $g_config.flag_captcha_on_comment}
            <div style="margin: 4px 0px;">
               <b>{"_Label:Verification"}:</b><br />
                <img src="" class="captcha-image" />
                <img src="{$g_config.template_uri}/images/reload-22x22.png" class="captcha-reload" />
                <br />
                <input type="text" id="comment-captcha" size="20" />
            </div>
            {/if}
            <button id="button-comment">{"_Button:Submit"}</button>
            {/if}
          </div>
          {/nocache}

          {comments var=$comments videoid=$video.video_id amount=20 page=1 pagination=$pagination sort="`date_commented` DESC"}

          <div id="video-comments">
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
          </div>

        </div>
      </span>

      <span class="section-right">

        <div class="section-sidebar-content">
          <div class="header-red">
            <span class="header-red-left"></span>
            <span class="header-red-right"></span>
            <span class="header-red-text">{"_Text:Related Videos"}</span>
          </div>

          {videos var=$videos related=$video amount=10 sort="`date_added` DESC"}

          {foreach var=$v from=$videos}
          <div class="video-brief">
            <span class="video-brief-thumb">
              <a href="{$g_config.base_uri}/index.php?r=video&amp;id={$v.video_id}">
                <img src="{if $v.thumbnail}{$v.thumbnail}{else}{$g_config.no_preview}{/if}" width="90" height="68" class="video-thumb" thumbs="{$v.num_thumbnails}" title="{$v.title}" />
              </a>
            </span>
            <span class="video-brief-details" class="fs90" style="width: 58%;">
              <a href="{$g_config.base_uri}/index.php?r=video&amp;id={$v.video_id}" class="fs90">{$v.title|t_chop(30,'...')}</a>
              <div class="video-brief-facets fs80">
                <span>{$v.total_num_views|t_tostring} {if $v.total_num_views == 1}{"_Text:view"}{else}{"_Text:views"}{/if}</span><br />
                <span><img src="{$g_config.template_uri}/images/{$v.total_avg_rating|t_nearesthalf}-stars.png" /></span>
              </div>
            </span>
          </div>
          {/foreach}

        </div>

      </span>

    {/if}
    </div>

{template file="global-footer.tpl"}