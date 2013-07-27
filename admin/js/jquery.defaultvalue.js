/*------------------------------------------------------------------#
# TubeX - Copyright © 2009 JMB Software, Inc. All Rights Reserved.  #
# This file may not be redistributed in whole or significant part.  #
# TubeX IS NOT FREE SOFTWARE                                        #
#-------------------------------------------------------------------#
# http://www.jmbsoft.com/           http://www.jmbsoft.com/license/ #
#------------------------------------------------------------------*/

jQuery.fn.defaultvalue = function(options)
{
    this.each(function()
              {
                  var field = this;
                  var defaultval = $(this).attr('defaultvalue');

                  if( this.value == '' )
                  {
                      this.value == defaultval;
                  }

                  $(this)
                  .focus(function()
                         {
                             if( this.value == defaultval )
                             {
                                 this.value = '';
                             }
                             $(this).removeClass('defaultvalue');
                         })
                  .blur(function()
                        {
                            if( this.value == defaultval || this.value == '' )
                            {
                                $(this).addClass('defaultvalue').val(defaultval);
                            }
                        });

                  var $form = $(this).parents('form');

                  if( options && options.form )
                  {
                      $form = $(options.form);
                  }

                  $form
                  .each(function()
                        {
                            $(this)
                            .bind('form-pre-serialize', function(e)
                                                        {
                                                            if( field.value == defaultval )
                                                            {
                                                                field.value = '';
                                                            }
                                                        });

                            $(this)
                            .bind('form-submit-notify', function()
                                                        {
                                                            $('input[defaultvalue]', this).trigger('blur');
                                                        });
                        });
            });

    return this;
};