<?php
if( !defined('TUBEX_CONTROL_PANEL') ) die('Invalid Access');

require_once('cp-global-header.php');
?>

<script language="JavaScript" type="text/javascript">
$(function()
{
    $('#tabs').tabs();

    $('.index-thumb-container img')
    .addClass('clickable')
    .click(videoPlayerPopup);
});
</script>

<div class="centerer">
  <span class="centerer" style="width: 1000px;">

    <div class="fieldset">
      <div class="legend" style="font-size: 110%">Functions</div>

      <span class="index-function-container">
        <img src="images/video-64x64.png" />
        <span class="index-function-text">
          <span class="index-function-title">Videos</span><br />
          <a href="index.php?r=tbxGenericShowSearch(video)">Search</a><br />
          <a href="" meta="{r: 'tbxGenericShowAdd(video)'}" class="dialog">Add New</a><br />
          <a href="index.php?r=tbxVideoImportShow">Import</a><br />
          <a href="index.php?r=tbxGenericShowSearch(video)&pds=pending">Review</a><br />
          <a href="index.php?r=tbxGenericShowSearch(video-feed)">Feeds</a><br />
          <a href="" meta="{r: 'tbxGenericShowAdd(video-feed)'}" class="dialog">Add Feed</a>
        </span>
      </span>

      <span class="index-function-container">
        <img src="images/user-64x64.png" />
        <span class="index-function-text">
          <span class="index-function-title">Users</span><br />
          <a href="index.php?r=tbxGenericShowSearch(user)">Search</a><br />
          <a href="" meta="{r: 'tbxGenericShowAdd(user)'}" class="dialog">Add New</a><br />
          <a href="index.php?r=tbxGenericShowSearch(user)&pds=pending">Review</a><br />
          <a href="index.php?r=tbxGenericShowSearch(user-level)">User Levels</a>
        </span>
      </span>

      <span class="index-function-container">
        <img src="images/template-64x64.png" />
        <span class="index-function-text">
          <span class="index-function-title">Templates</span><br />
          <a href="index.php?r=tbxSiteTemplateShow">Site</a><br />
          <a href="index.php?r=tbxEmailTemplateShow">E-mail</a><br />
          <a href="index.php?r=tbxGenericShowSearch(reason)">Reasons</a>
        </span>
      </span>

      <span class="index-function-container">
        <img src="images/settings-64x64.png" />
        <span class="index-function-text">
          <span class="index-function-title">Settings</span><br />
          <a href="" meta="{r: 'tbxGlobalSettingsShow'}" class="dialog">Global</a><br />
          <a href="index.php?r=tbxGenericShowSearch(blacklist-item)">Blacklist</a><br />
          <a href="index.php?r=tbxGenericShowSearch(banner)">Banners</a><br />
          <a href="index.php?r=tbxGenericShowSearch(administrator)">Administrators</a>
        </span>
      </span>

      <span class="index-function-container">
        <img src="images/comments-64x64.png" />
        <span class="index-function-text">
          <span class="index-function-title">Comments</span><br />
          <a href="index.php?r=tbxGenericShowSearch(video-comment)">Search</a><br />
          <a href="index.php?r=tbxGenericShowSearch(video-comment)&pds=pending">Review</a>
        </span>
      </span>

      <span class="index-function-container">
        <img src="images/database-64x64.png" />
        <span class="index-function-text">
          <span class="index-function-title">Database</span><br />
          <a href="index.php?r=tbxDatabaseUtilitiesShow">Utilities</a><br />
          <a href="index.php?r=tbxGenericShowSearch(video-custom-field)">Video Fields</a><br />
          <a href="index.php?r=tbxGenericShowSearch(user-custom-field)">User Fields</a><br />
          <a href="index.php?r=tbxGenericShowSearch(sponsor-custom-field)">Sponsor Fields</a><br />
          <a href="index.php?r=tbxGenericShowSearch(category-custom-field)">Category Fields</a><br />
        </span>
      </span>

      <span class="index-function-container">
        <img src="images/sponsors-64x64.png" />
        <span class="index-function-text">
          <span class="index-function-title">Sponsors</span><br />
          <a href="index.php?r=tbxGenericShowSearch(sponsor)">Search</a><br />
          <a href="" meta="{r: 'tbxGenericShowAdd(sponsor)'}" class="dialog">Add New</a>
        </span>
      </span>

      <span class="index-function-container">
        <img src="images/misc-64x64.png" />
        <span class="index-function-text">
          <span class="index-function-title">Other</span><br />
          <a href="index.php?r=tbxGenericShowSearch(category)">Categories</a><br />
          <a href="index.php?r=tbxGenericShowSearch(search-term)">Search Terms</a><br />
          <a href="docs/">Documentation</a><br />
          <a href="" meta="{r: 'tbxAboutShow'}" class="dialog">About</a>
        </span>
      </span>

    </div>

    <br />

    <!-- TABS START -->
    <div id="tabs">
      <ul>
        <li><a href="#tabs-1">New Videos</a></li>
        <li><a href="#tabs-2">New Users</a></li>
      </ul>
      <div id="tabs-1">
        <div>
          <?php
          $DB = GetDB();
          $result = $DB->Query('SELECT * FROM `tbx_video` ORDER BY `date_added` DESC LIMIT 5');

          if( $DB->NumRows($result) < 1 ):
          ?>
          <div class="message-warning text-center">No videos have been added yet!</div>
          <?php
          endif;

          while( $video = $DB->NextRow($result) ):
              $video = String::HtmlSpecialChars($video);
              $video['date_added'] = date(DATETIME_FRIENDLY, strtotime($video['date_added']));

              $preview_src = 'images/no-preview-120x90.png';
              if( !empty($video['display_thumbnail']) )
              {
                  $thumb = $DB->Row('SELECT * FROM `tbx_video_thumbnail` WHERE `thumbnail_id`=?', array($video['display_thumbnail']));
                  $preview_src = $thumb['thumbnail'];
              }
          ?>
          <div style="margin: 8px 0;">
            <span class="index-thumb-container">
              <img src="<?php echo $preview_src; ?>" videoid="<?php echo $video['video_id']; ?>" />
            </span>
            <span class="index-video-text">
              <b><?php echo $video['title']; ?></b><br/>
              <?php echo String::Truncate($video['description'], 300); ?><br />
              <span class="small defaultvalue"><?php echo $video['date_added']; ?></span>
            </span>
          </div>
          <?php
          endwhile;
          ?>
        </div>
      </div>
      <div id="tabs-2">
        <div>
          <?php
          $DB = GetDB();
          $result = $DB->Query('SELECT * FROM `tbx_user` ORDER BY `date_created` DESC LIMIT 5');

          if( $DB->NumRows($result) < 1 ):
          ?>
          <div class="message-warning text-center">No users have signed up yet!</div>
          <?php
          endif;

          while( $user = $DB->NextRow($result) ):
              $user = String::HtmlSpecialChars($user);
              $user['date_created'] = date(DATETIME_FRIENDLY, strtotime($user['date_created']));
              $stats = $DB->Row('SELECT * FROM `tbx_user_stat` WHERE `username`=?', array($user['username']));

              $avatar_src = '../images/avatar-150x120.png';
              if( !empty($user['avatar_id']) )
              {
                  $avatar = $DB->Row('SELECT * FROM `tbx_upload` WHERE `upload_id`=?', array($user['avatar_id']));
                  $avatar_src = $avatar['uri'];
              }
          ?>
          <div style="margin: 8px 0;">
            <span class="index-avatar-container">
              <img src="<?php echo $avatar_src; ?>" />
            </span>
            <span class="index-user-text">
              <b><?php echo $user['username']; ?></b><br />
              <span class="small defaultvalue">
                <?php echo $user['date_created']; ?><br />
                <?php echo NumberFormatInteger($stats['total_videos_uploaded']); ?> Videos,
                <?php echo NumberFormatInteger($stats['total_videos_watched']); ?> Watched
              </span>
            </span>
          </div>
          <?php
          endwhile;
          ?>
        </div>
      </div>
    </div>
    <!-- TABS END -->

    <div style="height: 50px;"></div>

  </span>
</div>

<?php require_once('cp-global-footer.php'); ?>