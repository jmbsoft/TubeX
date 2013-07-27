<?php
if( !defined('TUBEX_CONTROL_PANEL') ) die('Invalid Access');
require_once('cp-global-header.php');

$DB = GetDB();
$si = ServerInfo::GetCached();
$categories = $DB->FetchAll('SELECT * FROM `tbx_category` ORDER BY `name`');

$defaults = array('status' => STATUS_ACTIVE,
                  'allow_comments' => 'Yes - Add Immediately',
                  'allow_ratings' => 1,
                  'allow_embedding' => 1,
                  'is_private' => 0);
$_REQUEST = array_merge($defaults, $_REQUEST);

$db_fields = array('' => '-- Skip --',
                   'title' => 'Title',
                   'description' => 'Description',
                   'tags' => 'Tags',
                   'category' => 'Category',
                   'embed_code' => 'Embed Code',
                   'gallery_url' => 'Gallery URL',
                   'video_url' => 'Video URL',
                   'base_video_url' => 'Base Video URL',
                   'video_filename' => 'Video Filename',
                   'thumbnail_url' => 'Thumbnail URL',
                   'base_thumbnail_url' => 'Base Thumbnail URL',
                   'thumbnail_filename' => 'Thumbnail Filename',
                   'duration_seconds' => 'Duration (in seconds)',
                   'duration_formatted' => 'Duration (HH:MM:SS)');

$result = $DB->Query('SELECT * FROM `tbx_video_custom_schema`');
while( $row = $DB->NextRow($result) )
{
    $db_fields[$row['name']] = $row['label'];
}
$DB->Free($result);
?>
<div class="centerer">
  <span class="centerer" style="width: 90%;">

    <div class="header" style="margin-bottom: 20px;">Configure Import Settings</div>

    <div style="position: relative;">
       <a href="docs/cp-video-import.html"><img src="images/help-32x32.png" title="Help" border="0" style="position: absolute; top: -38px; right: -10px;" target="_blank" /></a>
    </div>

    <div class="bold text-center" style="font-size: 130%; display: none;" id="importing-done">
      Video importing has been completed!<br />
      <a href="index.php?r=tbxVideoImportShow">Import More Videos</a>
    </div>

    <div class="bold" style="font-size: 110%; margin-bottom: 8px; display: none;" id="importing-text">
      <img src="images/activity-22x22.gif" style="vertical-align: middle;" />
      <span style="vertical-align: middle;">Importing videos, please wait...</span>
    </div>
    <div id="pb-import" class="progressbar" style="margin-bottom: 20px;"></div>

    <form method="post" action="index.php" target="iframe">

      <div class="fieldset">
        <div class="legend">Define the Data Format</div>

        <div class="import-field" style="padding: 0px;"></div>

        <?php for( $i = 0; $i < count($fields); $i++ ): ?>
        <div class="import-field">
          <span>
            <select name="fields[<?php echo $i; ?>]">
              <?php echo Form_Field::Options($db_fields, $fields[$i]['guess']); ?>
            </select>
          </span>
          <span>
            <?php echo htmlspecialchars($fields[$i]['value']); ?>
          </span>
        </div>
        <?php endfor; ?>
      </div>


      <div class="fieldset">
        <div class="legend">Default Settings</div>

        <div class="field">
          <label>Category:</label>
          <span class="field-container">
            <select name="category_id">
              <?php
              echo Form_Field::Options($categories, Request::Get('category_id'), 'category_id', 'name');
              ?>
            </select>
          </span>
        </div>

        <div class="field">
          <label>Sponsor:</label>
          <span class="field-container">
            <select name="sponsor_id">
              <option value="">-- None --</option>
              <?php
              $sponsors = $DB->FetchAll('SELECT * FROM `tbx_sponsor` ORDER BY `name`');
              echo Form_Field::Options($sponsors, Request::Get('sponsor_id'), 'sponsor_id', 'name');
              ?>
            </select>
          </span>
        </div>

        <div class="field">
          <label>Username:</label>
          <span class="field-container"><input type="text" size="30" name="username" value="<?php echo Request::Get('username'); ?>" acomplete="tbx_user.username" /></span>
        </div>

        <div class="field">
          <label>Duration:</label>
          <span class="field-container"><input type="text" size="10" name="duration" class="defaultvalue" defaultvalue="HH:MM:SS" value="HH:MM:SS" /></span>
        </div>

        <div class="field">
          <label>Status:</label>
          <span class="field-container">
            <select name="status">
              <?php
              $statuses = array(STATUS_PENDING,STATUS_SCHEDULED,STATUS_ACTIVE,STATUS_DISABLED);
              echo Form_Field::OptionsSimple($statuses, Request::Get('status'));
              ?>
            </select>
          </span>
        </div>

        <div class="field">
          <label>Allow Comments:</label>
          <span class="field-container">
            <select name="allow_comments">
              <?php
              $allows = array('No','Yes - Add Immediately','Yes - Require Approval');
              echo Form_Field::OptionsSimple($allows, Request::Get('allow_comments'));
              ?>
            </select>
          </span>
        </div>

        <div class="field">
          <label></label>
          <span class="field-container">
            <div class="checkbox">
              <input type="hidden" name="allow_ratings" value="<?php echo Request::Get('allow_ratings'); ?>" />
              Allow ratings
            </div>
          </span>
        </div>

        <div class="field">
          <label></label>
          <span class="field-container">
            <div class="checkbox">
              <input type="hidden" name="allow_embedding" value="<?php echo Request::Get('allow_embedding'); ?>" />
              Allow embedding
            </div>
          </span>
        </div>

        <div class="field">
          <label></label>
          <span class="field-container">
            <div class="checkbox">
              <input type="hidden" name="is_private" value="<?php echo Request::Get('is_private'); ?>" />
              Make private
            </div>
          </span>
        </div>

        <div class="field">
          <label></label>
          <span class="field-container">
            <div class="checkbox">
              <input type="hidden" name="flag_skip_imported_check" value="<?php echo Request::Get('flag_skip_imported_check'); ?>" />
              Do not check if these videos have been imported before (may result in duplicates!)
            </div>
          </span>
        </div>

      </div>

      <?php if( $si->can_convert || $si->can_thumbnail ): ?>
      <div class="fieldset">
        <div class="legend">Import Settings</div>

        <?php if( $si->can_convert ): ?>
        <div class="field">
          <label></label>
          <span class="field-container">
            <div class="checkbox">
              <input type="hidden" name="flag_convert" value="0" />
              Queue for conversion
            </div>
          </span>
        </div>
        <?php endif; ?>

        <?php if( $si->can_thumbnail ): ?>
        <div class="field">
          <label></label>
          <span class="field-container">
            <div class="checkbox">
              <input type="hidden" name="flag_thumb" value="0" />
              Queue for thumbnail generation
            </div>
          </span>
        </div>
        <?php endif; ?>

      </div>
      <?php endif; ?>

      <div style="text-align: right; margin-top: 10px;">
        <input type="submit" id="b-import" value="Import Videos">
      </div>

      <input type="hidden" name="delimiter" value="<?php echo $_REQUEST['delimiter']; ?>" />
      <input type="hidden" name="import_file" value="<?php echo $file; ?>" />
      <input type="hidden" name="r" value="tbxVideoImport" />
    </form>

  </span>
</div>


<script language="JavaScript">
$(function()
{
    $('input.defaultvalue').defaultvalue();
    $('div.progressbar').hide().progressbar();

    $('#b-import')
    .click(function()
           {
               $('form').hide();
               $('#importing-text').show();

               $('iframe[name="iframe"]')
               .bind('load', function()
                             {
                                 $('#importing-text').hide();
                                 $('#importing-done').show();
                             });
           });
});
</script>

<?php
require_once('cp-global-footer.php');
?>