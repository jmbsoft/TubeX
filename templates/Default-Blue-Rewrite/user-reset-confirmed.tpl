{template file="global-header.tpl" title="_Text:Lost Password Utility"}

    <div class="main-content" style="margin-top: 30px;">

      <div class="section-header">{"_Text:Lost Password Utility"}</div>
      <div class="section-content">

        {if isset($g_user)}
        <div style="font-size: 115%; margin: 10px;">
        {"_Text:Password reset confirmed"}

        <br />
        <br />

        <b>{"_Label:Username"}:</b> {$g_user.username}<br />
        <b>{"_Label:Password"}:</b> {$g_password}
        </div>
        {else}
        <div class="message-error">
          <ul>
          {foreach var=$error from=$g_errors}
          <li>{$error}</li>
          {/foreach}
          </ul>
        </div>
        {/if}
      </div>

    </div>

{template file="global-footer.tpl"}