{template file="global-header.tpl"}

    <div class="main-content" style="margin-top: 30px;">
      <span class="section-left">

        <div class="section-header">{"_Text:Categories"}</div>
        <div class="section-content">

          {categories var=$categories}
          {assign var=$per_column code=ceil(count($categories)/4)}

          <table width="100%">
          <tr>
          <td valign="top" width="25%">
          {foreach var=$category from=$categories counter=$counter}
          <a href="{$g_config.base_uri}/category/{$category.url_name}/">{$category.name}</a> <span class="small" style="color: #999;">({$category.num_videos})</span><br />
          {insert location=$per_column counter=$counter}</td><td valign="top">{/insert}
          {/foreach}
          </td>
          </tr>
          </table>

        </div>
      </span>

      <span class="section-right">
        {template file="global-tags.tpl"}
      </span>
    </div>

{template file="global-footer.tpl"}