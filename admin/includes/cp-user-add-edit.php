    <?php
    $DB = GetDB();
    $defaults = array('date_created' => Database_MySQL::Now(),
                      'status' => STATUS_ACTIVE,
                      'user_level_id' => $DB->QuerySingleColumn('SELECT `user_level_id` FROM `tbx_user_level` WHERE `is_default`=1'));

    $_REQUEST = array_merge($defaults, $_REQUEST);

    ?>

    <div id="dialog-header" class="ui-widget-header ui-corner-all">
      <div id="dialog-close"></div>
      <?php echo (isset($editing) ? 'Update a User' : 'Add a User'); ?>
    </div>

    <form method="post" action="ajax.php" enctype="multipart/form-data">
      <div id="dialog-panel">
        <div style="padding: 8px;">

          <div class="fieldset">
            <div class="legend">Default Fields</div>

          <div id="dialog-help">
            <a href="docs/cp-user.html" target="_blank"><img src="images/help-22x22.png" alt="Help" title="Help" border="0" /></a>
          </div>

            <div class="field">
              <label>Username:</label>
              <?php if( isset($editing) ): ?>
              <span class="text-container">
                <?php echo Request::Get('username'); ?>
                <input type="hidden" name="username" value="<?php echo Request::Get('username'); ?>" />
              </span>
              <?php else: ?>
              <span class="field-container">
                <input type="text" size="30" name="username" value="<?php echo Request::Get('username'); ?>" />
              </span>
              <?php endif; ?>
            </div>

            <div class="field">
              <label>Password:</label>
              <span class="field-container">
                <?php
                if( isset($editing) )
                {
                    $_REQUEST['password'] = '';
                }
                ?>
                <input type="text" size="30" name="password" value="<?php echo Request::Get('password'); ?>" />
                <?php if( isset($editing) ): ?><span class="small">Only fill in if you want to change</span><?php endif; ?>
              </span>
            </div>

            <div class="field">
              <label>E-mail Address:</label>
              <span class="field-container"><input type="text" size="60" name="email" value="<?php echo Request::Get('email'); ?>" /></span>
            </div>

            <?php if( !isset($editing) ): ?>
            <div class="field">
              <label></label>
              <span class="field-container">
                <div class="checkbox">
                  <input type="hidden" name="flag_send_email" value="0" />
                  Send account signup e-mail message
                </div>
              </span>
            </div>
            <?php endif; ?>

            <div class="field">
              <label>Date/Time Created:</label>
              <span class="field-container">
                <input type="text" size="22" name="date_created" class="datetimepicker" value="<?php echo Request::Get('date_created'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Status:</label>
              <span class="field-container">
                <select name="status">
                  <?php
                  $opts = array('Pending','Active','Disabled');
                  echo Form_Field::OptionsSimple($opts, Request::Get('status'));
                  ?>
                </select>
              </span>
            </div>

            <div class="field">
              <label>User Level:</label>
              <span class="field-container">
                <select name="user_level_id">
                <?php
                $DB = GetDB();
                $user_levels = $DB->FetchAll('SELECT * FROM `tbx_user_level` ORDER BY `name`');
                echo Form_Field::Options($user_levels, Request::Get('user_level_id'), 'user_level_id', 'name');
                ?>
                </select>
              </span>
            </div>

            <?php
            if( isset($editing) && Request::Get('avatar_id') ):
                $avatar = $DB->Row('SELECT * FROM `tbx_upload` WHERE `upload_id`=?', array(Request::Get('avatar_id')));
            ?>
            <div class="field">
              <label>Existing Avatar:</label>
              <span class="field-container">
                <div class="checkbox" style="display: block; margin-bottom: 5px;">
                  <input type="hidden" name="remove_avatar" value="0" />
                  Remove Avatar
                </div>
                <img src="<?php echo String::HtmlSpecialChars($avatar['uri']); ?>" class="avatar" />
              </span>
            </div>
            <?php endif; ?>

            <div class="field">
              <label>Upload Avatar:</label>
              <span class="field-container">
                <input type="file" size="50" name="avatar_file" /><br />
                <span class="small">JPG, GIF, or PNG image</span>
              </span>
            </div>

            <div class="field">
              <label>Name:</label>
              <span class="field-container"><input type="text" size="60" name="name" value="<?php echo Request::Get('name'); ?>" /></span>
            </div>

            <div class="field">
              <label>Birthday:</label>
              <span class="field-container"><input type="text" size="22" name="date_birth" class="datepicker" value="<?php echo Request::Get('date_birth'); ?>" />
              </span>
            </div>

            <div class="field">
              <label>Gender:</label>
              <span class="field-container">
                <select name="gender">
                  <?php
                  $opts = array('Male','Female');
                  echo Form_Field::OptionsSimple($opts, Request::Get('gender'));
                  ?>
                </select>
              </span>
            </div>

            <div class="field">
              <label>Relationship:</label>
              <span class="field-container">
                <select name="relationship">
                  <?php
                  $opts = array('Single','Taken','Open');
                  echo Form_Field::OptionsSimple($opts, Request::Get('relationship'));
                  ?>
                </select>
              </span>
            </div>

            <div class="field">
              <label>About Me:</label>
              <span class="field-container"><textarea name="about" rows="3" cols="60"><?php echo Request::Get('about'); ?></textarea></span>
            </div>

            <div class="field">
              <label>Website URL:</label>
              <span class="field-container"><input type="text" size="60" name="website_url" value="<?php echo Request::Get('website_url'); ?>" /></span>
            </div>

            <div class="field">
              <label>Hometown:</label>
              <span class="field-container"><input type="text" size="40" name="hometown" value="<?php echo Request::Get('hometown'); ?>" /></span>
            </div>

            <div class="field">
              <label>Current City:</label>
              <span class="field-container"><input type="text" size="40" name="current_city" value="<?php echo Request::Get('current_city'); ?>" /></span>
            </div>

            <div class="field">
              <label>Postal Code:</label>
              <span class="field-container"><input type="text" size="10" name="postal_code" value="<?php echo Request::Get('postal_code'); ?>" /></span>
            </div>

            <div class="field">
              <label>Current Country:</label>
              <span class="field-container"><input type="text" size="30" name="current_country" value="<?php echo Request::Get('current_country'); ?>" /></span>
            </div>

            <div class="field">
              <label>Occupations:</label>
              <span class="field-container"><textarea name="occupations" rows="3" cols="60"><?php echo Request::Get('occupations'); ?></textarea></span>
            </div>

            <div class="field">
              <label>Companies:</label>
              <span class="field-container"><textarea name="companies" rows="3" cols="60"><?php echo Request::Get('companies'); ?></textarea></span>
            </div>

            <div class="field">
              <label>Schools:</label>
              <span class="field-container"><textarea name="schools" rows="3" cols="60"><?php echo Request::Get('schools'); ?></textarea></span>
            </div>

            <div class="field">
              <label>Hobbies:</label>
              <span class="field-container"><textarea name="hobbies" rows="3" cols="60"><?php echo Request::Get('hobbies'); ?></textarea></span>
            </div>

            <div class="field">
              <label>Favorite Shows/Movies:</label>
              <span class="field-container"><textarea name="movies" rows="3" cols="60"><?php echo Request::Get('movies'); ?></textarea></span>
            </div>

            <div class="field">
              <label>Favorite Music:</label>
              <span class="field-container"><textarea name="music" rows="3" cols="60"><?php echo Request::Get('music'); ?></textarea></span>
            </div>

            <div class="field">
              <label>Favorite Books:</label>
              <span class="field-container"><textarea name="books" rows="3" cols="60"><?php echo Request::Get('books'); ?></textarea></span>
            </div>

          </div>

          <div class="fieldset">
            <div class="legend">Custom Fields</div>

            <?php echo Form_Field::GenerateFromCustom('user'); ?>

          </div>


        </div>
      </div>

      <div id="dialog-buttons">
        <img src="images/activity-16x16.gif" height="16" width="16" border="0" title="Working..." />
        <input type="submit" id="button-save" value="<?php echo (isset($editing) ? 'Save Changes' : 'Add User') ?>" />
        <input type="button" id="dialog-button-cancel" value="Cancel" style="margin-left: 10px;" />
      </div>

      <input type="hidden" name="detailed" value="0" />
      <input type="hidden" name="r" value="<?php echo (isset($editing) ? 'tbxGenericEdit' : 'tbxGenericAdd') ?>(user)" />
    </form>