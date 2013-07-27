    <tr class="search-hilite search-result" id="<?php echo $item['term_id'] ?>">
      <td class="selectable" style="width: 40px;" title="Select">
      </td>
      <td>
        <?php echo $item['term']; ?>
      </td>
      <td>
        <?php echo NumberFormatInteger($item['frequency']); ?>
      </td>
      <td valign="top" class="search-result-icons">
        <img src="images/edit-22x22.png" class="item-icon" title="Edit" meta="{t: 'dialog', r: 'tbxGenericShowEdit(search-term)'}" />

        <img src="images/delete-22x22.png" class="item-icon" title="Delete" meta="{t: 'action', r: 'tbxGenericAction(search-term,delete)'}" />
      </td>
    </tr>