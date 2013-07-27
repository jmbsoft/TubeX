{template file="global-header.tpl" title="_Text:Account Confirmed"}

    <div class="main-content">
      <div class="section-content page-content">
        <div class="header">
          <span class="header-left"></span>
          <span class="header-right"></span>
          <span class="header-text">{"_Text:Account Confirmed"}</span>
        </div>

        {if $g_errors}
        <div class="message-error">
          <ul>
          {foreach var=$error from=$g_errors}
          <li>{$error}</li>
          {/foreach}
          </ul>
        </div>
        {else}

        {"_Text:Account has been confirmed"}
        {if $g_user.status == 'Pending'}
          {"_Text:Account submitted pending"}
        {else}
          {"_Text:Account submitted active",$g_config.base_uri}
        {/if}

        {/if}

      </div>
    </div>

{template file="global-footer.tpl"}