$('#dialog-panel')
.bind('dialog-visible', function()
                        {
                            $('input.defaultvalue:visible')
                            .livequery(function()
                                       {
                                           $(this).defaultvalue({form: '#dialog-content form'});
                                       });

                            $('#dialog-content form')
                            .bind('reset', function()
                                           {
                                               $('#validators').empty();
                                               $('#validator-master img[src$=add-16x16.png]').trigger('click');
                                           });

                            $('#type')
                            .change(function()
                                    {
                                        $(this).val() == 'Select' ?
                                        $('#field-options').show() :
                                        $('#field-options').hide();
                                    })
                            .trigger('change');

                            $('#validator-master img[src$=add-16x16.png]')
                            .click(function()
                                   {
                                       $('#validator-master')
                                       .clone(true)
                                       .appendTo('#validators')
                                       .attr('id', '')
                                       .show();
                                   })
                            .trigger('click');

                            $('#validator-master img[src$=remove-16x16.png]')
                            .click(function()
                                   {
                                       if( $('#validators div').length == 1 )
                                       {
                                           alert('There must be at least one validator field');
                                           return;
                                       }

                                       $(this)
                                       .parent('div')
                                       .remove();
                                   });
                        });
