{template file="global-header.tpl" title="_Text:Account Data Submitted"}

    <div class="main-content page-content">
      <span class="section-whole">

        <div class="section-content">
          <div class="header">
            <span class="header-left"></span>
            <span class="header-right"></span>
            <span class="header-text">{"_Text:Account Data Submitted"}</span>
          </div>

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