    <tr class="search-hilite search-result" id="<?php echo $item['user_level_id'] ?>">
      <td class="selectable" style="width: 40px;" title="Select">
      </td>
      <td>
        <?php echo $item['name']; ?>
      </td>
      <td>
        <?php echo NumberFormatInteger($item['daily_view_limit']); ?>
      </td>
      <td>
        <?php echo Format::BytesToString($item['daily_bandwidth_limit']); ?>
      </td>
      <td class="text-center">
        <img src="images/<?php echo ($item['is_guest'] ? 'approve' : 'reject'); ?>-22x22.png" />
      </td>
      <td class="text-center">
        <img src="images/<?php echo ($item['is_default'] ? 'approve' : 'reject'); ?>-22x22.png" />
      </td>
      <td valign="top" class="search-result-icons">
        <img src="images/edit-22x22.png" class="item-icon" title="Edit" meta="{t: 'dialog', r: 'tbxGenericShowEdit(user-level)'}" />
        <img src="images/delete-22x22.png" class="item-icon" title="Delete" meta="{t: 'action', r: 'tbxGenericAction(user-level,delete)'}" />
      </td>
    </tr>