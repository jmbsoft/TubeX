    <tr class="search-hilite search-result" id="<?php echo $item['field_id'] ?>">
      <td class="selectable" style="width: 40px;" title="Select">
      </td>
      <td>
        <?php echo $item['name']; ?>
      </td>
      <td>
        <?php echo $item['label']; ?>
      </td>
      <td>
        <?php echo $item['type']; ?>
      </td>
      <td class="text-center">
        <img src="images/<?php echo ($item['on_submit'] ? 'approve' : 'reject'); ?>-22x22.png" />
      </td>
      <td class="text-center">
        <img src="images/<?php echo ($item['on_edit'] ? 'approve' : 'reject'); ?>-22x22.png" />
      </td>
      <td valign="top" class="search-result-icons">
        <img src="images/edit-22x22.png" class="item-icon" title="Edit" meta="{t: 'dialog', r: 'tbxGenericShowEdit(user-custom-field)'}" />
        <img src="images/delete-22x22.png" class="item-icon" title="Delete" meta="{t: 'action', r: 'tbxGenericAction(user-custom-field,delete)'}" />
      </td>
    </tr>