    <tr class="search-hilite search-result" id="<?php echo $item['blacklist_id'] ?>">
      <td class="selectable" style="width: 40px;" title="Select">
      </td>
      <td>
        <?php echo $item['value'] ?>
      </td>
      <td>
        <?php echo $item['type'] ?>
      </td>
      <td>
        <?php echo $item['reason'] ?>
      </td>
      <td valign="top" class="search-result-icons">
        <img src="images/edit-22x22.png" class="item-icon" title="Edit" meta="{t: 'dialog', r: 'tbxGenericShowEdit(blacklist-item)'}" />
        <img src="images/delete-22x22.png" class="item-icon" title="Delete" meta="{t: 'action', r: 'tbxGenericAction(blacklist-item,delete)'}" />
      </td>
    </tr>