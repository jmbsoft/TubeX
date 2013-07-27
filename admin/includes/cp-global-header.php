<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title>TubeX Administration</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="css/admin.css" />
    <script type="text/javascript" src="js/tiny_mce/tiny_mce.js"></script>
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery.ui.js"></script>
    <script type="text/javascript" src="js/control-panel.js"></script>
    <script type="text/javascript" src="js/jquery.autocomplete.js"></script>
    <script type="text/javascript" src="js/jquery.calendar.js"></script>
    <script type="text/javascript" src="js/jquery.checkbox.js"></script>
    <script type="text/javascript" src="js/jquery.defaultvalue.js"></script>
    <script type="text/javascript" src="js/jquery.form.js"></script>
    <script type="text/javascript" src="js/jquery.growl.js"></script>
    <script type="text/javascript" src="js/jquery.itip.js"></script>
    <script type="text/javascript" src="js/jquery.livequery.js"></script>
    <script type="text/javascript" src="js/jquery.menu.js"></script>
    <script type="text/javascript" src="js/jquery.metadata.js"></script>
    <script type="text/javascript" src="js/jquery.searchform.js"></script>
    <script type="text/javascript" src="js/jquery.selectable.js"></script>
    <?php if( isset($js_file) && file_exists($js_file) ): ?>
    <script type="text/javascript" src="<?php echo $js_file; ?>"></script>
    <?php endif; ?>
    <script language="JavaScript" type="text/javascript">
    var config_dec_point = '<?php echo Config::Get('dec_point'); ?>';
    var config_thousands_sep = '<?php echo Config::Get('thousands_sep'); ?>';
    $(function()
    {
        <?php Growl::OutputJavascript(); ?>
    });
    </script>
  </head>
  <body>

    <div id="header-bar">
      <?php if( Authenticate::Authenticated() ): ?>
      <a href="index.php?r=tbxLogout" onclick="return confirm('Are you sure you want to log out?');"><img src="images/logout-87x26.png" alt="Log Out" id="logout" border="0" /></a>
      <?php endif; ?>
      <a href="index.php"><img src="images/tubex-174x41.png" alt="TubeX" border="0" /></a>
    </div>

    <?php
    if( Authenticate::Authenticated() )
    {
        require_once('cp-global-menu.php');
    }
    ?>