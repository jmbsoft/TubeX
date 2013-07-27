    <?php
    // A few defaults
    $defaults = array();
    $defaults['cookie_domain'] = isset($_SERVER['HTTP_HOST']) ? preg_replace('~^www\.~i', '', $_SERVER['HTTP_HOST']) : null;
    $defaults['document_root'] = realpath($_SERVER['DOCUMENT_ROOT']);
    $defaults['base_url'] = "http://{$_SERVER['HTTP_HOST']}" . preg_replace('~/admin/.*~', '', $_SERVER['REQUEST_URI']);
    $defaults['no_preview'] = $defaults['base_url'] . '/templates/Default-Blue-Rewrite/images/no-preview-120x90.png';
    $defaults['random_value'] = sha1(uniqid(rand(), true));

    foreach( $defaults as $key => $val )
    {
        if( Config::Get($key) === null )
        {
            Config::Set($key, $val);
        }
    }
    ?>

    <div id="dialog-header" class="ui-widget-header ui-corner-all">
      <div id="dialog-close"></div>
      Global Software Settings
    </div>

    <form method="post" action="ajax.php">
      <div id="dialog-panel">
        <div style="padding: 8px;">

          <div class="fieldset">
            <div class="legend">Base Settings</div>

            <div id="dialog-help">
              <a href="docs/cp-global-settings.html" target="_blank"><img src="images/help-22x22.png" alt="Help" title="Help" border="0" /></a>
            </div>

            <div class="field">
              <label>Site Name:</label>
              <span class="field-container">
                <input type="text" name="site_name" size="60" value="<?php echo Config::Get('site_name'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Meta Description:</label>
              <span class="field-container">
                <input type="text" name="meta_description" size="80" value="<?php echo Config::Get('meta_description'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Meta Keywords:</label>
              <span class="field-container">
                <input type="text" name="meta_keywords" size="80" value="<?php echo Config::Get('meta_keywords'); ?>" />
              </span>
            </div>

            <div class="field">
              <label></label>
              <span class="field-container">
                <div class="checkbox">
                  <input type="hidden" name="flag_using_cron" value="<?php echo Config::Get('flag_using_cron'); ?>" />
                  Using cron for stats rollover, conversion queue, etc
                </div>
              </span>
            </div>

          </div>

          <div class="fieldset">
            <div class="legend">Directory and URL Settings</div>

            <div class="field">
              <label>Document Root:</label>
              <span class="field-container">
                <input type="text" name="document_root" size="80" value="<?php echo Config::Get('document_root'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>TubeX URL:</label>
              <span class="field-container">
                <input type="text" name="base_url" size="80" value="<?php echo Config::Get('base_url'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>No Thumbnail URL:</label>
              <span class="field-container">
                <input type="text" name="no_preview" size="80" value="<?php echo Config::Get('no_preview'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Cookie Domain:</label>
              <span class="field-container">
                <input type="text" name="cookie_domain" size="40" value="<?php echo Config::Get('cookie_domain'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Cookie Path:</label>
              <span class="field-container">
                <input type="text" name="cookie_path" size="40" value="<?php echo Config::Get('cookie_path'); ?>" />
              </span>
            </div>

            <div class="field">
              <label></label>
              <span class="field-container">
                <div class="checkbox">
                  <input type="hidden" name="flag_mod_rewrite" value="<?php echo Config::Get('flag_mod_rewrite'); ?>" />
                  Use mod_rewrite for search engine friendly URLs
                </div>
              </span>
            </div>

          </div>

          <div class="fieldset">
            <div class="legend">E-mail Settings</div>

            <div class="field">
              <label>E-mail Address:</label>
              <span class="field-container">
                <input type="text" name="email_address" size="40" value="<?php echo Config::Get('email_address'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>E-mail Name:</label>
              <span class="field-container">
                <input type="text" name="email_name" size="40" value="<?php echo Config::Get('email_name'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>E-mail Method:</label>
              <span class="field-container">
                <select name="mailer" id="mailer">
                  <?php
                  $mailers = array(Mailer::MAIL => 'PHP mail() function',
                                   Mailer::SENDMAIL => 'Sendmail',
                                   Mailer::SMTP => 'SMTP');
                  echo Form_Field::Options($mailers, Config::Get('mailer'));
                  ?>
                </select>
              </span>
            </div>

            <div class="field" id="sendmail_field" style="display: none;">
              <label>Sendmail Path:</label>
              <span class="field-container">
                <input type="text" name="sendmail_path" size="60" value="<?php echo Config::Get('sendmail_path'); ?>" />
              </span>
            </div>

            <div id="smtp_fields" style="display: none;">
              <div class="field">
                <label>SMTP Hostname:</label>
                <span class="field-container">
                  <input type="text" name="smtp_hostname" size="40" value="<?php echo Config::Get('smtp_hostname'); ?>" />
                </span>
              </div>

              <div class="field">
                <label>SMTP Port:</label>
                <span class="field-container">
                  <input type="text" name="smtp_port" size="3" value="<?php echo Config::Get('smtp_port'); ?>" />
                </span>
              </div>

              <div class="field">
                <label></label>
                <span class="field-container">
                  <div class="checkbox">
                    <input type="hidden" name="flag_smtp_ssl" value="<?php echo Config::Get('flag_smtp_ssl'); ?>" />
                    Use SSL for SMTP server connection
                  </div>
                </span>
              </div>

              <div class="field">
                <label>SMTP Username:</label>
                <span class="field-container">
                  <input type="text" name="smtp_username" size="25" value="<?php echo Config::Get('smtp_username'); ?>" />
                </span>
              </div>

              <div class="field">
                <label>SMTP Password:</label>
                <span class="field-container">
                  <input type="password" name="smtp_password" size="25" value="<?php echo Config::Get('smtp_password'); ?>" />
                </span>
              </div>
            </div>

          </div>


          <div class="fieldset">
            <div class="legend">Template &amp; Localization Settings</div>

            <div class="field">
              <label>Template Set:</label>
              <span class="field-container">
                <select name="template">
                  <?php
                  $templates = Dir::ReadDirectories(BASE_DIR . '/templates', '~^[^._]~');
                  asort($templates);
                  echo Form_Field::OptionsSimple($templates, Config::Get('template'));
                  ?>
                </select>
              </span>
            </div>

            <div class="field">
              <label>Language:</label>
              <span class="field-container">
                <select name="language">
                  <?php
                  $languages = array();
                  foreach( glob(LANGUAGE_DIR . '/*.ini') as $ini )
                  {
                      $languages[] = preg_replace('~\.ini$~', '', basename($ini));
                  }
                  echo Form_Field::OptionsSimple($languages, Config::Get('language'));
                  ?>
                </select>
              </span>
            </div>

            <div class="field">
              <label>Timezone:</label>
              <span class="field-container">
                <select name="timezone">
                  <?php
                  $timezones = GetTimezones();
                  echo Form_Field::OptionsSimple($timezones, Config::Get('timezone'));
                  ?>
                </select>
              </span>
            </div>

            <div class="field">
              <label>Date Format:</label>
              <span class="field-container">
                <input type="text" name="date_format" size="10" value="<?php echo Config::Get('date_format'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Time Format:</label>
              <span class="field-container">
                <input type="text" name="time_format" size="10" value="<?php echo Config::Get('time_format'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Decimal Point:</label>
              <span class="field-container">
                <input type="text" name="dec_point" size="2" value="<?php echo Config::Get('dec_point'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Thousands Separator:</label>
              <span class="field-container">
                <input type="text" name="thousands_sep" size="2" value="<?php echo Config::Get('thousands_sep'); ?>" />
              </span>
            </div>

          </div>


          <div class="fieldset">
            <div class="legend">User Account Settings</div>

            <div class="field">
              <label>Avatar Dimensions:</label>
              <span class="field-container">
                <input type="text" name="avatar_dimensions" size="10" value="<?php echo Config::Get('avatar_dimensions'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Avatar Filesize:</label>
              <span class="field-container">
                <input type="text" name="avatar_filesize" size="10" value="<?php echo Config::Get('avatar_filesize'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Avatar Extensions:</label>
              <span class="field-container">
                <input type="text" name="avatar_extensions" size="50" value="<?php echo Config::Get('avatar_extensions'); ?>" />
              </span>
            </div>

            <div class="field">
              <label></label>
              <span class="field-container">
                <div class="checkbox">
                  <input type="hidden" name="flag_user_confirm_email" value="<?php echo Config::Get('flag_user_confirm_email'); ?>" />
                  All new user accounts must be confirmed by e-mail
                </div>
              </span>
            </div>

            <div class="field">
              <label></label>
              <span class="field-container">
                <div class="checkbox">
                  <input type="hidden" name="flag_user_approve" value="<?php echo Config::Get('flag_user_approve'); ?>" />
                  Review new user accounts
                </div>
              </span>
            </div>

            <div class="field">
              <label></label>
              <span class="field-container">
                <div class="checkbox">
                  <input type="hidden" name="flag_user_strip_tags" value="<?php echo Config::Get('flag_user_strip_tags'); ?>" />
                  Strip HTML tags from submitted account information
                </div>
              </span>
            </div>

            <div class="field">
              <label></label>
              <span class="field-container">
                <div class="checkbox">
                  <input type="hidden" name="flag_guest_ratings" value="<?php echo Config::Get('flag_guest_ratings'); ?>" />
                  Allow guests to rate videos
                </div>
              </span>
            </div>

          </div>


          <div class="fieldset">
            <div class="legend">Video Conversion Settings</div>

            <?php if( !$si->can_convert ): ?>
            <div class="message-warning" style="margin-bottom: 15px;">Video conversion is not supported on your server</div>
            <input type="hidden" name="video_extensions" value="<?php echo Config::Get('video_extensions'); ?>" />
            <input type="hidden" name="video_format" value="<?php echo Config::Get('video_format'); ?>" />
            <input type="hidden" name="video_size" value="<?php echo Config::Get('video_size'); ?>" />
            <input type="hidden" name="video_bitrate" value="<?php echo Config::Get('video_bitrate'); ?>" />
            <input type="hidden" name="audio_bitrate" value="<?php echo Config::Get('audio_bitrate'); ?>" />
            <?php else: ?>

            <div class="field">
              <label>File Extensions:</label>
              <span class="field-container">
                <input type="text" name="video_extensions" size="50" value="<?php echo Config::Get('video_extensions'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Video Format:</label>
              <span class="field-container">
                <select name="video_format">
                  <?php
                  $formats = array();
                  if( $si->can_mp4 ) $formats[Video_Converter::FORMAT_MP4] = 'MP4 (H.264/AAC)';
                  if( $si->can_vp6 ) $formats[Video_Converter::FORMAT_VP6] = 'FLV (VP6/MP3)';
                  if( $si->can_flv ) $formats[Video_Converter::FORMAT_H263] = 'FLV (H.263/MP3)';

                  echo Form_Field::Options($formats, Config::Get('video_format'));
                  ?>
                </select>
              </span>
            </div>

            <div class="field">
              <label>Video Size:</label>
              <span class="field-container">
                <input type="text" name="video_size" size="10" value="<?php echo Config::Get('video_size'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Video Quality/Bitrate:</label>
              <span class="field-container">
                <input type="text" name="video_bitrate" size="10" value="<?php echo Config::Get('video_bitrate'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Audio Bitrate:</label>
              <span class="field-container">
                <input type="text" name="audio_bitrate" size="10" value="<?php echo Config::Get('audio_bitrate'); ?>" />
              </span>
            </div>

            <?php endif; ?>

            <?php if( Video_Thumbnail::CanResize() ): ?>
            <div class="field">
              <label>Thumbnail Size:</label>
              <span class="field-container">
                <input type="text" name="thumb_size" size="10" value="<?php echo Config::Get('thumb_size'); ?>" />
              </span>
            </div>
            <?php else: ?>
            <input type="text" name="thumb_size" size="10" value="<?php echo Config::Get('thumb_size'); ?>" />
            <?php endif; ?>

            <?php if( $si->can_thumbnail ): ?>
            <div class="field">
              <label>Thumbnail Quality:</label>
              <span class="field-container">
                <input type="text" name="thumb_quality" size="10" value="<?php echo Config::Get('thumb_quality'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Thumbnail Amount:</label>
              <span class="field-container">
                <input type="text" name="thumb_amount" size="10" value="<?php echo Config::Get('thumb_amount'); ?>" />
              </span>
            </div>

            <div class="field">
              <label></label>
              <span class="field-container">
                <div class="checkbox">
                  <input type="hidden" name="flag_letterbox" value="<?php echo Config::Get('flag_letterbox'); ?>" />
                  Letterbox thumbnails to maintain aspect ratio
                </div>
              </span>
            </div>
            <?php else: ?>
            <div class="message-warning" style="margin-bottom: 15px;">Thumbnail generation is not supported on your server</div>
            <input type="hidden" name="thumb_amount" value="<?php echo Config::Get('thumb_amount'); ?>" />
            <input type="hidden" name="thumb_quality" value="<?php echo Config::Get('thumb_quality'); ?>" />
            <input type="hidden" name="flag_letterbox" value="<?php echo Config::Get('flag_letterbox'); ?>" />
            <?php endif; ?>

          </div>

          <div class="fieldset">
            <div class="legend">Video Upload Settings</div>

            <div class="field">
              <label></label>
              <span class="field-container">
                <div class="checkbox">
                  <input type="hidden" name="flag_allow_uploads" value="<?php echo Config::Get('flag_allow_uploads'); ?>" />
                  Allow users to upload videos
                </div>
              </span>
            </div>

            <div class="field">
              <label></label>
              <span class="field-container">
                <div class="checkbox">
                  <input type="hidden" name="flag_upload_reject_duplicates" value="<?php echo Config::Get('flag_upload_reject_duplicates'); ?>" />
                  Do not allow the same video file to be uploaded more than once
                </div>
              </span>
            </div>

            <div class="field">
              <label></label>
              <span class="field-container">
                <div class="checkbox">
                  <input type="hidden" name="flag_upload_allow_private" value="<?php echo Config::Get('flag_upload_allow_private'); ?>" />
                  Allow users to make their uploaded videos private
                </div>
              </span>
            </div>

            <?php if( $si->can_convert ): ?>
            <div class="field">
              <label></label>
              <span class="field-container">
                <div class="checkbox">
                  <input type="hidden" name="flag_upload_convert" value="<?php echo Config::Get('flag_upload_convert'); ?>" />
                  Convert uploaded videos (not recommended for shared servers)
                </div>
              </span>
            </div>
            <?php endif; ?>

            <div class="field">
              <label></label>
              <span class="field-container">
                <div class="checkbox">
                  <input type="hidden" name="flag_upload_review" value="<?php echo Config::Get('flag_upload_review'); ?>" />
                  Review uploaded videos before displaying on site
                </div>
              </span>
            </div>

            <div class="field">
              <label>Allowed Extensions:</label>
              <span class="field-container">
                <input type="text" name="upload_extensions" size="50" value="<?php echo Config::Get('upload_extensions'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Maximum Size:</label>
              <span class="field-container">
                <input type="text" name="max_upload_size" size="10" value="<?php echo Config::Get('max_upload_size'); ?>" />
                <span class="small">
                (PHP post_max_size: <?php echo $si->php_settings[ServerInfo::PHP_POST_MAX_SIZE]; ?>, upload_max_size: <?php echo $si->php_settings[ServerInfo::PHP_UPLOAD_MAX_FILESIZE]; ?>)
                </span>
              </span>
            </div>

            <div class="field">
              <label>Maximum Duration:</label>
              <span class="field-container">
                <input type="text" name="max_upload_duration" size="10" value="<?php echo Config::Get('max_upload_duration'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Title Length:</label>
              <span class="field-container">
                <input type="text" name="title_min_length" size="5" value="<?php echo Config::Get('title_min_length'); ?>" />
                to
                <input type="text" name="title_max_length" size="5" value="<?php echo Config::Get('title_max_length'); ?>" />
                characters
              </span>
            </div>

            <div class="field">
              <label>Description Length:</label>
              <span class="field-container">
                <input type="text" name="description_min_length" size="5" value="<?php echo Config::Get('description_min_length'); ?>" />
                to
                <input type="text" name="description_max_length" size="5" value="<?php echo Config::Get('description_max_length'); ?>" />
                characters
              </span>
            </div>

            <div class="field">
              <label>Tags Required:</label>
              <span class="field-container">
                <input type="text" name="tags_min" size="5" value="<?php echo Config::Get('tags_min'); ?>" />
                to
                <input type="text" name="tags_max" size="5" value="<?php echo Config::Get('tags_max'); ?>" />
                words
              </span>
            </div>

            <div class="field">
              <label></label>
              <span class="field-container">
                <div class="checkbox">
                  <input type="hidden" name="flag_video_strip_tags" value="<?php echo Config::Get('flag_video_strip_tags'); ?>" />
                  Strip HTML tags from submitted video information
                </div>
              </span>
            </div>

          </div>

          <div class="fieldset">
            <div class="legend">Comment Settings</div>

            <div class="field">
              <label>Maximum Length:</label>
              <span class="field-container">
                <input type="text" name="comment_max_length" size="5" value="<?php echo Config::Get('comment_max_length'); ?>" />
                characters
              </span>
            </div>

            <div class="field">
              <label>Throttle Period:</label>
              <span class="field-container">
                <input type="text" name="comment_throttle_period" size="5" value="<?php echo Config::Get('comment_throttle_period'); ?>" />
                seconds
              </span>
            </div>

            <div class="field">
              <label></label>
              <span class="field-container">
                <div class="checkbox">
                  <input type="hidden" name="flag_comment_strip_tags" value="<?php echo Config::Get('flag_comment_strip_tags'); ?>" />
                  Strip HTML tags from comment text
                </div>
              </span>
            </div>

          </div>

          <div class="fieldset">
            <div class="legend">CAPTCHA Settings</div>

            <?php
            if( !function_exists('imagettfbbox') ):
            ?>
            <div class="message-warning">The required PHP GD extension is either not installed or was not compiled with Freetype support</div>
            <input type="hidden" name="flag_captcha_words" value="0" />
            <input type="hidden" name="flag_captcha_on_signup" value="0" />
            <input type="hidden" name="flag_captcha_on_upload" value="0" />
            <input type="hidden" name="flag_captcha_on_comment" value="0" />
            <?php
            else:
            ?>

            <div class="field">
              <label>String Length:</label>
              <span class="field-container">
                <input type="text" name="captcha_min_length" size="5" value="<?php echo Config::Get('captcha_min_length'); ?>" />
                to
                <input type="text" name="captcha_max_length" size="5" value="<?php echo Config::Get('captcha_max_length'); ?>" />
                characters
              </span>
            </div>

            <div class="field">
              <label></label>
              <span class="field-container">
                <div class="checkbox">
                  <input type="hidden" name="flag_captcha_words" value="<?php echo Config::Get('flag_captcha_words'); ?>" />
                  Use words file for CAPTCHA strings
                </div>
              </span>
            </div>

            <div class="field">
              <label></label>
              <span class="field-container">
                <div class="checkbox">
                  <input type="hidden" name="flag_captcha_on_signup" value="<?php echo Config::Get('flag_captcha_on_signup'); ?>" />
                  Use CAPTCHA on user signup form
                </div>
              </span>
            </div>

            <div class="field">
              <label></label>
              <span class="field-container">
                <div class="checkbox">
                  <input type="hidden" name="flag_captcha_on_upload" value="<?php echo Config::Get('flag_captcha_on_upload'); ?>" />
                  Use CAPTCHA on video upload form
                </div>
              </span>
            </div>

            <div class="field">
              <label></label>
              <span class="field-container">
                <div class="checkbox">
                  <input type="hidden" name="flag_captcha_on_comment" value="<?php echo Config::Get('flag_captcha_on_comment'); ?>" />
                  Use CAPTCHA on video comment form
                </div>
              </span>
            </div>

            <?php
            endif;
            ?>

          </div>

          <div class="fieldset">
            <div class="legend">Cache Settings</div>

            <div class="field">
              <label>Main Page:</label>
              <span class="field-container">
                <input type="text" name="cache_main" size="10" value="<?php echo Config::Get('cache_main'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Search Page:</label>
              <span class="field-container">
                <input type="text" name="cache_search" size="10" value="<?php echo Config::Get('cache_search'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Categories Page:</label>
              <span class="field-container">
                <input type="text" name="cache_categories" size="10" value="<?php echo Config::Get('cache_categories'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Browse Videos Page:</label>
              <span class="field-container">
                <input type="text" name="cache_browse" size="10" value="<?php echo Config::Get('cache_browse'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Video Page:</label>
              <span class="field-container">
                <input type="text" name="cache_video" size="10" value="<?php echo Config::Get('cache_video'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Profile Page:</label>
              <span class="field-container">
                <input type="text" name="cache_profile" size="10" value="<?php echo Config::Get('cache_profile'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Comments Page:</label>
              <span class="field-container">
                <input type="text" name="cache_comments" size="10" value="<?php echo Config::Get('cache_comments'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Custom Pages:</label>
              <span class="field-container">
                <input type="text" name="cache_custom" size="10" value="<?php echo Config::Get('cache_custom'); ?>" />
              </span>
            </div>

          </div>

        </div>
      </div>

      <div id="dialog-buttons">
        <img src="images/activity-16x16.gif" height="16" width="16" border="0" title="Working..." />
        <input type="submit" id="button-save" value="Apply Changes" />
        <input type="button" id="dialog-button-cancel" value="Cancel" style="margin-left: 10px;" />
      </div>

      <input type="hidden" name="r" value="tbxGlobalSettingsSave" />
    </form>

    <script language="JavaScript" type="text/javascript">
    $('#dialog-content form')
    .ajaxForm({success: function(data)
                        {
                            dialogButtonEnable();
                            dialogSuccess(data);
                        },
               beforeSubmit: function()
                             {
                                 dialogButtonDisable();
                             }});

    $('#mailer')
    .change(function()
            {
                $('#smtp_fields, #sendmail_field').hide();

                switch($(this).val())
                {
                    case 'smtp':
                        $('#smtp_fields').show();
                        break;

                    case 'sendmail':
                        $('#sendmail_field').show();
                        break;
                }
            })
    .trigger('change');
    </script>