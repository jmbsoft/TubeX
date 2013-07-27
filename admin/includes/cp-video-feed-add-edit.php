    <?php
    $DB = GetDB();
    $categories = $DB->FetchAll('SELECT * FROM `tbx_category` ORDER BY `name`');

    $si = ServerInfo::GetCached();
    $defaults = array('status' => STATUS_ACTIVE,
                      'allow_comments' => 'Yes - Add Immediately',
                      'allow_ratings' => 1,
                      'allow_embedding' => 1);
    $_REQUEST = array_merge($defaults, $_REQUEST);
    ?>

    <div id="dialog-header" class="ui-widget-header ui-corner-all">
      <div id="dialog-close"></div>
      <?php echo (isset($editing) ? 'Update a Video Feed' : 'Add a Video Feed'); ?>
    </div>

    <form method="post" action="ajax.php">
      <div id="dialog-panel">
        <div style="padding: 8px;">

        <?php if( empty($categories) ): ?>
         <div class="message-error">
           You will need to create at least one category before you can begin adding video feeds
         </div>
        <?php else: ?>

          <div class="fieldset">
            <div class="legend">Base Settings</div>

            <div style="margin: 0 auto 10px auto;  font-size: 130%; font-weight: bold; width: 50%; border: 1px dotted #0097FF;" class="text-center message-notice">
              <a href="http://www.jmbsoft.com/sponsors.php" target="_blank">Find Sponsors with Video Feeds</a>
            </div>

            <div id="dialog-help">
              <a href="docs/cp-video-feed.html" target="_blank"><img src="images/help-22x22.png" alt="Help" title="Help" border="0" /></a>
            </div>

            <div class="field">
              <label>Feed Name:</label>
              <span class="field-container"><input type="text" size="60" name="name" value="<?php echo Request::Get('name'); ?>" /></span>
            </div>

            <div class="field">
              <label>Feed URL:</label>
              <span class="field-container">
                <input type="text" size="80" name="feed_url" value="<?php echo Request::Get('feed_url'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Feed Type:</label>
              <span class="field-container">
                <select name="type">
                  <?php
                  $opts = array(Video_Feed::XMLVIDEOS,Video_Feed::YOUTUBE);
                  echo Form_Field::OptionsSimple($opts, Request::Get('type'));
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
              <label>Username:</label>
              <span class="field-container"><input type="text" size="30" name="username" value="<?php echo Request::Get('username'); ?>" acomplete="tbx_user.username" /></span>
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

          </div>

          <?php if( $si->can_convert || $si->can_thumbnail ): ?>
          <div class="fieldset" id="import_settings">
            <div class="legend">Import Settings</div>

            <?php if( $si->can_convert ): ?>
            <div class="field">
              <label></label>
              <span class="field-container">
                <div class="checkbox">
                  <input type="hidden" name="flag_convert" value="<?php echo Request::Get('flag_convert'); ?>" />
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
                  <input type="hidden" name="flag_thumb" value="<?php echo Request::Get('flag_thumb'); ?>" />
                  Queue for thumbnail generation
                </div>
              </span>
            </div>
            <?php endif; ?>

          </div>
          <?php endif; ?>

          <?php endif; //if( empty($categories) ): ?>

        </div>
      </div>

      <div id="dialog-buttons">
        <img src="images/activity-16x16.gif" height="16" width="16" border="0" title="Working..." />
        <?php if( !empty($categories) ): ?>
        <input type="submit" id="button-save" value="<?php echo (isset($editing) ? 'Save Changes' : 'Add Video Feed') ?>" />
        <?php endif; ?>
        <input type="button" id="dialog-button-cancel" value="Cancel" style="margin-left: 10px;" />
      </div>

      <input type="hidden" name="r" value="<?php echo (isset($editing) ? 'tbxGenericEdit' : 'tbxGenericAdd') ?>(video-feed)" />
      <input type="hidden" name="feed_id" value="<?php echo Request::Get('feed_id'); ?>" />
    </form>

<script language="JavaScript" type="text/javascript">
$(function()
{
    $('select[name="type"]')
    .change(function()
            {
                if( $(this).val() == '<?php echo Video_Feed::XMLVIDEOS; ?>' )
                {
                    $('#xml-video-feeds-link').show();
                    $('#import_settings').show();
                }
                else
                {
                    $('#xml-video-feeds-link').hide();
                    $('#import_settings').hide();
                }
            })
    .change();


});
</script>