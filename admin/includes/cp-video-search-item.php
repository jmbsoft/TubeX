    <?php
    // Setup data
    $item['category_id'] = isset($categories[$item['category_id']]) ? $categories[$item['category_id']]['name'] : '';
    $item['sponsor_id'] = isset($sponsors[$item['sponsor_id']]) ? $sponsors[$item['sponsor_id']]['name'] : '-';
    $item['date_recorded'] = !empty($item['date_recorded']) ? date(DATE_FRIENDLY, strtotime($item['date_recorded'])) : '';
    $item['date_last_featured'] = !empty($item['date_last_featured']) ? date(DATETIME_FRIENDLY, strtotime($item['date_last_featured'])) : '';
    $item['date_added'] = date(DATETIME_FRIENDLY, strtotime($item['date_added']));

    if( $item['is_private'] )
    {
        $item['private_id'] = $DB->QuerySingleColumn('SELECT `private_id` FROM `tbx_video_private` WHERE `video_id`=?', array($item['video_id']));
    }

	if( Config::Get('flag_mod_rewrite') )
    {
        $watch_url = $item['is_private'] ?
                     Config::Get('base_url') . '/private/' . $item['video_id'] . '/' . $item['private_id'] . '/' :
                     Config::Get('base_url') . '/video/' . $item['video_id'] . '/' . URLify($original['title'], 7);
    }
    else
    {
        $watch_url = $item['is_private'] ?
                     Config::Get('base_url') . '/index.php?r=private&amp;id=' . $item['video_id'] . '&amp;pid=' . $item['private_id'] :
                     Config::Get('base_url') . '/index.php?r=video&amp;id=' . $item['video_id'];
    }

    //$watch_url = $item['is_private'] ?
    //             Config::Get('base_url') . '/index.php?r=private&amp;id=' . $item['video_id'] . '&amp;pid=' . $item['private_id'] :
    //             Config::Get('base_url') . '/index.php?r=video&amp;id=' . $item['video_id'];

    $preview_src = 'images/no-preview-120x90.png';
    if( !empty($item['display_thumbnail']) )
    {
        $thumb = $DB->Row('SELECT * FROM `tbx_video_thumbnail` WHERE `thumbnail_id`=?', array($item['display_thumbnail']));
        $preview_src = $thumb['thumbnail'];
    }
    ?>
    <tr class="search-hilite search-result <?php echo $item['status']; ?>" id="<?php echo $item['video_id'] ?>">
      <td class="selectable" style="width: 40px;" title="Select">
      </td>
      <td valign="top">
        <div class="relative">
          <div>
            <img src="<?php echo $preview_src; ?>" class="preview-thumb" videoid="<?php echo $item['video_id'] ?>" />
            <span style="color: #999;">#<?php echo $item['video_id']; ?></span>
            <?php if( $item['status'] == STATUS_ACTIVE ): ?>
            <a href="<?php echo $watch_url; ?>" class="video-title-link" target="_blank"><?php echo $item['title']; ?></a><br/>
            <?php else: ?>
            <span class="video-title-link" style="color: #000;"><?php echo $item['title']; ?></span><br />
            <?php endif; ?>
            <div class="video-description"><?php echo $item['description']; ?></div>
          </div>
          <div style="clear: both;">
          <?php if( $_REQUEST['detailed'] ): ?>
          <?php echo ResizeableColumn('Username', $item['username'], false, null, null, 'index.php?r=tbxGenericShowSearch(user)&pds=user&username=' . urlencode($original['username'])); ?>
          <?php echo ResizeableColumn('Tags', $item['tags']); ?>
          <?php echo ResizeableColumn('Date Added', $item['date_added']); ?>
          <?php echo ResizeableColumn('Date Recorded', $item['date_recorded']); ?>
          <?php echo ResizeableColumn('Last Featured', $item['date_last_featured']); ?>
          <?php echo ResizeableColumn('Location Recorded', $item['location_recorded']); ?>
          <?php echo ResizeableColumn('Status', $item['status']); ?>
          <?php echo ResizeableColumn('Duration', Format::SecondsToDuration($item['duration'])); ?>
          <?php echo ResizeableColumn('Category', $item['category_id']); ?>
          <?php echo ResizeableColumn('Sponsor', $item['sponsor_id']); ?>
          <?php echo ResizeableColumn('Private', $item['is_private'], false, null, Form_Field::CHECKBOX); ?>
          <?php echo ResizeableColumn('Comments Allowed', $item['allow_comments']); ?>
          <?php echo ResizeableColumn('Allow ratings', $item['allow_ratings'], false, null, Form_Field::CHECKBOX); ?>
          <?php echo ResizeableColumn('Allow embedding', $item['allow_embedding'], false, null, Form_Field::CHECKBOX); ?>

          <?php echo ResizeableColumn('Views',
                               array($item['today_num_views'],
                                     $item['week_num_views'],
                                     $item['month_num_views'],
                                     $item['total_num_views']),
                               true,
                               ' / '); ?>

          <?php echo ResizeableColumn('Flagged',
                               array($item['today_num_flagged'],
                                     $item['week_num_flagged'],
                                     $item['month_num_flagged'],
                                     $item['total_num_flagged']),
                               true,
                               ' / '); ?>

          <?php echo ResizeableColumn('Featured',
                               array($item['today_num_featured'],
                                     $item['week_num_featured'],
                                     $item['month_num_featured'],
                                     $item['total_num_featured']),
                               true,
                               ' / '); ?>

          <?php echo ResizeableColumn('Comments',
                               array($item['today_num_comments'],
                                     $item['week_num_comments'],
                                     $item['month_num_comments'],
                                     $item['total_num_comments']),
                               true,
                               ' / '); ?>

          <?php echo ResizeableColumn('Favorited',
                               array($item['today_num_favorited'],
                                     $item['week_num_favorited'],
                                     $item['month_num_favorited'],
                                     $item['total_num_favorited']),
                               true,
                               ' / '); ?>

          <?php echo ResizeableColumn('Ratings',
                               array($item['today_num_ratings'],
                                     $item['week_num_ratings'],
                                     $item['month_num_ratings'],
                                     $item['total_num_ratings']),
                               true,
                               ' / '); ?>

          <?php echo ResizeableColumn('Average Rating',
                               array($item['today_avg_rating'],
                                     $item['week_avg_rating'],
                                     $item['month_avg_rating'],
                                     $item['total_avg_rating']),
                               true,
                               ' / '); ?>

          <?php
          foreach( $custom_schema as $field )
          {
              echo ResizeableColumn($field['label'], $item[$field['name']], false, null, $field['type']);
          }
          ?>
          <?php
          endif;
          ?>
            <?php if( $item['conversion_failed'] ):  ?>
            <div class="message-error">
              Conversion of this video failed! <img src="images/log-16x16.png" style="vertical-align: middle; margin-left: 20px;" title="View Log" class="view-log clickable" />
            </div>
            <?php endif; ?>
          </div>
        </div>
      </td>
      <td valign="top" class="search-result-icons">
        <img src="images/enable-22x22.png" class="item-icon" title="Enable" <?php if( $item['status'] != STATUS_DISABLED ): ?> style="display: none;"<?php endif; ?> meta="{t: 'action', r: 'tbxGenericAction(video,enable)'}" />
        <img src="images/disable-22x22.png" class="item-icon" title="Disable" <?php if( $item['status'] != STATUS_ACTIVE ): ?> style="display: none;"<?php endif; ?> meta="{t: 'action', r: 'tbxGenericAction(video,disable)'}" />
        <img src="images/comments-22x22.png" class="item-icon" title="Comments" meta="{t: 'link', u: 'index.php?r=tbxGenericShowSearch(video-comment)&pds=video&video_id=<?php echo urlencode($original['video_id']); ?>'}" />
        <!--<img src="images/blacklist-22x22.png" class="item-icon" title="Blacklist" meta="{t: 'action', r: 'tbxGenericAction(user,blacklist)'}" />-->
        <img src="images/edit-22x22.png" class="item-icon" title="Edit" meta="{t: 'dialog', r: 'tbxGenericShowEdit(video)'}" />
        <?php if( !empty($item['username']) ): ?>
        <img src="images/email-22x22.png" class="item-icon" title="E-mail" meta="{t: 'dialog', r: 'tbxGenericShowEmail(video)'}" />
        <?php endif; ?>
        <img src="images/delete-22x22.png" class="item-icon" title="Delete" meta="{t: 'action', r: 'tbxGenericAction(video,delete)'}" />

        <?php if( $item['status'] == STATUS_PENDING ): ?>
        <div class="process-pending-section" style="margin-top: 8px;">
          <img src="images/approve-22x22.png" class="item-icon" title="Approve" meta="{t: 'action', r: 'tbxGenericAction(video,approve)'}" />
          <img src="images/reject-22x22.png" class="item-icon" title="Reject" meta="{t: 'action', r: 'tbxGenericAction(video,reject)', f: '#reason-id'}" /><br />
          <select name="reason_id" id="reason-id">
            <option value="">-- None --</option>
            <?php echo Form_Field::Options($rejections, null, 'reason_id', 'short_name'); ?>
          </select>
        </div>
        <?php else: ?>
        <div class="flagged-featured-section" style="margin-top: 8px;">
          <img src="images/flag-22x22.png" class="item-icon" title="Reasons Flagged" meta="{t: 'dialog', r: 'tbxVideoReasonFlaggedShow()'}" />
          <img src="images/featured-<?php echo $item['is_featured'] ? '' : 'off-'; ?>22x22.png" class="item-icon" title="Reasons Featured" meta="{t: 'dialog', r: 'tbxVideoReasonFeaturedShow(video)'}" />

          <img title="Feature" class="item-icon" meta="{t: 'action', r: 'tbxGenericAction(video,feature)'}" style="display: none;" />
          <img title="Un-Feature" class="item-icon" meta="{t: 'action', r: 'tbxGenericAction(video,unfeature)'}" style="display: none;" />
        </div>
        <?php endif; ?>

        <div style="margin-top: 8px;">
          <img src="images/conversion-queue-22x22.png" class="item-icon" title="Convert" meta="{t: 'action', r: 'tbxGenericAction(video,convert)'}" />
          <img src="images/thumb-queue-22x22.png" class="item-icon" title="Thumbnail" meta="{t: 'action', r: 'tbxGenericAction(video,thumbnail)'}" />
        </div>
      </td>
    </tr>