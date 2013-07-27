    <div id="dialog-header" class="ui-widget-header ui-corner-all">
      <div id="dialog-close"></div>
      <?php echo (isset($editing) ? 'Update an Administrator' : 'Add an Administrator'); ?>
    </div>

    <form method="post" action="ajax.php">
      <div id="dialog-panel">
        <div style="padding: 8px;">

          <div class="fieldset">
            <div class="legend">General Settings</div>

            <div id="dialog-help">
              <a href="docs/cp-administrator.html" target="_blank"><img src="images/help-22x22.png" alt="Help" title="Help" border="0" /></a>
            </div>

            <div class="field">
              <label>Username:</label>
              <?php if( isset($editing) ): ?>
              <span class="text-container">
                <?php echo Request::Get('username'); ?>
                <input type="hidden" name="username" value="<?php echo Request::Get('username'); ?>" />
              </span>
              <?php else: ?>
              <span class="field-container"><input type="text" size="35" name="username" value="<?php echo Request::Get('username'); ?>" /></span>
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
                <input type="text" size="35" name="password" value="<?php echo Request::Get('password'); ?>" />
                <?php if( isset($editing) ): ?><span class="small">Only fill in if you want to change</span><?php endif; ?>
              </span>
            </div>

            <div class="field">
              <label>E-mail Address:</label>
              <span class="field-container"><input type="text" size="60" name="email" value="<?php echo Request::Get('email'); ?>" /></span>
            </div>

            <div class="field">
              <label>Name:</label>
              <span class="field-container"><input type="text" size="40" name="name" value="<?php echo Request::Get('name'); ?>" /></span>
            </div>

            <div class="field">
              <label>Account Type:</label>
              <span class="field-container">
                <select name="type" id="account_type">
                  <?php
                  $opts = array('Superuser','Editor');
                  echo Form_Field::OptionsSimple($opts, Request::Get('type'));
                  ?>
                </select>
              </span>
            </div>

          </div>

          <div class="fieldset">
            <div class="legend">Privileges</div>

            <div class="overlay" id="privileges_overlay">PRIVILEGES ARE ONLY SET FOR EDITOR ACCOUNTS</div>

            <table width="700" align="center" id="privileges_checkboxes">
              <tr>

              <?php
              $reflect = new ReflectionClass('Privileges');
              $privileges = $reflect->getConstants();

              $counter = 0;
              $total = count($privileges);
              foreach( $privileges as $privilege => $bitmask ):
                  $counter++;
              ?>
              <td width="33%">
                  <div class="checkbox">
                    <input type="hidden" name="PRIVILEGE_<?php echo $privilege; ?>" value="<?php echo ($bitmask & Request::Get('privileges') ? 1 : 0); ?>" />
                    Manage <?php echo ucwords(strtolower(str_replace('_', ' ', $privilege))); ?>
                  </div>
                </td>
                <?php if( $counter % 3 == 0 && $counter != $total ): ?>
              </tr>
              <tr>
              <?php
                  endif;
              endforeach;
               ?>

              </tr>
            </table>

          </div>

        </div>
      </div>

      <div id="dialog-buttons">
        <img src="images/activity-16x16.gif" height="16" width="16" border="0" title="Working..." />
        <input type="submit" id="button-save" value="<?php echo (isset($editing) ? 'Save Changes' : 'Add Administrator') ?>" />
        <input type="button" id="dialog-button-cancel" value="Cancel" style="margin-left: 10px;" />
      </div>

      <input type="hidden" name="r" value="<?php echo (isset($editing) ? 'tbxGenericEdit' : 'tbxGenericAdd') ?>(administrator)" />

    </form>

    <script language="JavaScript" type="text/javascript">

    $('#dialog-panel')
    .bind('dialog-visible', function()
                            {
                                $('#privileges_overlay').overlayOn('#privileges_checkboxes');

                                $('#account_type')
                                .change(function()
                                        {
                                            switch( $(this).val() )
                                            {
                                                case 'Editor':
                                                    $('#privileges_overlay').hide();
                                                    break;

                                                default:
                                                    $('#privileges_overlay').show();
                                                    break;
                                            }
                                        })
                                .trigger('change');
                            });
    </script>