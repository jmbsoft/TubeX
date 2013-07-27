/*------------------------------------------------------------------#
# TubeX - Copyright © 2009 JMB Software, Inc. All Rights Reserved.  #
# This file may not be redistributed in whole or significant part.  #
# TubeX IS NOT FREE SOFTWARE                                        #
#-------------------------------------------------------------------#
# http://www.jmbsoft.com/           http://www.jmbsoft.com/license/ #
#------------------------------------------------------------------*/


jQuery.growl = {
    remove: function($notice)
            {
                $notice.animate({opacity: 'hide', height: 'hide'}, 'normal', 'swing', function() { $(this).remove() });
            },

    message: function(message, params)
             {
                 var defaults = {type: 'info',
                                 closer: false,
                                 timeout: 5000,
                                 pulsed: false,
                                 pulseTimes: 0,
                                 pulseStyleStart: {color: 'black'},
                                 pulseStyleEnd: {color: 'white'}};

                 var options = $.extend(defaults, params);

                 // Create the container div if it does not already exist
                 // Do this only once
                 if( $('#growl-container').length == 0 )
                 {
                     $('<div id="growl-container"></div>').appendTo('body');
                 }

                 var html = '<div class="growl-notice-container">' +
                            '<div class="growl-notice">' +
                            '<div class="growl-tl"></div>' +
                            '<div class="growl-tr"></div>' +
                            '<div class="growl-bl"></div>' +
                            '<div class="growl-br"></div>' +
                            '<div class="growl-t"></div>' +
                            '<div class="growl-b"></div>' +
                            '<div class="growl-l"></div>' +
                            '<div class="growl-r"></div>' +
                            '<div class="growl-text growl-text-'+options.type+'">' +
                            message +
                            '</div>' +
                            '</div>' +
                            '</div>';

                 // Append the new growl message to the container div
                 // Display with an animation
                 var $notice = $(html)
                               .appendTo('#growl-container')
                               .animate({opacity: 'show', height: 'show'}, 'normal', 'swing', function()
                                                                                              {
                                                                                                  /*for( i = 0; i < options.pulseTimes; i++)
                                                                                                  {
                                                                                                      $('.growl-text', this).animate(options.pulseStyleStart, 400).animate(options.pulseStyleEnd, 400);
                                                                                                  }*/
                                                                                              });

                 // If a timeout has been specified, setup to automatically remove the message
                 if( options.timeout > 0 )
                 {
                     setTimeout(function() { jQuery.growl.remove($notice); }, options.timeout);
                 }

                 $('.growl-tl', $notice).click(function() { jQuery.growl.remove($notice); });
             },

    // Shortcut to generate error messages
    error: function(message, errors)
           {
               if( typeof errors == 'object' )
               {
                   message += ':<ul><li>'+errors.join('</li><li>')+'</li></ul>';
               }

               jQuery.growl.message(message, {type: 'error',
                                              timeout: 10000,
                                              pulseTimes: 2,
                                              pulseStyleStart: {color: '#5a0000'},
                                              pulseStyleEnd: {color: 'red'}});
           },

    // Shortcut to generate warning messages
    warning: function(message, warnings)
             {
                 jQuery.growl.message(message, {type: 'warn',
                                                timeout: 10000,
                                                pulseTimes: 1,
                                                pulseStyleStart: {color: '#5a5a00'},
                                                pulseStyleEnd: {color: '#ffff00'}});
             }
};
