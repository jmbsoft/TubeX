{template file="global-header.tpl" title="_Text:Update Your Profile"}

{if !isset($g_form)}
{user var=$g_form username=$g_username}
{/if}

<script language="JavaScript" type="text/javascript">
$(function()
{
    $('input[name="gender"][value="{$g_form.gender}"]').attr('checked', 'checked');
    $('select[name="relationship"] option[value="{$g_form.relationship}"]').attr('selected', 'selected');
});
</script>

    <div class="main-content" style="margin-top: 30px;">
      <span class="section-left">

        <div class="section-header">{"_Text:Update Your Profile"}</div>
        <div class="section-content">

          {if $g_success}
          <div class="message-notice">
            {"_Text:Profile updated"}
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

          <form method="post" action="{$g_config.base_uri}/user.php?r=edit">

            <div class="field">
              <span class="label wider">{"_Label:New Password"}:</span>
              <span class="input-container">
                <input type="password" name="new_password" size="20" />

                <b style="margin-left: 20px;">{"_Label:Confirm Password"}:</b>
                <input type="password" name="confirm_password" size="20" />
                <br />
                <span class="smallest">
                  {"_Text:New password"}<br />
                  {"_Text:Password restriction"}
                </span>
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:E-mail"}:</span>
              <span class="input-container">
                <input type="text" name="email" size="40" value="{$g_form.email}" />
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Name"}:</span>
              <span class="input-container">
                <input type="text" name="name" size="40" value="{$g_form.name}" />
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Gender"}:</span>
              <span class="input-container">
                <input type="radio" name="gender" value="Male" id="gender-male" /> <label for="gender-male">{"_Text:Male"}</label>
                <input type="radio" name="gender" value="Female" id="gender-female" /> <label for="gender-female">{"_Text:Female"}</label>
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Relationship"}:</span>
              <span class="input-container">
                <select name="relationship">
                  <option value="Single">{"_Text:Single"}</option>
                  <option value="Taken">{"_Text:Taken"}</option>
                  <option value="Open">{"_Text:Open"}</option>
                </select>
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Hometown"}:</span>
              <span class="input-container">
                <input type="text" name="hometown" size="40" value="{$g_form.hometown}" />
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Current City"}:</span>
              <span class="input-container">
                <input type="text" name="current_city" size="40" value="{$g_form.current_city}" />
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Postal Code"}:</span>
              <span class="input-container">
                <input type="text" name="postal_code" size="12" value="{$g_form.postal_code}" />
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Country"}:</span>
              <span class="input-container">
                <input type="text" name="current_country" size="40" value="{$g_form.current_country}" />
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Website URL"}:</span>
              <span class="input-container">
                <input type="text" name="website_url" size="40" value="{$g_form.website_url}" />
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:About Me"}:</span>
              <span class="input-container">
                <textarea name="about" rows="4" cols="50">{$g_form.about}</textarea>
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Occupations"}:</span>
              <span class="input-container">
                <textarea name="occupations" rows="4" cols="50">{$g_form.occupations}</textarea>
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Companies"}:</span>
              <span class="input-container">
                <textarea name="companies" rows="4" cols="50">{$g_form.companies}</textarea>
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Schools"}:</span>
              <span class="input-container">
                <textarea name="schools" rows="4" cols="50">{$g_form.schools}</textarea>
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Hobbies"}:</span>
              <span class="input-container">
                <textarea name="hobbies" rows="4" cols="50">{$g_form.hobbies}</textarea>
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Movies"}:</span>
              <span class="input-container">
                <textarea name="movies" rows="4" cols="50">{$g_form.movies}</textarea>
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Music"}:</span>
              <span class="input-container">
                <textarea name="music" rows="4" cols="50">{$g_form.music}</textarea>
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Books"}:</span>
              <span class="input-container">
                <textarea name="books" rows="4" cols="50">{$g_form.books}</textarea>
              </span>
            </div>

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
                <input type="submit" value="{"_Button:Update Profile"}" />
              </span>
            </div>

            <input type="hidden" name="r" value="edit-submit" />
          </form>

        </div>

      </span>

      <span class="section-right">

        {template file="user-menu.tpl"}

      </span>
    </div>

{template file="global-footer.tpl"}