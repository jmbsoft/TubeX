/*------------------------------------------------------------------#
# TubeX - Copyright © 2009 JMB Software, Inc. All Rights Reserved.  #
# This file may not be redistributed in whole or significant part.  #
# TubeX IS NOT FREE SOFTWARE                                        #
#-------------------------------------------------------------------#
# http://www.jmbsoft.com/           http://www.jmbsoft.com/license/ #
#------------------------------------------------------------------*/

jQuery.fn.menu = function()
{
    return this.each(function()
                     {
                         if( $.browser.safari )
                         {
                             $('.menu-top-level > a').css({height: '1.45em'});
                         }

                         var offset = $.browser.mozilla ? 2 : 1;

                         $('.menu-top-level, .menu-more', this)
                         .hover(function()
                                {
                                    var css = (this.className == 'menu-more' ?
                                               {left: ($(this).width() - offset) + 'px', top: ($(this).position().top) - 1 + 'px'} :
                                               {top: $(this).outerHeight() + 'px'});

                                    $(this)
                                    .children('.menu-sub-level')
                                    .css(css)
                                    .show();
                                },
                                function()
                                {
                                    $(this)
                                    .children('.menu-sub-level')
                                    .hide();
                                });
                     });
};