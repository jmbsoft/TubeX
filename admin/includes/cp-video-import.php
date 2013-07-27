<?php
if( !defined('TUBEX_CONTROL_PANEL') ) die('Invalid Access');
require_once('cp-global-header.php');
$DB = GetDB();
?>
<div class="centerer">
  <span class="centerer" style="width: 1000px;">

    <?php if( $DB->QueryCount('SELECT COUNT(*) FROM `tbx_category`') == 0 ): ?>
     <div class="message-error">
       You will need to create at least one category before you can begin importing videos
     </div>
    <?php else: ?>

    <div class="header">Import Videos</div>

    <form method="post" action="index.php" enctype="multipart/form-data">

      <div style="position: relative;">
        <a href="docs/cp-video-import.html"><img src="images/help-32x32.png" title="Help" border="0" style="position: absolute; top: -22px; right: -10px;" target="_blank" /></a>
      </div>

      <div style="margin: 10px auto;  font-size: 130%; font-weight: bold; width: 50%; border: 1px dotted #0097FF;" class="text-center message-notice">
        <a href="http://www.jmbsoft.com/sponsors.php" target="_blank">Find Sponsors with Importable Videos</a>
      </div>

      <?php if( $errors ): ?>
      <div class="message-error" style="margin-bottom: 10px;">
        Please fix the following errors:

        <ul>
        <?php foreach( $errors as $error ): ?>
          <li> <?php echo $error; ?></li>
        <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <div class="field">
        <label>Source:</label>
        <span class="field-container">
          <select name="source">
            <?php
            $sources = array(Video_Import::SOURCE_CLIPBOARD, Video_Import::SOURCE_UPLOAD, Video_Import::SOURCE_URL);
            echo Form_Field::OptionsSimple($sources, Request::Get('source'));
            ?>
          </select>
        </span>
      </div>

      <div class="field">
        <label>Delimiter:</label>
        <span class="field-container"><input type="text" name="delimiter" style="width: 18px;" value="|" /></span>
      </div>


      <div class="field source source-Upload">
        <label>File:</label>
        <span class="field-container">
          <input type="file" name="<?php echo Video_Import::FIELD_UPLOAD; ?>" size="80" /><br />
          <span class="smallest">Select the text file that contains the import data</span>
        </span>
      </div>

      <div class="field source source-URL">
        <label>URL:</label>
        <span class="field-container">
          <input type="text" name="<?php echo Video_Import::FIELD_URL; ?>" size="110" /><br />
          <span class="smallest">Enter the URL to the text file that contains the import data</span>
        </span>
      </div>

      <div class="field source source-Clipboard">
        <label>Clipboard:</label>
        <span class="field-container">
          <textarea name="<?php echo Video_Import::FIELD_CLIPBOARD; ?>" rows="15" style="padding: 1px; width: 775px;" wrap="off"></textarea><br />
          <span class="smallest">Paste the import data from your clipboard</span>
        </span>
      </div>

      <div class="field">
        <label></label>
        <span class="field-container"><input type="submit" value="Next Step &gt;"></span>
      </div>

      <input type="hidden" name="r" value="tbxVideoImportAnalyze" />
    </form>

    <?php endif; //if( empty($categories) ): ?>

  </span>
</div>


<script language="JavaScript">
$(function()
{
    $('select[name="source"]')
    .change(function()
            {
                $('.source').hide();
                $('.source-'+$(this).val()).show()
            })
    .change();
});
</script>

<?php
require_once('cp-global-footer.php');
?>