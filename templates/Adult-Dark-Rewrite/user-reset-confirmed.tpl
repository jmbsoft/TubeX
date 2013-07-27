{template file="global-header.tpl" title="_Text:Lost Password Utility"}

    <div class="main-content page-content">

      <div class="section-content">
        <div class="header">
          <span class="header-left"></span>
          <span class="header-right"></span>
          <span class="header-text">{"_Text:Lost Password Utility"}</span>
        </div>

        {if isset($g_user)}
        <div style="margin: 10px;">
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