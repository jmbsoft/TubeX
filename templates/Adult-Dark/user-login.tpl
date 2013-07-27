{template file="global-header.tpl"}

    <div class="main-content">

      <div class="section-content page-content">
        <div class="header">
          <span class="header-left"></span>
          <span class="header-right"></span>
          <span class="header-text">{"_Text:Account Login"}</span>
        </div>


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

        <span style="display: inline-block; width: 45%; margin: 10px;">
          <span style="font-weight: bold;">
            {"_Text:Login Now",$g_config.site_name}
          </span>

          <ul>
            <li>{"_Text:Upload and share your own videos with the world"}</li>
            <li>{"_Text:Comment on and rate your favorite videos"}</li>
            <li>{"_Text:Build playlists of favorites to watch later"}</li>
          </ul>

          {"_Text:No Account"} <a href="{$g_config.base_uri}/user.php?r=register">{"_Text:Signup Now"}</a>
        </span>

        <span style="display: inline-block; vertical-align: top; margin: 10px;">
          <form action="{$g_config.base_uri}/user.php?r=login" method="post">

            <div class="field">
              <span class="label">{"_Label:Username"}:</span> <input type="text" name="username" size="20" value="{$g_form.username}" />
            </div>

            <div class="field">
              <span class="label">{"_Label:Password"}:</span> <input type="password" name="password" size="20" /> <a href="{$g_config.base_uri}/user.php?r=reset" class="fs80">Lost Password?</a>
            </div>

            <div class="field">
              <span class="label"></span>
              <input type="checkbox" name="remember" value="1"{if $g_form.remember} checked="checked"{/if} /> {"_Label:Remember Me"}
            </div>

            <div class="field">
              <span class="label"></span> <input type="submit" value="{"_Button:Login"}" />
            </div>

            <input type="hidden" name="referrer" value="{nocache}{if !stristr($g_referrer, '/user/')}{$g_referrer}{/if}{/nocache}" />
            <input type="hidden" name="r" value="login-submit" />
          </form>
        </span>

      </div>

    </div>

{template file="global-footer.tpl"}