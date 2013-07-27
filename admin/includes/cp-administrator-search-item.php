    <tr class="search-hilite search-result" id="<?php echo $item['username'] ?>">
      <td class="selectable" style="width: 40px;" title="Select">
      </td>
      <td>
        <?php echo $item['username'] ?>
      </td>
      <td>
        <?php echo $item['name'] ?>
      </td>
      <td>
        <?php echo $item['email'] ?>
      </td>
      <td>
        <?php echo $item['type'] ?>
      </td>
      <td valign="top" class="search-result-icons">
        <img src="images/edit-22x22.png" class="item-icon" title="Edit" meta="{t: 'dialog', r: 'tbxGenericShowEdit(administrator)'}" />
        <img src="images/email-22x22.png" class="item-icon" title="E-mail" meta="{t: 'dialog', r: 'tbxGenericShowEmail(administrator)'}" />
        <img src="images/delete-22x22.png" class="item-icon" title="Delete" meta="{t: 'action', r: 'tbxGenericAction(administrator,delete)'}" />
      </td>
    </tr>