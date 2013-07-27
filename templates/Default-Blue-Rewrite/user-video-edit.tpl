{template file="global-header.tpl" title="_Text:Update A Video"}

{if !isset($g_form)}
{user var=$g_form username=$g_username}
{/if}

<script language="JavaScript" type="text/javascript">
$(function()
{
    // Thumbnail selection
    $('.thumb-select-container img')
    .mousedown(function()
              {
                  $(this).addClass('thumb-selected').siblings().removeClass('thumb-selected');
                  $('input[name="display_thumbnail"]').val($(this).attr('thumbid'));
              })
    .bind('selectstart', function() { return false; });

    // Character counting
    $('#title').bind('keyup', function() { $('#title-length').html($(this).val().length); }).trigger('keyup');
    $('#description').bind('keyup', function() { $('#description-length').html($(this).val().length); }).trigger('keyup');

    // Word counting
    $('#tags')
    .bind('keyup', function()
                   {
                       var string = $(this).val().replace(/^ +| +$/, '').replace(/ +/, ' ');
                       var matches = string.split(/ /);
                       var count = 0;

                       for( var i = 0; i < matches.length; i++ )
                       {
                           if( matches[i].length >= {$g_tag_min_length} )
                           {
                               count++;
                           }
                       }

                       $('#tags-length').html(count);
                   })
    .trigger('keyup');

    // Select date of birth fields
    $('select[name="recorded_day"] option[value="{$g_form.recorded_day}"]').attr('selected', 'selected');
    $('select[name="recorded_month"] option[value="{$g_form.recorded_month}"]').attr('selected', 'selected');
    $('select[name="recorded_year"] option[value="{$g_form.recorded_year}"]').attr('selected', 'selected');
});
</script>

    <div class="main-content" style="margin-top: 30px;">
      <span class="section-left">

        <div class="section-header">{"_Text:Update A Video"}</div>
        <div class="section-content">

          {if !$g_form}
          <div class="message-error">
            {"_Text:No such video"}
          </div>
          {else}

          {if $g_success}
          <div class="message-notice">
            {"_Text:Video updated"}
          </div>
          {/if}

          {if !empty($g_errors)}
          <div class="message-error">
            {"_Text:Please Fix"}
            <ul>
            {foreach var=$error from=$g_errors}
            <li>{$error}</li>
            {/foreach}
            </ul>
          </div>
          {/if}

          <form method="post" action="{$g_config.base_uri}/user/video-edit/">

            <div class="field">
              <span class="label wider">{"_Label:Title"}:</span>
              <span class="input-container" style="width: 50%">
                <input type="text" name="title" id="title" size="60" value="{$g_form.title}" /><br />
                <span class="smallest">
                  {"_Text:Title restriction",$g_config.title_min_length,$g_config.title_max_length}<br />
                  {"_Text:Current length"}: <span id="title-length"></span>
                </span>
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Description"}:</span>
              <span class="input-container" style="width: 50%">
                <textarea name="description" id="description" rows="5" cols="50">{$g_form.description}</textarea><br />
                <span class="smallest">
                  {"_Text:Description restriction",$g_config.description_min_length,$g_config.description_max_length}<br />
                  {"_Text:Current length"}: <span id="description-length"></span>
                </span>
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Tags"}:</span>
              <span class="input-container" style="width: 50%">
                <input type="text" name="tags" id="tags" size="60" value="{$g_form.tags}" /><br />
                <span class="smallest">
                  {"_Text:Tags restriction",$g_config.tags_min,$g_config.tags_max,$g_tag_min_length}<br />
                  {"_Text:Current tags"}: <span id="tags-length"></span>
                </span>
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Category"}:</span>
              <span class="input-container">
                {categories var=$categories}
                <select name="category_id">
                  {options from=$categories key=category_id value=name selected=$g_form.category_id}
                </select>
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Date Recorded"}:</span>
              <span class="input-container">
                <select name="recorded_month">
                  <option value="0">---</option>
                  <option value="01">{"_Text:January"}</option>
                  <option value="02">{"_Text:February"}</option>
                  <option value="03">{"_Text:March"}</option>
                  <option value="04">{"_Text:April"}</option>
                  <option value="05">{"_Text:May"}</option>
                  <option value="06">{"_Text:June"}</option>
                  <option value="07">{"_Text:July"}</option>
                  <option value="08">{"_Text:August"}</option>
                  <option value="09">{"_Text:September"}</option>
                  <option value="10">{"_Text:October"}</option>
                  <option value="11">{"_Text:November"}</option>
                  <option value="12">{"_Text:December"}</option>
                </select>

                <select name="recorded_day">
                  <option value="0">---</option>
                  <option value="01">1</option>
                  <option value="02">2</option>
                  <option value="03">3</option>
                  <option value="04">4</option>
                  <option value="05">5</option>
                  <option value="06">6</option>
                  <option value="07">7</option>
                  <option value="08">8</option>
                  <option value="09">9</option>
                  <option value="10">10</option>
                  <option value="11">11</option>
                  <option value="12">12</option>
                  <option value="13">13</option>
                  <option value="14">14</option>
                  <option value="15">15</option>
                  <option value="16">16</option>
                  <option value="17">17</option>
                  <option value="18">18</option>
                  <option value="19">19</option>
                  <option value="20">20</option>
                  <option value="21">21</option>
                  <option value="22">22</option>
                  <option value="23">23</option>
                  <option value="24">24</option>
                  <option value="25">25</option>
                  <option value="26">26</option>
                  <option value="27">27</option>
                  <option value="28">28</option>
                  <option value="29">29</option>
                  <option value="30">30</option>
                  <option value="31">31</option>
                </select>

                <select name="recorded_year">
                  <option value="0">---</option>
                  {range start=$g_year end=1900 counter=$year}
                  <option value="{$year}">{$year}</option>
                  {/range}
                </select>
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Location Recorded"}:</span>
              <span class="input-container">
                <input type="text" name="location_recorded" size="60" value="{$g_form.location_recorded}" />
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Display Thumbnail"}:</span>
              <span class="input-container" style="width: 70%;">
                {if empty($g_thumbs)}
                <div class="message-warning center">No thumbnails exist for this video</div>
                {else}
                <span class="smallest">{"_Text:Select thumbnail"}</span>
                <div class="thumb-select-container">
                {foreach var=$t from=$g_thumbs}
                  <img src="{$t.thumbnail}" border="0" thumbid="{$t.thumbnail_id}" class="click{if $t.thumbnail_id == $g_form.display_thumbnail} thumb-selected{/if}" />
                {/foreach}
                <input type="hidden" name="display_thumbnail" value="{$g_form.display_thumbnail}" />
                </div>
                {/if}
              </span>
          </div>

            {if $g_config.flag_upload_allow_private}
            <div class="field">
              <span class="label wider"></span>
              <span class="input-container">
                <input type="checkbox" name="is_private" value="1"{if $g_form.is_private} checked="checked"{/if} />
                {"_Label:Make Private"}
              </span>
            </div>
            {/if}

            <div class="field">
              <span class="label wider"></span>
              <span class="input-container">
                <input type="checkbox" name="allow_comments" value="1"{if $g_form.allow_comments} checked="checked"{/if} />
                {"_Label:Allow Comments"}
              </span>
            </div>

            <div class="field">
              <span class="label wider"></span>
              <span class="input-container">
                <input type="checkbox" name="allow_ratings" value="1"{if $g_form.allow_ratings} checked="checked"{/if} />
                {"_Label:Allow Rating"}
              </span>
            </div>

            {*
            <!--<div class="field">
              <span class="label wider"></span>
              <span class="input-container">
                <input type="checkbox" name="allow_embedding" value="1"{if $g_form.allow_embedding} checked="checked"{/if} />
                {"_Label:Allow Embedding"}
              </span>
            </div>-->
            *}

            {foreach var=$field from=$g_custom_fields}
            <div class="field">
              {if $field.type == 'Checkbox'}
              <span class="label wider"></span>
              <span class="input-container">{$field|t_formfield|rawhtml} {$field.label}</span>
              {else}
              <span class="label wider">{$field.label}:</span>
              <span class="input-container">{$field|t_formfield|rawhtml}</span>
              {/if}
            </div>
            {/foreach}


            <div class="field">
              <span class="label wider"></span>
              <span class="input-container">
                <input type="submit" value="{"_Button:Update Video"}" />
              </span>
            </div>

            <input type="hidden" name="r" value="video-edit-submit" />
            <input type="hidden" name="video_id" value="{$g_form.video_id}" />
          </form>

          {/if} {* END if !$g_form *}

        </div>

      </span>

      <span class="section-right">

        {template file="user-menu.tpl"}

      </span>
    </div>

{template file="global-footer.tpl"}