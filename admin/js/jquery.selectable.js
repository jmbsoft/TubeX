/*------------------------------------------------------------------#
# TubeX - Copyright © 2009 JMB Software, Inc. All Rights Reserved.  #
# This file may not be redistributed in whole or significant part.  #
# TubeX IS NOT FREE SOFTWARE                                        #
#-------------------------------------------------------------------#
# http://www.jmbsoft.com/           http://www.jmbsoft.com/license/ #
#------------------------------------------------------------------*/

(function($)
{
    $.fn.selectable = function(fnc)
    {
        switch(fnc)
        {
            case 'attach':
                var $master = this;
                $(arguments[1]).click(function()
                                      {
                                          $(this).toggleClass('selectable-selected');

                                          if( $master.data('members').length == $master.data('members').filter('.selectable-selected').length )
                                          {
                                              $master.addClass('selectable-master-selected');
                                          }
                                          else
                                          {
                                              $master.removeClass('selectable-master-selected');
                                          }
                                      });

                $(this).data('members', $(this).data('members').add(arguments[1]));
                break;

            case 'detach':
                $(arguments[1]).unbind('click');
                $(this).data('members', $(this).data('members').not(arguments[1]));
                break;

            case 'select-all':
                return $(this).data('members').addClass('selectable-selected');
                break;

            case 'deselect-all':
                return $(this).data('members').removeClass('selectable-selected');
                break;

            case 'get-selected':
                return $(this).data('members').filter('.selectable-selected');
                break;

            case 'get-selected-ids':
                var ids = new Array();

                $(this)
                .data('members')
                .filter('.selectable-selected')
                .each(function()
                      {
                          ids.push($(this).parents('tr.search-result').attr('id'));
                      });

                return ids;
                break;


            default:
                this.each(function()
                          {
                              $(this)
                              .data('members', $(fnc))
                              .click(function(e)
                                     {
                                         if( $(this).toggleClass('selectable-master-selected').hasClass('selectable-master-selected') )
                                         {
                                             $(this).selectable('select-all');
                                         }
                                         else
                                         {
                                             $(this).selectable('deselect-all');
                                         }

                                         e.preventDefault();
                                         e.stopPropagation();

                                         return false;
                                     });
                          });
        }

        return this;
    };

})(jQuery);