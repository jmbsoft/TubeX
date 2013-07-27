    <?php
    $si = ServerInfo::GetCached();
    $defaults = array('date_added' => Database_MySQL::Now(),
                      'status' => STATUS_ACTIVE,
                      'allow_comments' => 'Yes - Add Immediately',
                      'allow_ratings' => 1,
                      'allow_embedding' => 1,
                      'is_private' => 0);
    $_REQUEST = array_merge($defaults, $_REQUEST);
    $DB = GetDB();
    $categories = $DB->FetchAll('SELECT `category_id`,`name` FROM `tbx_category` ORDER BY `name`');
    $clips = $DB->FetchAll('SELECT * FROM `tbx_video_clip` WHERE `video_id`=?', array(Request::Get('video_id')));
    ?>
    <div id="dialog-header" class="ui-widget-header ui-corner-all">
      <div id="dialog-close"></div>
      <?php echo (isset($editing) ? 'Update a Video' : 'Add a Video'); ?>
    </div>

    <form method="post" action="ajax.php" enctype="multipart/form-data">
      <div id="dialog-panel">
        <div style="padding: 8px;">

        <?php if( empty($categories) ): ?>
         <div class="message-error">
           You will need to create at least one category before you can begin adding videos
         </div>
        <?php else: ?>

        <?php if( !isset($editing) ): ?>
          <div class="fieldset">
            <div class="legend">Video Source</div>

            <div class="field">
              <label>Source:</label>
              <span class="field-container">
                <select name="source_type" id="source-type">
                  <option value="<?php echo Video_Source::UPLOAD; ?>">Uploads</option>
                  <?php if( $si->php_extensions[ServerInfo::EXT_CURL] ): ?>
                  <option value="<?php echo Video_Source::URL; ?>">URLs</option>
                  <?php endif; ?>
                  <?php if( $si->php_extensions[ServerInfo::EXT_CURL] ): ?>
                  <option value="<?php echo Video_Source::GALLERY; ?>">Gallery</option>
                  <?php endif; ?>
                  <option value="<?php echo Video_Source::EMBED; ?>">Embed Code</option>
                </select>
              </span>
            </div>

            <div class="field vs vs_<?php echo Video_Source::UPLOAD; ?>">
              <label>Upload:</label>
              <span class="field-container"><input type="file" size="60" name="source_uploads[]" /></span>
              <img src="images/add-16x16.png" class="clickable vertical-middle" style="padding-left: 6px;" title="Add" />
              <img src="images/remove-16x16.png" class="clickable vertical-middle" style="padding-left: 6px;" title="Remove" />
            </div>

            <div class="field vs vs_<?php echo Video_Source::URL; ?>">
              <label>URL:</label>
              <span class="field-container"><input type="text" size="80" name="source_urls[]" /></span>
              <img src="images/add-16x16.png" class="clickable vertical-middle" style="padding-left: 6px;" title="Add" />
              <img src="images/remove-16x16.png" class="clickable vertical-middle" style="padding-left: 6px;" title="Remove" />
            </div>

            <div class="field vs vs_<?php echo Video_Source::GALLERY; ?>">
              <label>Gallery URL:</label>
              <span class="field-container"><input type="text" size="80" name="source_gallery" /></span>
            </div>

            <div class="field vs vs_<?php echo Video_Source::EMBED; ?>">
              <label>Embed Code:</label>
              <span class="field-container"><textarea name="source_embed" rows="4" style="width: 600px;"></textarea></span>
            </div>

            <div class="field vs vs_<?php echo Video_Source::GALLERY; ?> vs_<?php echo Video_Source::URL; ?>">
              <label></label>
              <span class="field-container">
                <div class="checkbox">
                  <input type="hidden" name="flag_hotlink" id="flag-hotlink" value="1" />
                  Hotlink video clips
                </div>
              </span>
            </div>

            <?php if( $si->can_thumbnail ): ?>
            <div class="field vs vs_<?php echo Video_Source::GALLERY; ?> vs_<?php echo Video_Source::URL; ?>">
              <label></label>
              <span class="field-container">
                <div class="checkbox">
                  <input type="hidden" name="flag_thumb" id="flag-thumb" value="0" />
                  Queue for thumbnail generation
                </div>
              </span>
            </div>
            <?php endif; ?>

            <?php if( $si->can_convert ): ?>
            <div class="field vs vs_<?php echo Video_Source::GALLERY; ?> vs_<?php echo Video_Source::URL; ?> vs_<?php echo Video_Source::UPLOAD; ?>">
              <label></label>
              <span class="field-container">
                <div class="checkbox">
                  <input type="hidden" name="flag_convert" id="flag-convert" value="0" />
                  Queue video for conversion
                </div>
              </span>
            </div>
            <?php endif; ?>

            <div class="field vs vs_<?php echo Video_Source::GALLERY; ?> vs_<?php echo Video_Source::URL; ?> vs_<?php echo Video_Source::EMBED; ?><?php if( $si->shell_exec_disabled || !$si->binaries[ServerInfo::BIN_MENCODER] ): ?> vs_<?php echo Video_Source::UPLOAD; ?><?php endif; ?>">
              <label>Video Duration:</label>
              <span class="field-container"><input type="text" size="10" name="source_duration" class="defaultvalue" defaultvalue="HH:MM:SS" value="HH:MM:SS" /></span>
            </div>

            <div class="field vs vs_<?php echo Video_Source::EMBED; ?>">
              <label>Thumbnails:</label>
              <span class="field-container"><input type="file" size="60" name="source_thumbnails[]" /></span>
              <img src="images/add-16x16.png" class="clickable vertical-middle" style="padding-left: 6px;" title="Add" />
              <img src="images/remove-16x16.png" class="clickable vertical-middle" style="padding-left: 6px;" title="Remove" />
            </div>

          </div>
        <?php endif; ?>


          <div class="fieldset">
            <div class="legend">Default Fields</div>

            <div id="dialog-help">
              <a href="docs/cp-video.html" target="_blank"><img src="images/help-22x22.png" alt="Help" title="Help" border="0" /></a>
            </div>

            <div class="field">
              <label>Title:</label>
              <span class="field-container"><input type="text" size="80" name="title" value="<?php echo Request::Get('title'); ?>" /></span>
            </div>

            <div class="field">
              <label>Description:</label>
              <span class="field-container">
                <textarea name="description" rows="6" style="width: 600px;"><?php echo Request::Get('description'); ?></textarea>
              </span>
            </div>

            <div class="field">
              <label>Tags:</label>
              <span class="field-container"><input type="text" size="80" name="tags" value="<?php echo Request::Get('tags'); ?>" /></span>
            </div>

            <div class="field">
              <label>Username:</label>
              <span class="field-container"><input type="text" size="30" name="username" value="<?php echo Request::Get('username'); ?>" acomplete="tbx_user.username" /></span>
            </div>

            <div class="field">
              <label>Date/Time Added:</label>
              <span class="field-container"><input type="text" size="22" name="date_added" class="datetimepicker" value="<?php echo Request::Get('date_added'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Date Recorded:</label>
              <span class="field-container"><input type="text" size="22" name="date_recorded" class="datepicker" value="<?php echo Request::Get('date_recorded'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Location Recorded:</label>
              <span class="field-container"><input type="text" size="60" name="location_recorded" value="<?php echo Request::Get('location_recorded'); ?>" /></span>
            </div>

            <?php if( isset($editing) ): ?>
            <div class="field">
              <label>Video Duration:</label>
              <span class="field-container"><input type="text" size="10" name="duration" value="<?php echo Format::SecondsToDuration(Request::Get('duration')); ?>" /></span>
            </div>
            <?php endif; ?>

            <!--
            <div class="field">
              <label>Status:</label>
              <span class="field-container">
                <select name="status">
                  <?php
                  $opts = array(STATUS_ACTIVE,STATUS_DISABLED);
                  echo Form_Field::OptionsSimple($opts, Request::Get('status'));
                  ?>
                </select>
              </span>
            </div>
            -->

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
                  $sponsors = $DB->FetchAll('SELECT `sponsor_id`,`name` FROM `tbx_sponsor` ORDER BY `name`');
                  echo Form_Field::Options($sponsors, Request::Get('sponsor_id'), 'sponsor_id', 'name');
                  ?>
                </select>
              </span>
            </div>

            <div class="field">
              <label>Comments Allowed:</label>
              <span class="field-container">
                <select name="allow_comments">
                  <?php
                  $opts = array('No','Yes - Add Immediately','Yes - Require Approval');
                  echo Form_Field::OptionsSimple($opts, Request::Get('allow_comments'));
                  ?>
                </select>
              </span>
            </div>

            <div class="field">
              <label></label>
              <span class="field-container">
                <div class="checkbox">
                  <input type="hidden" name="is_private" value="<?php echo Request::Get('is_private'); ?>" />
                  Private
                </div>
              </span>
            </div>

            <div class="field">
              <label></label>
              <span class="field-container">
                <div class="checkbox">
                  <input type="hidden" name="is_featured" value="<?php echo Request::Get('is_featured'); ?>" />
                  Featured
                </div>
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

          </div>

          <?php if( isset($editing) ): ?>
          <div class="fieldset">
            <div class="legend">Clips</div>

            <?php foreach( $clips as $clip ): String::HtmlSpecialChars($clip); ?>
              <?php if( $clip['type'] == 'Embed' ): ?>
            <div class="field">
              <label>Embed Code:</label>
              <span class="field-container">
                <textarea name="clips[<?php echo $clip['clip_id']; ?>][clip]" rows="6" style="width: 600px;" wrap="off"><?php echo $clip['clip']; ?></textarea>
              </span>
            </div>
              <?php else: ?>
            <div class="field">
              <label>Clip URL:</label>
              <span class="field-container"><input type="text" size="80" name="clips[<?php echo $clip['clip_id']; ?>][clip]" value="<?php echo $clip['clip']; ?>" /></span>
            </div>
              <?php endif; ?>
            <?php endforeach; ?>

          </div>


          <div class="fieldset" id="thumbnails">
            <div class="legend">Thumbnails</div>
            <?php
            $thumbs = $DB->FetchAll('SELECT * FROM `tbx_video_thumbnail` WHERE `video_id`=? ORDER BY `thumbnail_id`', array(Request::Get('video_id')));
            ?>

            <div id="thumb-none" class="message-warning text-center"<?php if( !empty($thumbs) ): ?> style="display: none;"<?php endif; ?>>No thumbnails exist for this video</div>

            <div id="thumb-select"<?php if( empty($thumbs) ): ?> style="display: none;"<?php endif; ?>>
              <div style="font-weight: bold; margin-bottom: 4px;">
                Select the thumbnail image that you would like to appear as the preview for this video<br />
                To delete a thumbnail, hold down shift on your keyboard as you click
              </div>
              <div class="thumb-select-container">
              <?php foreach( $thumbs as $thumb ): ?>
                <img src="<?php echo $thumb['thumbnail'] . '?' . rand(); ?>" border="0" height="90" thumbid="<?php echo $thumb['thumbnail_id']; ?>" class="clickable <?php echo $thumb['thumbnail_id'] == Request::Get('display_thumbnail') ? 'thumb-selected' : ''; ?>" />
              <?php endforeach; ?>
              </div>
            </div>

            <input type="hidden" name="display_thumbnail" value="<?php echo Request::Get('display_thumbnail'); ?>" />

            <div class="field thumb-uploads" style="margin-top: 8px;">
              <label>Upload .jpg Image:</label>
              <span class="field-container"><input type="file" size="60" name="thumb_uploads[]" /></span>
              <img src="images/add-16x16.png" class="clickable vertical-middle" style="padding-left: 6px;" title="Add" />
              <img src="images/remove-16x16.png" class="clickable vertical-middle" style="padding-left: 6px;" title="Remove" />
            </div>

          </div>
          <?php endif; ?>

          <div class="fieldset">
            <div class="legend">Custom Fields</div>

            <?php echo Form_Field::GenerateFromCustom('video'); ?>

          </div>

          <?php endif; //if( empty($categories) ): ?>
        </div>
      </div>

      <div id="dialog-buttons">
        <img src="images/activity-16x16.gif" height="16" width="16" border="0" title="Working..." />
        <?php if( !empty($categories) ): ?>
        <input type="submit" id="button-save" value="<?php echo (isset($editing) ? 'Save Changes' : 'Add Video') ?>" />
        <?php endif; ?>
        <input type="button" id="dialog-button-cancel" value="Cancel" style="margin-left: 10px;" />
      </div>

      <input type="hidden" name="r" value="<?php echo (isset($editing) ? 'tbxVideoEdit' : 'tbxVideoAdd') ?>(video)" />
      <input type="hidden" name="video_id" value="<?php echo Request::Get('video_id'); ?>" />
      <input type="hidden" name="detailed" value="0" />
    </form>

    <?php IncludeJavascript(BASE_DIR . '/admin/js/cp-video-add.js'); ?>