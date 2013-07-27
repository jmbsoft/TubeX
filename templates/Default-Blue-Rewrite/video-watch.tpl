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

    <div class="main-content" style="margin-top: 30px;">

    {if empty($video)}
      <div class="message-error" style="margin-bottom: 30px;">
        {"_Text:No such video"}
      </div>
    {else}
      <span class="section-left">

        <div class="section-header">{$video.title}</div>
        <div class="section-content center">

          <div class="video-player-container" style="display: inline-block; margin-bottom: 30px;">
            {clips var=$clips videoid=$g_video_id}
            {player clips=$clips flv="video-player-flash.tpl" wmv="video-player-silverlight.tpl" qt="video-player-quicktime.tpl" other="video-player-other.tpl"}

            <div style="position: relative; text-align: left;">
              {if $video.allow_ratings}
              <div class="rater-div" stars="1"></div>
              <div class="rater-div" stars="2" style="left: 18.6px;"></div>
              <div class="rater-div" stars="3" style="left: 37.2px;"></div>
              <div class="rater-div" stars="4" style="left: 55.8px;"></div>
              <div class="rater-div" stars="5" style="left: 74.4px;"></div>
              {/if}
              <img src="{$g_config.template_uri}/images/{$video.total_avg_rating|t_nearesthalf}-stars-large.png"
                   osrc="{$g_config.template_uri}/images/{$video.total_avg_rating|t_nearesthalf}-stars-large.png" id="rater-stars" width="93" height="20" />
              <span id="rater-text"></span>
              <span class="bold" style="position: absolute; right: 0; top: 0.3em">{$video.total_num_views|t_tostring} {"_Text:views"}</span>
            </div>
          </div>

          <div id="panel-rating-message"></div>

          {if count($clips) > 1}
          <div id="clips">
            <b>Clips From This Video:</b><br />
            {foreach var=$clip from=$clips}
              <img src="{if $clip.thumbnail}{$clip.thumbnail}{else}{$g_config.no_preview}{/if}" href="{$clip.clip}" />
            {/foreach}
          </div>
          {/if}

          <div class="inner-section-header" style="max-height: 1.2em; overflow: visible; margin-top: 15px;">
            <span href="#panel-feature" class="selected">{"_Text:Feature"}</span>
            <span href="#panel-flagged">{"_Text:Flag"}</span>
            <span href="#panel-favorite">{"_Text:Favorite"}</span>
            <span href="#panel-comment">{"_Text:Comment"}</span>
          </div>
          <div class="inner-section-content panels">
            <div id="panel-favorite">
              {if $video.is_private}
              {"_Text:Private cannot be favorited"}
              {else}
              {nocache}
              {if $g_logged_in}
              <button id="button-fav-add">{"_Button:Add to Favorites"}</button>
              <button id="button-fav-remove">{"_Button:Remove From Favorites"}</button>
              {else}
              {"_Text:Want to add favorites"}
              {"_Text:Sign In or Sign Up now",$g_config.base_uri,$g_config.base_uri}
              {/if}
              {/nocache}
              {/if}
            </div>

            <div id="panel-comment">
              {nocache}
              {if $g_logged_in}
              <b>{"_Label:Comment"}:</b><br />
              <textarea id="comment-text" rows="5" cols="60"></textarea><br />
              <span class="smallest">{"_Text:Comment restriction",$g_config.comment_max_length} {"_Text:Current length"}: <span id="comment-length"></span></span><br />
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

              {else}
              {"_Text:Want to add comments"}
              {"_Text:Sign In or Sign Up now",$g_config.base_uri,$g_config.base_uri}
              {/if}
              {/nocache}
            </div>

            <div id="panel-feature">
              {nocache}
              {if $g_logged_in}
              {/nocache}
              Select the reason you think this video should be featured:<br />
              <select id="reason-feature" style="margin-top: 10px;">
                {reasons var=$reasons type="Feature"}
                {options from=$reasons key="reason_id" value="short_name"}
              </select>
              <button id="button-feature">Feature</button>
              {nocache}
              {else}
              {"_Text:Want to feature a video"}
              {"_Text:Sign In or Sign Up now",$g_config.base_uri,$g_config.base_uri}
              {/if}
              {/nocache}
            </div>

            <div id="panel-flagged">
              {nocache}
              {if $g_logged_in}
              {/nocache}
              Select the reason you are flagging this video:<br />
              <select id="reason-flagged" style="margin-top: 10px;">
                {reasons var=$reasons type="Flag"}
                {options from=$reasons key="reason_id" value="short_name"}
              </select>
              <button id="button-flag">Flag</button>
              {nocache}
              {else}
              {"_Text:Want to flag a video"}
              {"_Text:Sign In or Sign Up now",$g_config.base_uri,$g_config.base_uri}
              {/if}
              {/nocache}
            </div>
          </div>

        </div>

        <div class="section-header">{"_Text:Comments and Statistics"}</div>
        <div class="section-content">

          <div style="padding: 4px; padding-bottom: 0px; border-bottom: 2px solid #afafaf; margin-bottom: 4px;" class="large bold">
            {"_Text:Statistics"}
          </div>

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
              </td>
              <td style="width: 33%;">
              </td>
            </tr>
            <tr>
              <td colspan="3">
                <div class="bold" style="margin-bottom: 4px; border-top: 1px solid #efefef; margin-top: 4px; padding-top: 4px;">{"_Text:Recently Rated"}</div>

                {ratings var=$ratings amount=4 videoid=$video.video_id sort="`date_rated` DESC"}

                {foreach var=$r from=$ratings}
                <span style="display: inline-block; width: 24%; margin-left: 8px;" class="smallest">
                  <img src="{$g_config.template_uri}/images/{$r.rating}-stars.png" /><br />
                  <a href="{$g_config.base_uri}/profile/{$r.username}/" class="normal">{$r.username}</a>
                </span>
                {/foreach}

              </td>
          </table>

          <div style="padding: 4px; padding-bottom: 0px; border-bottom: 2px solid #afafaf; margin-bottom: 4px;" class="large bold">
            {"_Text:Comments"}
          </div>

          {comments var=$comments videoid=$video.video_id amount=20 page=1 pagination=$pagination sort="`date_commented` DESC"}

          <div id="video-comments">
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
              {template file="global-pagination.tpl" uri="video-comments-ajax/$g_video_id"}
            </div>
            {else}
            <div class="message-warning">
              {"_Text:No comments submitted"}
            </div>
            {/if}
          </div>

          {* <iframe src="{$g_config.base_uri}/video-comments-iframe/{$video.video_id}/1/" class="comments-iframe" frameborder="0"></iframe> *}

        </div>
      </span>

      <span class="section-right">

        <div class="section-header">{"_Text:Video Details"}</div>
        <div class="section-content">

          {user var=$user username=$video.username}
          {if $user}
          <img src="{if $user.uri}{$user.uri}{else}{$g_config.template_uri}/images/avatar-150x120.png{/if}" style="max-width: 46px; max-height: 46px; border: 1px solid black; display: inline-block; margin-right: 8px;" />
          {/if}
          <span style="display: inline-block; vertical-align: top;">
            {if $user}<a href="{$g_config.base_uri}/profile/{$video.username}/">{$video.username}</a><br />{/if}
            {$video.date_added|t_datetime}
          </span>

          <div style="padding: 8px 4px;">
            {$video.description}
          </div>

          <div style="margin-bottom: 4px;">
            <span style="display: inline-block; width: 5em; text-align: right; font-weight: bold;">{"_Label:Category"}:</span>
            <span style="display: inline-block;"><a href="{$g_config.base_uri}/category/{$category.url_name}">{$category.name}</a></span>
          </div>

          <div style="margin-bottom: 8px;">
            <span style="display: inline-block; width: 5em; text-align: right; font-weight: bold; vertical-align: top;">{"_Label:Tags"}:</span>
            <span style="display: inline-block; width: 14em;">
              {assign var=$tags code=explode(' ', $video.tags)}
              {foreach var=$tag from=$tags}
              <a href="{$g_config.base_uri}/tag/{$tag}">{$tag}</a>
              {/foreach}
            </span>
          </div>

          {if !$video.is_private}
          <div class="small" style="border-top: 1px solid #afafaf; padding: 4px 2px;">
            <div style="margin-top: 3px;">
              <span style="display: inline-block; width: 3.5em; text-align: right;">{"_Text:URL"}</span>
              <input type="text" size="30" class="small" style="width: 200px;" value="{$g_config.base_url}/video/{$video.video_id}/{$video.title|t_urlify(5)}" /><br />
            </div>

            {*
            <!--<div style="margin-top: 3px;">
              <span style="display: inline-block; width: 3.5em; text-align: right;">{"_Text:Embed"}</span>
              <input type="text" size="30" class="small" style="width: 200px;" />
            </div>-->
            *}
          </div>
          {/if}
        </div>

        {if $user}
        <div class="section-header">{"_Text:More From"}: {$video.username}</div>
        <div class="section-content">

          {videos var=$videos username=$video.username not=$video.video_id amount=5 sort="`date_added` DESC"}

          {foreach var=$v from=$videos}
          <div class="video-brief">
            <span class="video-brief-thumb">
              <a href="{$g_config.base_uri}/video/{$v.video_id}/{$v.title|t_urlify(5)}">
                <img src="{if $v.thumbnail}{$v.thumbnail}{else}{$g_config.no_preview}{/if}" width="90" height="68" class="video-thumb" thumbs="{$v.num_thumbnails}" title="{$v.title}" />
              </a>
              <img src="{$g_config.template_uri}/images/{$v.total_avg_rating|t_nearesthalf}-stars-shadow.png" class="stars" />
            </span>
            <span class="video-brief-details" class="small" style="width: 58%;">
              <a href="{$g_config.base_uri}/video/{$v.video_id}/{$v.title|t_urlify(5)}" class="small">{$v.title|t_chop(30,'...')}</a>
              <div class="video-brief-facets smallest">
                <span>{$v.total_num_views|t_tostring} {"_Text:views"}</span>
              </div>
            </span>
          </div>
          {/foreach}

        </div>
        {/if}

        <div class="section-header">{"_Text:Related Videos"}</div>
        <div class="section-content">

          {videos var=$videos related=$video amount=5 sort="`date_added` DESC"}

          {foreach var=$v from=$videos}
          <div class="video-brief">
            <span class="video-brief-thumb">
              <a href="{$g_config.base_uri}/video/{$v.video_id}/{$v.title|t_urlify(5)}">
                <img src="{if $v.thumbnail}{$v.thumbnail}{else}{$g_config.no_preview}{/if}" width="90" height="68" class="video-thumb" thumbs="{$v.num_thumbnails}" title="{$v.title}" />
              </a>
              <img src="{$g_config.template_uri}/images/{$v.total_avg_rating|t_nearesthalf}-stars-shadow.png" class="stars" />
            </span>
            <span class="video-brief-details" class="small" style="width: 58%;">
              <a href="{$g_config.base_uri}/video/{$v.video_id}/{$v.title|t_urlify(5)}" class="small">{$v.title|t_chop(30,'...')}</a>
              <div class="video-brief-facets smallest">
                <span>{$v.total_num_views|t_tostring} {"_Text:views"}</span><br />
                <span><a href="{$g_config.base_uri}/profile/{$v.username}" class="normal">{$v.username}</a></span>
              </div>
            </span>
          </div>
          {/foreach}

        </div>

      </span>

    {/if}
    </div>

{template file="global-footer.tpl"}
