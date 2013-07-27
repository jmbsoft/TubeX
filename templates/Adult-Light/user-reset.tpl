{template file="global-header.tpl" title="_Text:Lost Password Utility"}

    <div class="main-content page-content">

      <div class="section-content">
        <div class="header">
          <span class="header-left"></span>
          <span class="header-right"></span>
          <span class="header-text">{"_Text:Lost Password Utility"}</span>
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

        <div style="margin: 10px;">
        {"_Text:Enter e-mail to get new password"}
        </div>

        <div style="text-align: center; margin-bottom: 10px;">
          <form action="{$g_config.base_uri}/user.php?r=reset" method="post">
            <b>{"_Label:E-mail"}:</b> <input type="text" size="40" name="email" value="{$g_form.email}" />
            <input type="submit" value="Submit" />
            <input type="hidden" name="r" value="reset-submit" />
          </form>
        </div>

      </div>

    </div>

{template file="global-footer.tpl"}