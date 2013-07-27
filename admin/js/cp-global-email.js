$('#dialog-content form')
.bind('form-pre-serialize', function(event, $form, options) { tinyMCE.triggerSave(); })
.ajaxForm({success: function(data)
                    {
                        dialogButtonEnable();
                        dialogSuccess(data);
                    },
           beforeSubmit: function()
                         {
                             dialogButtonDisable();
                         }});

var interval = setInterval(function()
                          {
                              if( $('#dialog-content #message').is(':visible') )
                              {
                                  clearInterval(interval);

                                  tinyMCE.init({mode: 'exact',
                                                elements: 'message',
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
                                                force_br_newlines: true});
                              }
                          },
                          200);