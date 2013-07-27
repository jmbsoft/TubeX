<?php
if( !defined('TUBEX_CONTROL_PANEL') ) die('Invalid Access');

require_once('cp-global-header.php');
?>

<script language="JavaScript" type="text/javascript">
$(function()
{
    $('img.activity').hide();
    $('div.progressbar').hide().progressbar();

    $('#backup-form')
    .ajaxForm({beforeSubmit: function(data, form, options)
                             {
                                 $('input[type=submit]', form).attr('disabled', 'disabled');
                                 $('img.activity', form).show();
                                 return true;
                             },
               success: function(data, status, form)
                        {
                            $('input[type=submit]', form).removeAttr('disabled');
                            $('img.activity', form).hide();
                        }});

    $('#restore-form')
    .ajaxForm({beforeSubmit: function(data, form, options)
                             {
                                 if( confirm('Are you sure you want to restore your data from this file?') )
                                 {
                                     $('input[type=submit]', form).attr('disabled', 'disabled');
                                     $('img.activity', form).show();
                                     return true;
                                 }

                                 return false;
                             },
               success: function(data, status, form)
                        {
                            $('input[type=submit]', form).removeAttr('disabled');
                            $('img.activity', form).hide();
                        }});

    $('#query-form')
    .ajaxForm({beforeSubmit: function(data, form, options)
                             {
                                 if( confirm('Are you sure you want to run this database query?') )
                                 {
                                     $('#query-results').empty().hide();
                                     $('input[type=submit]', form).attr('disabled', 'disabled');
                                     $('img.activity', form).show();
                                     return true;
                                 }

                                 return false;
                             },
               success: function(data, status, form)
                        {
                            if( data.html )
                            {
                                $('#query-results').html(data.html).show();
                            }

                            $('input[type=submit]', form).removeAttr('disabled');
                            $('img.activity', form).hide();
                        }});

    $('form[target=iframe]')
    .submit(function()
            {
                $('input[type=submit]', this).attr('disabled', 'disabled');
                $('img.activity', this).show();
                return true;
            });
});
</script>

<div class="centerer">
  <span class="centerer" style="width: 90%;">

    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 10px;">
      <tr>
        <td width="49%" valign="top" class="fieldset">

          <div style="position: relative;">
            <div class="legend">Database Backup</div>
            Use this function to generate a dump of your current TubeX database.  This function will dump all of the MySQL data into a text file so it can be restored
            if needed.  The dump file will be generated in the following directory:<br />
            <b><?php echo BASE_DIR . '/data'; ?></b>

            <br />
            <br />

            <form method="post" action="ajax.php" id="backup-form">
              <b>Backup Filename:</b> <input type="text" name="filename" value="backup.sql" size="30" /> <input type="submit" value="Run Database Backup" />
              <input type="hidden" name="r" value="tbxDatabaseBackup" />
              <img src="images/activity-22x22.gif" border="0" title="Working..." class="activity" style="position: absolute; display: inline;" />
            </form>
          </div>

        </td>
        <td width="2%"></td>
        <td width="49%" valign="top" class="fieldset">

          <div style="position: relative;">
            <div class="legend">Database Restore</div>
              Use this function to restore data from a previously generated dump of your MySQL data.  Note that this will overwrite all of your existing MySQL database tables!

              <br />
              <br />

              <?php
              $files = Dir::ReadFiles(BASE_DIR . '/data', '~\.sql$~');
              if( count($files) ):
              ?>
              <b>Restore Filename:</b>
              <form method="post" action="ajax.php" id="restore-form">
                <select name="filename">
                  <?php echo Form_Field::OptionsSimple($files); ?>
                </select>
                <input type="submit" value="Run Database Restore" />
                <input type="hidden" name="r" value="tbxDatabaseRestore" />
                <img src="images/activity-22x22.gif" border="0" title="Working..." class="activity" style="position: absolute; display: inline;" />
              </form>
              <?php else: ?>
              <div class="message-warning">
                No dump files could be located!  Please upload the dump file (with a .sql extension) to this directory:<br />
                <?php echo BASE_DIR . '/data'; ?>
              </div>
              <?php endif; ?>
          </div>

        </td>
      </tr>
      <tr>
        <td style="height: 35px;">
        </td>
      </tr>
      <tr>
        <td width="49%" valign="top" class="fieldset">

          <div style="position: relative;">
            <div class="legend">Database Optimize</div>
            This function allows you to cleanup the MySQL database tables that TubeX uses so they are optimized for both
            speed and disk usage.  In general, you will not need to run this command more than once every few weeks, as MySQL is quite efficient in
            maintaining it's datafiles.  The only time you may need to run it more often is if you do a large number of deletions from the database.

            <br />
            <br />

            <div style="float: right; width: 60%;">
              <div id="pb-optimize" class="progressbar"></div>
            </div>

            <form method="post" action="index.php" target="iframe">
              <input type="submit" id="b-optimize" value="Run Database Optimize Now" />
              <input type="hidden" name="r" value="tbxDatabaseOptimize" />
              <img src="images/activity-22x22.gif" border="0" title="Working..." class="activity" style="position: absolute; display: inline;" />
            </form>
          </div>

        </td>
        <td width="2%"></td>
        <td width="49%" valign="top" class="fieldset">

          <div style="position: relative;">
            <div class="legend">Database Repair</div>
            In some cases, usually after your server recovers from a serious error, your MySQL database may become corrupted.  When this happens you will
            get error messages from TubeX indicating that there is a problem with one or more of your database tables.  In most circumstances MySQL
            is able to repair damaged tables automatically.  Use this function to run the automatic MySQL repair commands.

            <br />
            <br />

            <div style="float: right; width: 60%;">
              <div id="pb-repair" class="progressbar"></div>
            </div>

            <form method="post" action="index.php" target="iframe">
              <input type="submit" id="b-repair" value="Run Database Repair Now" />
              <input type="hidden" name="r" value="tbxDatabaseRepair" />
              <img src="images/activity-22x22.gif" border="0" title="Working..." class="activity" style="position: absolute; display: inline;" />
            </form>
          </div>

        </td>
      </tr>
      <tr>
        <td style="height: 35px;">
        </td>
      </tr>
      <tr>
        <td colspan="3" valign="top" class="fieldset">

          <div style="position: relative;">
            <div class="legend">Raw Database Query</div>
            This function allows you to run a raw SQL query against the MySQL database that is storing the TubeX data.  This feature is intended for advanced users who have experience working
            with SQL queries and understand the impact they will have on the database.  In most cases you will not need to use this feature, unless you have been specifically instructed to
            do so for tech support purposes.

            <br />
            <br />

            <form method="post" action="ajax.php" id="query-form">
              <b>Query:</b> <input type="text" name="query" style="width: 75%;" />
              <input type="submit" value="Run Database Query" />
              <input type="hidden" name="r" value="tbxDatabaseQuery" />
              <img src="images/activity-22x22.gif" border="0" title="Working..." class="activity" style="position: absolute; display: inline;" />
            </form>

            <div id="query-results"></div>
          </div>

        </td>
      </tr>
    </table>

    <div style="height: 50px;"></div>

  </span>
</div>


<!-- BEGIN TOOLBAR -->
<div id="toolbar">
  <div id="toolbar-content">

    <a href="docs/cp-database-utilities.html" target="_blank" ><img border="0" title="Documentation" class="toolbar-icon" src="images/help-32x32.png"/></a>

  </div>
</div>
<div class="toolbar-spacer"></div>
<!-- END TOOLBAR -->

<?php
require_once('cp-global-footer.php');
?>