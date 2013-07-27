    <tr class="search-hilite search-result" id="<?php echo $item['sponsor_id'] ?>">
      <td class="selectable" style="width: 40px;" title="Select">
      </td>
      <td>
        <?php echo $item['name']; ?>
      </td>
      <td>
        <?php echo ResizeableColumn('Main', $item['url']); ?>
        <?php echo ResizeableColumn('2257', $item['us2257_url']); ?>
      </td>
      <td>
        <?php echo NumberFormatInteger($item['videos']); ?>
      </td>
      <td>
        <?php
        $banners = $DB->QueryCount('SELECT COUNT(*) FROM `tbx_banner` WHERE `sponsor_id`=?', array($item['sponsor_id']));
        echo NumberFormatInteger($banners);
        ?>
      </td>
      <td valign="top" class="search-result-icons">
        <img src="images/video-22x22.png" class="item-icon" title="Videos" meta="{t: 'link', u: 'index.php?r=tbxGenericShowSearch(video)&pds=sponsor&name=<?php echo urlencode($original['name']); ?>'}" />
        <img src="images/edit-22x22.png" class="item-icon" title="Edit" meta="{t: 'dialog', r: 'tbxGenericShowEdit(sponsor)'}" />
        <img src="images/delete-22x22.png" class="item-icon" title="Delete" meta="{t: 'action', r: 'tbxGenericAction(sponsor,delete)'}" />
      </td>
    </tr>