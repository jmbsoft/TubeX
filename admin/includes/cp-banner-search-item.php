    <tr class="search-hilite search-result" id="<?php echo $item['banner_id'] ?>">
      <td class="selectable" style="width: 40px;" title="Select">
      </td>
      <td>
        <?php if( stripos($original['banner_html'], '<script') === false ): ?>
        <span class="banner-container">
          <?php echo $original['banner_html']; ?>
        </span>
        <?php else: ?>
        <iframe src="index.php?r=tbxBannerDisplay&id=<?php echo $item['banner_id']; ?>" class="banner-container" frameborder="0" border="0"></iframe>
        <?php endif; ?>
        <div style="margin-top: 10px;">
        <?php echo ResizeableColumn('Sponsor', empty($item['sponsor_id']) ? '-' : $sponsors[$item['sponsor_id']]['name']); ?>
        <?php echo ResizeableColumn('Zone', $item['zone']); ?>
        <?php echo ResizeableColumn('Tags', $item['tags']); ?>
        <?php echo ResizeableColumn('Displayed', $item['times_displayed'], true); ?>
        </div>
      </td>
      <td valign="top" class="search-result-icons">
        <img src="images/edit-22x22.png" class="item-icon" title="Edit" meta="{t: 'dialog', r: 'tbxGenericShowEdit(banner)'}" />
        <img src="images/delete-22x22.png" class="item-icon" title="Delete" meta="{t: 'action', r: 'tbxGenericAction(banner,delete)'}" />
      </td>
    </tr>