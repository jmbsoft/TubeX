{template file="global-header.tpl" title="_Text:Account Data Submitted"}

    <div class="main-content" style="margin-top: 30px;">
      <span class="section-whole">

        <div class="section-header">{"_Text:Account Data Submitted"}</div>
        <div class="section-content">

        {"_Text:Account data submitted successfully"}
        {if $g_user.status == 'Submitted'}
          {"_Text:Account submitted waiting confirmation"}
        {elseif $g_user.status == 'Pending'}
          {"_Text:Account submitted pending"}
        {else}
          {"_Text:Account submitted active",$g_config.base_uri}
        {/if}

        </div>

      </span>
    </div>

{template file="global-footer.tpl"}