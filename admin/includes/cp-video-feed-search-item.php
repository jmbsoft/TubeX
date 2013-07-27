    <tr class="search-hilite search-result" id="<?php echo $item['feed_id'] ?>">
      <td class="selectable" style="width: 40px;" title="Select">
      </td>
      <td>
        [<?php echo $item['feed_id'] ?>] <?php echo $item['name']; ?>
      </td>
      <td>
        <?php echo $item['type']; ?>
      </td>
      <td class="date-last-read">
        <?php echo !empty($item['date_last_read']) ? date(DATETIME_FRIENDLY, strtotime($item['date_last_read'])) : '-'; ?>
      </td>
      <td valign="top" class="search-result-icons">
        <img src="images/feed-read-22x22.png" class="item-icon" title="Read" meta="{t: 'action', confirm: false, r: 'tbxFeedRead()'}" />
        <img src="images/approve-22x22.png" class="item-icon" title="Test" meta="{t: 'action', confirm: false, r: 'tbxFeedTest()'}" />
        <img src="images/edit-22x22.png" class="item-icon" title="Edit" meta="{t: 'dialog', r: 'tbxGenericShowEdit(video-feed)'}" />
        <img src="images/delete-22x22.png" class="item-icon" title="Delete" meta="{t: 'action', r: 'tbxGenericAction(video-feed,delete)'}" />
      </td>
    </tr>