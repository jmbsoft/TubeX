    <tr class="search-hilite search-result" id="<?php echo $item['reason_id'] ?>">
      <td class="selectable" style="width: 40px;" title="Select">
      </td>
      <td>
        <?php echo $item['type']; ?>
      </td>
      <td>
        <?php echo $item['short_name']; ?>
      </td>
      <td valign="top" class="search-result-icons">
        <img src="images/edit-22x22.png" class="item-icon" title="Edit" meta="{t: 'dialog', r: 'tbxGenericShowEdit(reason)'}" />
        <img src="images/delete-22x22.png" class="item-icon" title="Delete" meta="{t: 'action', r: 'tbxGenericAction(reason,delete)'}" />
      </td>
    </tr>