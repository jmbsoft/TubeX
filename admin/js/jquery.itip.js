/*------------------------------------------------------------------#
# TubeX - Copyright © 2009 JMB Software, Inc. All Rights Reserved.  #
# This file may not be redistributed in whole or significant part.  #
# TubeX IS NOT FREE SOFTWARE                                        #
#-------------------------------------------------------------------#
# http://www.jmbsoft.com/           http://www.jmbsoft.com/license/ #
#------------------------------------------------------------------*/


jQuery.fn.itip = function(params)
{
    var options = $.extend({marginTop: 8, marginLeft: 30, arrow: 'top'}, params);

    this.each(function()
              {
                  $(this)
                  .focus(function(e)
                         {
                             var $this = $(this);
                             var offset = $this.offset();
                             var height = $this.outerHeight();
                             var width = $this.outerWidth();
                             var css = {top: offset.top + height + options.marginTop + 'px', left: offset.left + options.marginLeft + 'px'};
                             var is_select = (this.tagName == 'SELECT');
                             var html = '<div class="tooltip">' +
                                         '<div class="tooltip-contents">' +
                                         '<div class="tooltip-arrow-'+ (is_select ? 'left' : 'top') +'"></div>' +
                                         $this.attr('tip').replace(/\\n/g, '<br />') +
                                         '</div>' +
                                         '</div>';

                             this.$itip = $(html).appendTo('body');

                             if( is_select )
                             {
                                 css = {top: offset.top + 'px', left: offset.left + width + options.marginTop + 'px'};
                             }

                             this.$itip.css(css).show();
                         })
                  .blur(function(e)
                        {
                            this.$itip.remove();
                        });
              });

    return this;
};