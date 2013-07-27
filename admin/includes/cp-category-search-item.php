    <tr class="search-hilite search-result" id="<?php echo $item['category_id']; ?>">
      <td class="selectable" style="width: 40px;" title="Select">
      </td>
      <td>
       <?php echo $item['name']; ?>
      </td>
      <td>
       <?php echo $item['url_name']; ?>
      </td>
      <td>
        <?php echo NumberFormatInteger($item['num_videos']); ?>
      </td>
      <td valign="top" class="search-result-icons">
        <img src="images/video-22x22.png" class="item-icon" title="Videos" meta="{t: 'link', u: 'index.php?r=tbxGenericShowSearch(video)&pds=category&name=<?php echo urlencode($original['name']); ?>'}" />
        <img src="images/edit-22x22.png" class="item-icon" title="Edit" meta="{t: 'dialog', r: 'tbxGenericShowEdit(category)'}" />
        <img src="images/delete-22x22.png" class="item-icon" title="Delete" meta="{t: 'action', r: 'tbxGenericAction(category,delete)'}" />
      </td>
    </tr>