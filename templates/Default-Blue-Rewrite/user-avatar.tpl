{template file="global-header.tpl"}

    <div class="main-content" style="margin-top: 30px;">
      <span class="section-left">

        {user var=$user username=$g_username}

        <div class="section-header">{"_Text:Modify Your Avatar"}</div>
        <div class="section-content">

          {if $g_success}
          <div class="message-notice">
            {"_Text:Avatar updated"}
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

          <form method="post" action="{$g_config.base_uri}/user/avatar/" enctype="multipart/form-data">

            <div class="field">
              <span class="label wider">{"_Label:Current Avatar"}:</span>
              <span class="input-container">
                <img src="{if $user.uri}{$user.uri}{else}{$g_config.template_uri}/images/avatar-150x120.png{/if}" style="max-width: 100px; max-height: 100px;" />
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:New Avatar"}:</span>
              <span class="input-container" style="width: 50%">
                <input type="file" name="avatar_file" size="50" /><br />
                <span class="smallest">{"_Text:Avatar restriction",$g_config.avatar_dimensions,$g_config.avatar_filesize,$g_config.avatar_extensions}</span>
              </span>
            </div>

            <div class="field">
              <span class="label wider"></span>
              <span class="input-container">
                <input type="submit" value="{"_Button:Update Avatar"}" />
              </span>
            </div>

            <input type="hidden" name="r" value="avatar-submit" />

          </form>

        </div>

      </span>

      <span class="section-right">

        {template file="user-menu.tpl"}

      </span>
    </div>

{template file="global-footer.tpl"}