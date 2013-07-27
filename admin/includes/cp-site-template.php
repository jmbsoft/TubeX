<?php
if( !defined('TUBEX_CONTROL_PANEL') ) die('Invalid Access');

require_once('cp-global-header.php');
?>

<style>
#template-code {
  width: 99%;
  border: 1px solid #afafaf;
  height: 5em;
}

.explorer {
  border: 1px solid #afafaf;
  height: 5em;
  overflow: auto;
}

.explorer-template {
  cursor: pointer;
  color: green;
  background: transparent url(images/template-16x16.png) no-repeat 10px 50%;
  padding-left: 30px;
  height: 2em;
  line-height: 2em;
}

.explorer-template:hover {
  background-color: #f3f9fd;
}

.explorer-template span {
  display: inline;
  text-decoration: underline;
  vertical-align: middle;
}
</style>

<script language="JavaScript" type="text/javascript">
$(function()
{
    var dirty = false;

    window.onbeforeunload = function() { return (dirty ? 'The currently loaded template has not been saved' : undefined); };

    $(window)
    .resize(function()
            {
                var wh = $(window).height();
                var tbh = $('#toolbar').outerHeight();
                var offset = $('div.explorer').offset();
                var pad = 15;

                $('#template-code, div.explorer').css({height: wh - offset.top - tbh - pad + 'px'});
            })
    .trigger('resize');

    $('#template-code')
    .livequery('change',
               function()
               {
                   dirty = true;
               });

    $('#template-form')
    .ajaxForm({beforeSubmit: function()
                             {
                                 $.dim('#icon-save');
                             },
               success: function()
                        {
                            dirty = false;
                            $.undim('#icon-save');
                        }});

    $('div.explorer-template')
    .click(function()
           {
               var template = $(this).text();

               if( !dirty || confirm('The currently loaded template has not been saved, are you sure you want to continue?') )
               {
                   $('#activity-loading').show();
                   $('#code-header, #template-form').hide();
                   $('#template').val('');
                   $('#template-code').remove();
                   dirty = false;

                   $.ajax({data: 'r=tbxSiteTemplateLoad&template=' + escape(template),
                           success: function(data)
                                    {
                                        $('<textarea name="template_code" id="template-code" wrap="off">'+data.code+'</textarea>')
                                        .prependTo('#template-form')
                                        .css({height: $('div.explorer').innerHeight() + 'px'});

                                        $('#loaded-template').text(template);
                                        $('#template').val(template);
                                        $('#activity-loading').hide();
                                        $('#code-header, #template-form').show();
                                    }});
               }
           });


    $('#icon-save')
    .click(function()
           {
               var template = $('#template').val();

               if( !template )
               {
                   alert('No template is currently loaded for editing');
                   return;
               }

               $('#template-form').submit();
           });


    $('#icon-reload')
    .click(function()
           {
               var template = $('#template').val();

               if( !template )
               {
                   alert('No template is currently loaded for editing');
                   return;
               }

               $('div.explorer-template:textmatch('+template+')').trigger('click');
           });
});
</script>

<div class="centerer">
  <span class="centerer" style="width: 90%;">

    <table width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td>
          <div class="header">Select Template</div>
        </td>
        <td style="padding-left: 10px;">
          <div class="header" id="code-header" style="display: none;">Template Code For <span id="loaded-template"></span></div>
        </td>
      </tr>
      <tr>
        <td valign="top" style="width: 20em;">
          <?php
          $templates = String::HtmlSpecialChars(Dir::ReadFiles(TEMPLATES_DIR, '~^(?!email).*?(\.tpl$|\.css$)~'));
          asort($templates);
          ?>
          <div class="explorer">
            <?php foreach( $templates as $template ): ?>
            <div class="explorer-template"><span><?php echo $template; ?></span></div>
            <?php endforeach; ?>
          </div>
        </td>
        <td valign="top" style="padding-left: 10px;">
          <div id="activity-loading" style="display: none;"><img src="images/activity-32x32.gif" style="vertical-align: middle;" /> Loading template...</div>
          <form method="post" action="ajax.php" style="display: none;" id="template-form">
            <textarea name="template_code" id="template-code" wrap="off"></textarea>
            <input type="hidden" name="template" id="template" value="" />
            <input type="hidden" name="r" value="tbxSiteTemplateSave" />
          </form>
        </td>
      </tr>
    </table>

  </span>
</div>

<!-- BEGIN TOOLBAR -->
<div id="toolbar">
  <div id="toolbar-content">
    <img title="Search and Replace" class="toolbar-icon" src="images/search-replace-32x32.png"  meta="{t: 'dialog', r: 'tbxSiteTemplateSearchReplaceShow'}" />
    <span class="toolbar-icon-separator"></span>
    <img title="Save" class="toolbar-custom-icon" src="images/save-32x32.png" id="icon-save" />
    <img title="Reload" class="toolbar-custom-icon" src="images/reload-32x32.png" id="icon-reload" />
    <span class="toolbar-icon-separator"></span>
    <a target="_blank" href="docs/templates-site.html"><img border="0" title="Documentation" class="toolbar-icon" src="images/help-32x32.png" /></a>
  </div>
</div>
<div class="toolbar-spacer"></div>
<!-- END TOOLBAR -->

<?php
require_once('cp-global-footer.php');
?>