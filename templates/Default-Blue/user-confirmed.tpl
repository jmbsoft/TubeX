{template file="global-header.tpl" title="_Text:Account Confirmed"}

    <div class="main-content" style="margin-top: 30px;">
      <span class="section-whole">

        <div class="section-header">{"_Text:Account Confirmed"}</div>
        <div class="section-content">

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

      </span>
    </div>

{template file="global-footer.tpl"}