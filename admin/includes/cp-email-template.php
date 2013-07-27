<?php
if( !defined('TUBEX_CONTROL_PANEL') ) die('Invalid Access');

require_once('cp-global-header.php');
?>

<script language="JavaScript" type="text/javascript">
$(function()
{
    window.onbeforeunload = function() { return (tinyMCE.activeEditor.isDirty() ? 'The currently loaded template has not been saved' : undefined); };

    $('#template-form')
    .bind('form-pre-serialize', function(event, $form, options)
                                {
                                    tinyMCE.triggerSave();
                                })
    .ajaxForm({beforeSubmit: function()
                             {
                                 $.dim('#icon-save');
                             },
               success: function(data)
                        {
                            $.undim('#icon-save');

                            if( data.status == JSON.SUCCESS )
                            {
                                tinyMCE.activeEditor.undoManager.clear();
                                tinyMCE.activeEditor.isNotDirty = 1;
                                tinyMCE.activeEditor.startContent = tinyMCE.activeEditor.getContent({format : 'raw'});
                            }
                        }});

    $('div.explorer-template')
    .click(function()
           {
               var template = $(this).text();

               if( !tinyMCE.activeEditor.isDirty() || confirm('The currently loaded template has not been saved, are you sure you want to continue?') )
               {
                   $('#activity-loading').show();
                   $('#code-header, #template-form').hide();
                   $('#template, #template-subject').val('');
                   tinyMCE.activeEditor.setContent('');

                   $.ajax({data: 'r=tbxEmailTemplateLoad&template=' + escape(template),
                           success: function(data)
                                    {
                                        tinyMCE.activeEditor.setContent(data.t_message);
                                        tinyMCE.activeEditor.undoManager.clear();
                                        tinyMCE.activeEditor.isNotDirty = 1;
                                        tinyMCE.activeEditor.startContent = tinyMCE.activeEditor.getContent({format : 'raw'});
                                        $('#loaded-template').text(template);
                                        $('#template').val(template);
                                        $('#template-subject').val(data.t_subject);
                                        $('#activity-loading').hide();
                                        $('#code-header, #template-form').show();
                                        template.match(/global/) ? $('#subject-field').hide() : $('#subject-field').show();
                                        $(window).trigger('resize');
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
        <td valign="top" style="width: 20em; min-width: 20em;">
          <?php
          $templates = String::HtmlSpecialChars(Dir::ReadFiles(TEMPLATES_DIR, '~^email~'));
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

            <div id="subject-field" style="margin-bottom: 8px;">
              <div style="font-weight: bold;">Subject:</div>
              <input type="text" name="template_subject" id="template-subject" size="90" value="" />
            </div>

            <div style="font-weight: bold;">Message:</div>
            <textarea name="template_message" id="template-message"></textarea>

            <input type="hidden" name="template" id="template" value="" />
            <input type="hidden" name="r" value="tbxEmailTemplateSave" />
          </form>
        </td>
      </tr>
    </table>

  </span>
</div>

<!-- BEGIN TOOLBAR -->
<div id="toolbar">
  <div id="toolbar-content">
    <img title="Search and Replace" class="toolbar-icon" src="images/search-replace-32x32.png"  meta="{t: 'dialog', r: 'tbxEmailTemplateSearchReplaceShow'}" />
    <span class="toolbar-icon-separator"></span>
    <img title="Save" class="toolbar-custom-icon" src="images/save-32x32.png" id="icon-save" />
    <img title="Reload" class="toolbar-custom-icon" src="images/reload-32x32.png" id="icon-reload" />
    <span class="toolbar-icon-separator"></span>
    <a target="_blank" href="docs/templates-email.html"><img border="0" title="Documentation" class="toolbar-icon" src="images/help-32x32.png" /></a>
  </div>
</div>
<div class="toolbar-spacer"></div>
<!-- END TOOLBAR -->

<script language="JavaScript" type="text/javascript">
    tinyMCE.init({mode: 'exact',
                    elements: 'template-message',
                    content_css: 'css/admin.css',
                    skin : 'o2k7',
                    skin_variant : 'silver',
                    theme: 'advanced',
                    theme_advanced_toolbar_location: 'top',
                    theme_advanced_toolbar_align: 'left',
                    theme_advanced_buttons1: "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect",
                    theme_advanced_buttons2: "bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,code,|,forecolor,backcolor,|,hr,removeformat,visualaid,|,sub,sup,|,charmap",
                    theme_advanced_buttons3: null,
                    forced_root_block: '',
                    force_br_newlines: true,
                    width: '99%',
                    init_instance_callback: function()
                                            {
                                                $(window)
                                                .resize(function()
                                                        {
                                                            var wh = $(window).height();
                                                            var tbh = $('#toolbar').outerHeight();
                                                            var offset = $('div.explorer').offset();
                                                            var pad = 15;

                                                            $('div.explorer').css({height: wh - offset.top - tbh - pad + 'px'});

                                                            offset = $('table.mceLayout').offset();
                                                            var mceh = $('td.mceToolbar').outerHeight();

                                                            $('td.mceIframeContainer > iframe').css({height: wh - offset.top - tbh - pad - mceh + 'px'});
                                                        })
                                                .trigger('resize');
                                            }});
</script>

<?php
require_once('cp-global-footer.php');
?>