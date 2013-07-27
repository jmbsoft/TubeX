    <?php
    $item['date_commented'] = date(DATETIME_FRIENDLY, strtotime($item['date_commented']));
    $video = $DB->Row('SELECT * FROM `tbx_video` WHERE `video_id`=?', array($item['video_id']));
    $video = String::HtmlSpecialChars($video);
    ?>
    <tr class="search-hilite search-result" id="<?php echo $item['comment_id'] ?>">
      <td class="selectable" style="width: 40px;" title="Select">
      </td>
      <td valign="top">
        <div style="margin-bottom: 5px;">
          <b style="display: inline-block; width: 6em; text-align: right;">Username:</b>
          <a href="index.php?r=tbxGenericShowSearch(user)&pds=user&username=<?php echo urlencode($original['username']); ?>"><?php echo $item['username']; ?></a>
        </div>
        <div style="margin-bottom: 5px;">
          <b style="display: inline-block; width: 6em; text-align: right;">Date:</b>
          <?php echo $item['date_commented']; ?>
        </div>
        <div style="margin-bottom: 5px;">
          <b style="display: inline-block; width: 6em; text-align: right;">Status:</b>
          <?php echo $item['status']; ?>
        </div>
        <div>
          <b style="display: inline-block; width: 6em; text-align: right; vertical-align: top;">Video:</b>
          <span style="display: inline-block; max-height: 4.2em; overflow: auto; width: 80%;">
            <a href="index.php?r=tbxGenericShowSearch(video)&pds=video&video_id=<?php echo urlencode($original['video_id']); ?>"><?php echo $video['title']; ?></a>
          </span>
        </div>
        <div>
          <b style="display: inline-block; width: 6em; text-align: right; vertical-align: top;">Comment:</b>
          <span style="display: inline-block; max-height: 4.2em; overflow: auto; width: 80%;"><?php echo nl2br($item['comment']); ?></span>
        </div>
      </td>
      <td valign="top" class="search-result-icons">
        <img src="images/video-22x22.png" class="item-icon" title="Videos" meta="{t: 'link', u: 'index.php?r=tbxGenericShowSearch(video)&pds=video&video_id=<?php echo urlencode($original['video_id']); ?>'}" />
        <img src="images/edit-22x22.png" class="item-icon" title="Edit" meta="{t: 'dialog', r: 'tbxGenericShowEdit(video-comment)'}" />
        <img src="images/email-22x22.png" class="item-icon" title="E-mail" meta="{t: 'dialog', r: 'tbxGenericShowEmail(video-comment)'}" />
        <img src="images/delete-22x22.png" class="item-icon" title="Delete" meta="{t: 'action', r: 'tbxGenericAction(video-comment,delete)'}" />

        <?php if( $item['status'] == STATUS_PENDING ): ?>
        <div class="process-pending-section">
          <img src="images/approve-22x22.png" class="item-icon" title="Approve" meta="{t: 'action', r: 'tbxGenericAction(video-comment,approve)'}" />
          <img src="images/reject-22x22.png" class="item-icon" title="Reject" meta="{t: 'action', r: 'tbxGenericAction(video-comment,reject)'}" />
        </div>
        <?php endif; ?>
      </td>
    </tr>