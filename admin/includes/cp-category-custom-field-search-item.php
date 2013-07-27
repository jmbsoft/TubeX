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
      <td valign="top" class="search-result-icons">
        <img src="images/edit-22x22.png" class="item-icon" title="Edit" meta="{t: 'dialog', r: 'tbxGenericShowEdit(category-custom-field)'}" />
        <img src="images/delete-22x22.png" class="item-icon" title="Delete" meta="{t: 'action', r: 'tbxGenericAction(category-custom-field,delete)'}" />
      </td>
    </tr>