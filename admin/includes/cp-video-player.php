<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title><?php echo $video['title']; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="css/admin.css" />
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
    <script language="Javascript" type="text/javascript">
    var player;
    var playertype;
    var types = {flash: 'flash',
                 qt: 'qt',
                 wmv: 'wmv',
                 wmp: 'wmp'};

    $(function()
    {
        $('a')
        .click(function()
               {
                   switch(playertype)
                   {
                       case types.flash:
                       case types.wmv:
                           player.sendEvent('STOP', true);
                           player.sendEvent('LOAD', $(this).attr('href'));
                           break;

                       case types.qt:
                           player.Stop();
                           player.SetURL($(this).attr('href'));
                           break;

                       case types.wmp:
                            player.controls.stop();
                            player.URL = $(this).attr('href');
                            player.controls.play();
                           break;
                   }

                   return false;
               });
    });
    </script>
  </head>
  <body>

  <?php
  foreach( $clips as $clip )
  {
      if( $clip['type'] == 'Embed' )
      {
          echo $clip['clip'];
      }
      else if( preg_match('~\.' . FLASH_EXTENSIONS . '$~i', $clip['clip']) )
      {
  ?>
    <object id="flvplayerax" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" name="player" width="640" height="480">
      <param name="movie" value="player/player.swf" />
      <param name="allowfullscreen" value="true" />
      <param name="allowscriptaccess" value="always" />
      <param name="flashvars" value="autostart=true&file=<?php echo urlencode($clip['clip']); ?>" />
      <object id="flvplayer" name="flvplayer" type="application/x-shockwave-flash" data="player/player.swf" width="640" height="480">
        <param name="movie" value="player/player.swf" />
        <param name="allowfullscreen" value="true" />
        <param name="allowscriptaccess" value="always" />
        <param name="flashvars" value="autostart=true&file=<?php echo urlencode($clip['clip']); ?>" />
      </object>
    </object>
    <script language="JavaScript" type="text/javascript">
    player = document.getElementById($.browser.msie ? 'flvplayerax' : 'flvplayer');
    playertype = types.flash;
    </script>
  <?php
      }
      else if( preg_match('~\.' . QT_EXTENSIONS . '$~i', $clip['clip']) )
      {
  ?>
    <object id="qtplayerax" width="640" height="480" codebase="http://www.apple.com/qtactivex/qtplugin.cab#version=7,3,0,0" classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B">
      <param name="src" value="<?php echo htmlspecialchars($clip['clip']); ?>"/>
      <param name="autostart" value="true"/>
      <param name="saveembedtags" value="true"/>
      <param name="postdomevents" value="true"/>
      <param name="EnableJavaScript" value="true"/>
      <param name="scale" value="aspect"/>

      <object id="qtplayer" name="qtplayer" width="640" height="480" type="video/quicktime" data="<?php echo htmlspecialchars($clip['clip']); ?>">
        <param name="autostart" value="true"/>
        <param name="saveembedtags" value="true"/>
        <param name="postdomevents" value="true"/>
        <param name="EnableJavaScript" value="true"/>
        <param name="scale" value="aspect"/>
      </object>
    </object>
    <script language="JavaScript" type="text/javascript">
    player = document.getElementById($.browser.msie ? 'qtplayerax' : 'qtplayer');
    playertype = types.qt;
    </script>
  <?php
      }
      else if( preg_match('~\.' . WM_EXTENSIONS . '$~i', $clip['clip']) )
      {
  ?>
    <div name="wmvsilverplayer" id="wmvsilverplayer"></div>
    <script type='text/javascript' src="player/silverlight.js"></script>
    <script type='text/javascript' src="player/wmvplayer.js"></script>
    <script type="text/javascript">
        playertype = types.wmv;
        player = new jeroenwijering.Player(document.getElementById('wmvsilverplayer'),
                                           'player/wmvplayer.xaml',
                                           {file: '<?php echo $clip['clip']; ?>',
                                           height: '480',
                                           width: '640',
                                           autostart: 'true'});
    </script>
  <?php
      }
      else
      {
  ?>
    <object id="wmplayerax" classid="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6" type="video/x-ms-wmv" width="640" height="480">
      <param name="AutoStart" value="true">
      <param name="ShowTracker" value="true">
      <param name="ShowControls" value="true">
      <param name="ShowGotoBar" value="false">
      <param name="ShowDisplay" value="false">
      <param name="ShowStatusBar" value="false">
      <param name="AutoSize" value="false">
      <param name="StretchToFit" value="true">
      <param name="URL" value="<?php echo htmlspecialchars($clip['clip']); ?>">
      <object id="wmplayer" name="wmplayer" data="<?php echo htmlspecialchars($clip['clip']); ?>" type="video/x-ms-wmv" width="640" height="480">
        <param name="AutoStart" value="true">
        <param name="ShowTracker" value="true">
        <param name="ShowControls" value="true">
        <param name="ShowGotoBar" value="false">
        <param name="ShowDisplay" value="false">
        <param name="ShowStatusBar" value="false">
        <param name="AutoSize" value="false">
        <param name="StretchToFit" value="true">
        <param name="URL" value="<?php echo htmlspecialchars($clip['clip']); ?>">
      </object>
    </object>
    <script language="JavaScript" type="text/javascript">
    player = document.getElementById($.browser.msie ? 'wmplayerax' : 'wmplayer');
    playertype = types.wmp;
    </script>
  <?php
      }

      break;
  }
  ?>

  <div style="text-align: center; font-weight: bold; font-size: 105%; height: 15px;">
    <span style="vertical-align: middle;">
  <?php
  $clipcount = 1;
  foreach( $clips as $clip )
  {
      if( $clip['type'] != 'Embed' )
      {
          echo "<a href=\"{$clip['clip']}\">CLIP #$clipcount</a> &nbsp; ";
      }
      $clipcount++;
  }
  ?>
    </span>
  </div>

  </body>
</html>
