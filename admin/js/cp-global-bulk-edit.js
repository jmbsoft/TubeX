var actions = {SET: 'Set',
               APPEND: 'Append',
               PREPEND: 'Prepend',
               ADD: 'Add',
               SUBTRACT: 'Subtract',
               INCREMENT: 'Increment',
               DECREMENT: 'Decrement',
               REPLACE: 'Replace',
               TRIM: 'Trim',
               CLEAR: 'Clear',
               TRUNCATE: 'Truncate',
               UPPERCASE_ALL: 'Uppercase All',
               UPPERCASE_FIRST: 'Uppercase First',
               LOWERCASE_ALL: 'Lowercase All',
               RAW_SQL: 'Raw SQL'};

$('#dialog-content form')
.ajaxForm({success: function(data)
                    {
                        dialogButtonEnable();
                        dialogSuccess(data);

                        if( data.status == JSON.SUCCESS )
                        {
                            $('#search-form').submit();
                        }
                    },
           beforeSubmit: function()
                         {
                             dialogButtonDisable();
                         }});

// Handle clicks on the + icon
$('img.update-add')
.click(function()
       {
           var $fields = $('#update-master').clone(true).appendTo('#dialog-panel > div').attr('id', '').show();

           $('.value', $fields).autocomplete({buddy: '.field'});

           $('select.field', $fields)
           .change(function()
                   {
                       var allowed = $('option:selected', this).attr('actions').split(',');
                       var $select = $(this).siblings('select[name="action[]"]');
                       $select.children().remove();

                       if( allowed[0][0] == '-' )
                       {
                           allowed[0] = allowed[0].substr(1);

                           var o = {};
                           for( i in allowed )
                           {
                               o[allowed[i]] = '';
                           }

                           for( action in actions )
                           {
                               if( !(action in o) )
                               {
                                   $select.append('<option value="'+actions[action]+'">'+actions[action]+'</option>');
                               }
                           }
                       }
                       else
                       {
                           for( i in allowed)
                           {
                               $select.append('<option value="'+actions[allowed[i]]+'">'+actions[allowed[i]]+'</option>');
                           }
                       }
                   })
           .trigger('change');

           // Re-center the dialog if it is extending off the bottom of the client area
           var offset = $('#dialog').offset();
           if( $(window).height() < $('#dialog').outerHeight() + offset.top )
           {
               $('#dialog').center('vertical', true);
           }
       })
.click();

// Handle clicks on the - icon
$('img.update-remove')
.click(function()
       {
           if( $('div.update-fields:not(#update-master)').length == 1 )
           {
               alert('There must be at least one update action defined');
               return;
           }

           $(this).parents('div.field').remove();
       });