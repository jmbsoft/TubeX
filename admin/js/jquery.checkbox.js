/*------------------------------------------------------------------#
# TubeX - Copyright © 2009 JMB Software, Inc. All Rights Reserved.  #
# This file may not be redistributed in whole or significant part.  #
# TubeX IS NOT FREE SOFTWARE                                        #
#-------------------------------------------------------------------#
# http://www.jmbsoft.com/           http://www.jmbsoft.com/license/ #
#------------------------------------------------------------------*/

jQuery.fn.checkbox = function()
{
    return this.each(function()
                     {
                         var $input = $('input[type=hidden]', this);

                         if( parseInt($input.val()) )
                         {
                             $(this).addClass('checked');
                         }

                         $(this)
                         .mousedown(function()
                                    {
                                        var value = parseInt($input.val());

                                        $input.val(value ? 0 : 1).trigger('change');
                                        $(this).toggleClass('checked');

                                        return false;
                                    })
                         .bind('selectstart', function() { return false; });
                     });
};