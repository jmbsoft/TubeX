$('#dialog-content form').ajaxForm({success: function(data)
                                             {
                                                 dialogButtonEnable();
                                                 dialogSuccess(data, $('#dialog-content form'), true);

                                                 if( data.status == JSON.SUCCESS )
                                                 {
                                                    $('#search-form').submit();
                                                 }
                                             },
                                    beforeSubmit: function()
                                                  {
                                                      dialogButtonDisable();
                                                  }});