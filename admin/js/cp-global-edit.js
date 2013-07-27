$('#dialog-content form').ajaxForm({success: function(data)
                                             {
                                                 dialogButtonEnable();
                                                 dialogSuccess(data, $('#dialog-content form'), false);
                                                 $('#search-results-container #' + data.id).replaceWith(data.html);
                                             },
                                    beforeSerialize: function($form, options)
                                                     {
                                                         $('input[name="detailed"]', $form).val($('#cb-detailed').val());
                                                     },
                                    beforeSubmit: function()
                                                  {
                                                      dialogButtonDisable();
                                                  }});