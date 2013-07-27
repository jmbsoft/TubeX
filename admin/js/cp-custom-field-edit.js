$('#dialog-panel')
.bind('dialog-visible', function()
                        {
                            $('#validators').empty();

                            if( validators.type.length < 1 )
                            {
                                $('#validator-master img[src$=add-16x16.png]').trigger('click');
                                return;
                            }

                            for( var i = 0; i < validators.type.length; i++ )
                            {
                                $('#validator-master img[src$=add-16x16.png]').trigger('click');
                                var $validator = $('#validators > div').get(i);

                                $('select option[value=' + validators.type[i] + ']', $validator).attr('selected', 'selected');
                                $('input:eq(0)', $validator).val(validators.message[i]);
                                $('input:eq(1)', $validator).val(validators.extras[i]);
                            }

                            setTimeout(function() { $('input[defaultvalue]:visible').trigger('focus').trigger('blur'); }, 100);
                        });