    <?php
    // Setup data
    $item['user_level_id'] = !empty($item['user_level_id']) && isset($user_levels[$item['user_level_id']]) ? $user_levels[$item['user_level_id']]['name'] : '';
    $item['date_birth'] = !empty($item['date_birth']) ? date(DATE_FRIENDLY, strtotime($item['date_birth'])) . ' (' . GetAgeInYears($item['date_birth']) . ')' : '';
    $item['date_created'] = date(DATETIME_FRIENDLY, strtotime($item['date_created']));
    $item['date_last_login'] = !empty($item['date_last_login']) ? date(DATETIME_FRIENDLY, strtotime($item['date_last_login'])) : '-';

    $avatar_src = str_replace(Config::Get('document_root'), '', TEMPLATES_DIR . '/images/avatar-150x120.png');
    if( !empty($item['avatar_id']) )
    {
        $avatar = $DB->Row('SELECT * FROM `tbx_upload` WHERE `upload_id`=?', array($item['avatar_id']));
        $avatar_src = $avatar['uri'];
    }
    ?>
    <tr class="search-hilite search-result <?php echo $item['status']; ?>" id="<?php echo $item['username']; ?>">
      <td class="selectable" style="width: 40px;" title="Select">
      </td>
      <td>
        <div class="relative" style="min-height: 92px;">
          <img src="<?php echo $avatar_src; ?>" class="avatar" />
          <b style="color: #ef8c16; font-size: 110%;"><?php echo $item['username']; ?></b><br/>
          <?php echo ResizeableColumn('E-mail Address', $item['email']); ?>
          <?php echo ResizeableColumn('Date Created', $item['date_created']); ?>
          <?php echo ResizeableColumn('Last Login', $item['date_last_login']); ?>
          <?php echo ResizeableColumn('Status', $item['status']); ?>
          <?php echo ResizeableColumn('User Level', $item['user_level_id']); ?>
          <?php echo ResizeableColumn('Name', $item['name']); ?>
          <?php if( $_REQUEST['detailed'] ): ?>
          <?php echo ResizeableColumn('Birthday', $item['date_birth']); ?>
          <?php echo ResizeableColumn('Gender', $item['gender']); ?>
          <?php echo ResizeableColumn('Relationship', $item['relationship']); ?>
          <?php echo ResizeableColumn('About Me', $item['about']); ?>
          <?php echo ResizeableColumn('Website URL', $item['website_url']); ?>
          <?php echo ResizeableColumn('Hometown', $item['hometown']); ?>
          <?php echo ResizeableColumn('Current City', $item['current_city']); ?>
          <?php echo ResizeableColumn('Postal Code', $item['postal_code']); ?>
          <?php echo ResizeableColumn('Current Country', $item['current_country']); ?>
          <?php echo ResizeableColumn('Occupations', $item['occupations']); ?>
          <?php echo ResizeableColumn('Companies', $item['companies']); ?>
          <?php echo ResizeableColumn('Schools', $item['schools']); ?>
          <?php echo ResizeableColumn('Hobbies', $item['hobbies']); ?>
          <?php echo ResizeableColumn('Shows/Movies', $item['movies']); ?>
          <?php echo ResizeableColumn('Music', $item['music']); ?>
          <?php echo ResizeableColumn('Books', $item['books']); ?>
          <?php echo ResizeableColumn('Video Views',
                               array($item['today_video_views'],
                                     $item['week_video_views'],
                                     $item['month_video_views'],
                                     $item['total_video_views']),
                               true,
                               ' / '); ?>

          <?php echo ResizeableColumn('Profile Views',
                               array($item['today_profile_views'],
                                     $item['week_profile_views'],
                                     $item['month_profile_views'],
                                     $item['total_profile_views']),
                               true,
                               ' / '); ?>

          <?php echo ResizeableColumn('Videos Watched',
                               array($item['today_videos_watched'],
                                     $item['week_videos_watched'],
                                     $item['month_videos_watched'],
                                     $item['total_videos_watched']),
                               true,
                               ' / '); ?>

          <?php echo ResizeableColumn('Bandwidth Used',
                               array(Format::BytesToString($item['today_bandwidth_used'], '', 1),
                                     Format::BytesToString($item['week_bandwidth_used'], '', 1),
                                     Format::BytesToString($item['month_bandwidth_used'], '', 1),
                                     Format::BytesToString($item['total_bandwidth_used'], '', 1)),
                               false,
                               ' / '); ?>

          <?php echo ResizeableColumn('Uploads',
                               array($item['today_videos_uploaded'],
                                     $item['week_videos_uploaded'],
                                     $item['month_videos_uploaded'],
                                     $item['total_videos_uploaded']),
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
        </div>
      </td>
      <td valign="top" class="search-result-icons">
        <img src="images/enable-22x22.png" class="item-icon" title="Enable" <?php if( $item['status'] != STATUS_DISABLED ): ?> style="display: none;"<?php endif; ?> meta="{t: 'action', r: 'tbxGenericAction(user,enable)'}" />
        <img src="images/disable-22x22.png" class="item-icon" title="Disable" <?php if( $item['status'] != STATUS_ACTIVE ): ?> style="display: none;"<?php endif; ?> meta="{t: 'action', r: 'tbxGenericAction(user,disable)'}" />
        <img src="images/video-22x22.png" class="item-icon" title="Videos" meta="{t: 'link', u: 'index.php?r=tbxGenericShowSearch(video)&pds=user&username=<?php echo urlencode($original['username']); ?>'}" />
        <!--<img src="images/blacklist-22x22.png" class="item-icon" title="Blacklist" meta="{t: 'action', r: 'tbxGenericAction(user,blacklist)'}" />-->
        <img src="images/edit-22x22.png" class="item-icon" title="Edit" meta="{t: 'dialog', r: 'tbxGenericShowEdit(user)'}" />
        <img src="images/email-22x22.png" class="item-icon" title="E-mail" meta="{t: 'dialog', r: 'tbxGenericShowEmail(user)'}" />
        <img src="images/delete-22x22.png" class="item-icon" title="Delete" meta="{t: 'action', r: 'tbxGenericAction(user,delete)'}" />
        <?php if( $item['status'] == STATUS_PENDING ): ?>
        <div class="process-pending-section">
          <img src="images/approve-22x22.png" class="item-icon" title="Approve" meta="{t: 'action', r: 'tbxGenericAction(user,approve)'}" />
          <img src="images/reject-22x22.png" class="item-icon" title="Reject" meta="{t: 'action', r: 'tbxGenericAction(user,reject)', f: '#reason-id'}" /><br />
          <select name="reason_id" id="reason-id">
            <option value="">-- None --</option>
            <?php echo Form_Field::Options($rejections, null, 'reason_id', 'short_name'); ?>
          </select>
        </div>
        <?php endif; ?>
      </td>
    </tr>