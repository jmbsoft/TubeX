/*------------------------------------------------------------------#
# TubeX - Copyright © 2009 JMB Software, Inc. All Rights Reserved.  #
# This file may not be redistributed in whole or significant part.  #
# TubeX IS NOT FREE SOFTWARE                                        #
#-------------------------------------------------------------------#
# http://www.jmbsoft.com/           http://www.jmbsoft.com/license/ #
#------------------------------------------------------------------*/

// Globals
var JSON = {FAILURE: 0,
            SUCCESS: 1,
            LOGOUT: 2,
            ERROR: 3};

var EFFECT_DURATION = 900;
var EFFECT_DURATION_SHORT = 250;

var ACTIVITY16X16 = 'images/activity-16x16.gif';
var ACTIVITY22X22 = 'images/activity-22x22.gif';
var ACTIVITY30X30 = 'images/activity-30x30.gif';
var ACTIVITY32X32 = 'images/activity-32x32.gif';



// Handle Firebug in unsupported browsers
if( !console )
{
/*<REMOVE_TAG>
    var _empty_func = function() {};
    var console = {log: _empty_func,
                   dir: _empty_func,
                   debug: _empty_func,
                   warn: _empty_func,
                   error: _empty_func,
                   assert: _empty_func,
                   trace: _empty_func};
</REMOVE_TAG>*/

//<REMOVE_SECTION>
    var _alert_func = function()
                      {
                          for( var i = 0; i < arguments.length; i++ )
                          {
                              alert(arguments[i].toString());
                          }
                      };
    var console = {log: _alert_func,
                   dir: _alert_func};
//</REMOVE_SECTION>
}



/**
* Startup function
*/
$(function()
{
    // Initialize menu
    $('#menu').menu();

    // Initialize global AJAX settings
    $.ajaxSetup({url: 'ajax.php', dataType: 'json', type: 'post', cache: false, timeout: 0});
    $(document).ajaxSuccess(globalAjaxSuccess);
    $(document).ajaxError(globalAjaxError);

    // Initialize search form
    $.searchform();

    // Initialize selectables
    $('#toolbar-select-all').selectable('td.selectable');
    $('td.selectable')
    .livequery(function()
               {
                   $('#toolbar-select-all').selectable('attach', this);
               },
               function ()
               {
                   $('#toolbar-select-all').removeClass('selectable-master-selected').selectable('detach', this);
               });

    // Function icons
    $('img.toolbar-icon, img.item-icon, .menu-action').livequery('click', iconHandler);
    $('img.toolbar-top').click(function() { window.scrollTo(0,0); });

    // Datepicker input fields
    $('input.datepicker').livequery(function() { $(this).calendar({notime: true}); }, function() {});
    $('input.datetimepicker').livequery(function() { $(this).calendar(); }, function() {});

    // Checkboxes
    $('div.checkbox').livequery(function() { $(this).checkbox(); }, function() {});

    // Autocomplete
    $('input[acomplete]').livequery(function() { $(this).autocomplete(); }, function() { $(this).unautocomplete(); });

    // Dialog links
    $('a.dialog')
    .click(function()
           {
               var settings = eval('('+ $(this).attr('meta') +')');
               $.lightbox();
               $.ajax({data: 'r=' + settings.r,
                       success: function(data)
                                {
                                    $.dialog('show', {content: data.html});
                                }});
               return false;
           });
});



/**
* Display video player popup window
*/
function videoPlayerPopup()
{
    var id = $(this).attr('videoid'),
        w = 640,
        h = 520,
        winl = (screen.width - w) / 2,
        wint = (screen.height - h) / 2;

    window.open('index.php?r=tbxVideoPlayer&video_id=' + id, 'videoplayer', 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars=no,toolbar=no');
}



/**
* Handle clicks on toolbar icons and individual item icons
*/
function iconHandler()
{
    var settings = eval('('+ $(this).attr('meta') +')');
    var selected = getSelected(this);

    if( !settings )
    {
        return;
    }

    switch(settings.t)
    {
        case 'link':
            window.location = settings.u;
            break;

        case 'action':
            if( settings.confirm == false || confirmation($(this).attr('title').toLowerCase(), selected) )
            {
                $.dim(this);
                $.ajax({data: 'r=' + settings.r + selected.post + (settings.f ? '&' + $(this).siblings(settings.f).attr('name') + '=' + $(this).siblings(settings.f).val() : ''),
                        icon: this,
                        success: function(data)
                                 {
                                     if( settings.onsuccess )
                                     {
                                         settings.onsuccess(data);
                                     }
                                 },
                        complete: function()
                                  {
                                      $.undim(this.icon);
                                  }});
            }
            break;

        case 'dialog':
            $.lightbox();
            $.ajax({data: 'r=' + settings.r + selected.post,
                    success: function(data)
                             {
                                 $.dialog('show', {content: data.html});
                             }});
            break;

        case 'custom':
            return;

        default:
            $.ajax({data: 'r=' + settings.r});
            break;

    }

    return false;
}



/**
* Display confirmation based on number of items selected
*/
function confirmation(action, selected)
{
    if( selected.amount == undefined )
    {
        return true;
    }

    if( item_config.text_lower == 'category' && action == 'delete' && !confirm('NOTICE: Deleting a category will also delete all of the videos in that category') )
    {
        return;
    }

    switch(selected.amount)
    {
        case 0:
            return confirm('Are you sure you want to ' + action + ' all matching ' + item_config.text_lower_plural + '?');

        case 1:
            return confirm('Are you sure you want to ' + action + ' this ' + item_config.text_lower + '?');

        default:
            return confirm('Are you sure you want to ' + action + ' the ' + selected.amount + ' selected ' + item_config.text_lower_plural + '?');
    }
}



/**
* Get selected search results
*/
function getSelected(icon)
{
    if( $(icon).hasClass('item-icon') )
    {
        var id = $(icon).parents('tr.search-result').attr('id');
        return {amount: 1, post: '&id=' + id  + '&search=' + escape('selected=true&search_term=' + id)}
    }
    else
    {
        if( $('#toolbar-select-all').length )
        {
            var selected = $('#toolbar-select-all').selectable('get-selected-ids');
            var data = {amount: selected.length, post: '&search=' + escape('selected=true&search_term=' + selected.join(','))};

            if( selected.length < 1 )
            {
                data.post = '&search=' + escape($('#search-form').formSerialize());
            }

            return data;
        }

        return {post: ''}
    }
}



/**
* Global callback for all AJAX requests that finish successfully (200 OK)
*/
function globalAjaxSuccess(e, xhr, settings, data)
{
    if( (typeof data == 'string' && data.match(/"status":2/)) || data.status == JSON.LOGOUT )
    {
        $('.autocomplete-results').hide();
        $.dialog('hide');
        $.modalLogout();
        return;
    }

    if( data.status == JSON.ERROR )
    {
        $('.autocomplete-results').hide();
        $.dialog('show',
                 {content: '<div id="dialog-header" class="ui-widget-header ui-corner-all">'+
                           '<div id="dialog-close"></div>' +
                           'Alert' +
                           '</div>' +
                           '<div id="dialog-panel">' +
                           '<div style="padding: 8px;">' +
                           '<div class="dialog-alert">' +
                           data.message +
                           '</div>' +
                           '</div>' +
                           '</div>' +
                           '<div id="dialog-buttons">' +
                           '<input type="button" id="dialog-button-cancel" value="OK" style="margin-left: 10px;" />' +
                           '</div>'});
        return;
    }

    if( data.status == JSON.FAILURE )
    {
        $.growl.error(data.message + (typeof data.errors == 'object' ? ':<ul><li>'+data.errors.join('</li><li>')+'</li></ul>' : ''));
    }
    else if( data.message )
    {
        $.growl.message(data.message);
    }

    if( data.js )
    {
        $('head').append('<script language="JavaScipt" type="text/javascript">' + data.js + '<'+'/script>');
    }

    if( data.eval )
    {
        eval(data.eval);
    }
}



/**
* Global callback for all AJAX requests that do not complete successfully
*/
function globalAjaxError(e, xhr, text, exception)
{
    var message = 'Ajax request failed!<br />';
    var responseText = xhr.responseText.replace(/\r\n|\r|\n/gi, '<br />').replace(/^(<br ?\/?>)+|(<br ?\/?>)+$/gi, '');

    switch(text)
    {
        case 'error':
            if( xhr.status == 0 && responseText.match(/Function argument was missing/) )
            {
                message += 'Unable to complete the ajax request.  This may indicate that you have exceeded the maximum allowed upload size';
            }
            else
            {
                message += 'HTTP Status: ' + xhr.status + ' ' + xhr.statusText + '<br /><div class="growl-overflow">' + responseText + '</div>';
            }
            break;

        case 'parsererror':
            message += 'Unable to parse return data as JSON<br /><div class="growl-overflow">' + responseText + '</div>';
            break;

        default:
            if( exception )
            {
                message += 'Exception: ' + exception.message + '<br />';
            }
            break;
    }

    if( $.dialog('is_visible') )
    {
        dialogButtonEnable();
    }

    $.growl.error(message);
}


/**
* Handle successful response when submitting a dialog
*/
function dialogSuccess(data, $form, clear)
{
    if( data.status == JSON.FAILURE )
    {
        return false;
    }
    else
    {
        if( clear )
        {
            $form.resetForm();
        }
        return true;
    }
}


/**
* Disable the buttons on a dialog
*/
function dialogButtonDisable()
{
    $('#dialog-buttons input').attr('disabled', 'disabled');
    $('#dialog-buttons img').show();
}



/**
* Enable the buttons on a dialog
*/
function dialogButtonEnable()
{
    $('#dialog-buttons input').removeAttr('disabled');
    $('#dialog-buttons img').hide();
}


/**
* Format a number for output
*/
function number_format(number, decimals, dec_point, thousands_sep)
{
    var n = number, prec = decimals;
    n = !isFinite(+n) ? 0 : +n;
    prec = !isFinite(+prec) ? 0 : Math.abs(prec);
    var sep = (typeof thousands_sep == "undefined") ? ',' : thousands_sep;
    var dec = (typeof dec_point == "undefined") ? '.' : dec_point;

    var s = (prec > 0) ? n.toFixed(prec) : Math.round(n).toFixed(prec); //fix for IE parseFloat(0.55).toFixed(0) = 0;

    var abs = Math.abs(n).toFixed(prec);
    var _, i;

    if( abs >= 1000 )
    {
        _ = abs.split(/\D/);
        i = _[0].length % 3 || 3;

        _[0] = s.slice(0,i + (n < 0)) +
              _[0].slice(i).replace(/(\d{3})/g, sep+'$1');

        s = _.join(dec);
    }
    else
    {
        s = s.replace('.', dec);
    }

    return s;
}


/**
* increment(), decrement(), setVal(), center(), lightbox(), dialog() functions
*/
(function($)
{
    $.fn.increment = function(selector)
    {
        return this.each(function()
                         {
                            var current = parseInt($(selector, this).val());
                            $(selector, this).val(++current);
                         });
    };

    $.fn.decrement = function(selector)
    {
        return this.each(function()
                         {
                            var current = parseInt($(selector, this).val());
                            $(selector, this).val(--current);
                         });
    };

    $.fn.decrementHTML = function(amount)
    {
        if( amount == undefined )
        {
            amount = 1;
        }

        return this.each(function()
                         {
                            var current = parseInt($(this).text().replace(new RegExp('\\'+config_thousands_sep), '').replace(new RegExp('\\'+config_dec_point), '.'));
                            $(this).html(number_format(current - amount, 0, config_dec_point, config_thousands_sep));
                         });
    };

    $.fn.setVal = function(selector, value)
    {
        return this.each(function()
                         {
                            $(selector, this).val(value);
                         });
    };

    $.fn.center = function(direction, animate)
    {
        var w = $(window).width();
        var h = $(window).height();
        var sl = $(document).scrollLeft();
        var st = $(document).scrollTop();

        return this.each(function()
                         {
                             var oh = $(this).outerHeight();
                             var ow = $(this).outerWidth();
                             var l = Math.max(0, ((w - ow)/2)+sl);
                             var t = Math.max(0, ((h - oh)/2)+st);

                             switch(direction)
                             {
                                 case 'vertical':
                                    if( animate )
                                        $(this).animate({top: t + 'px'}, 'slow', 'swing');
                                    else
                                        $(this).css({top: t + 'px'});
                                    break;

                                 case 'horizontal':
                                    if( animate )
                                        $(this).animate({left: l + 'px'}, 'slow', 'swing');
                                    else
                                        $(this).css({left: l + 'px'});
                                    break;

                                 default:
                                    $(this).css({top: t + 'px', left: l + 'px'});
                                    break;
                             }
                         });
    };


    // Overlay a div to prevent access to items below
    $.fn.overlayOn = function(covered)
    {
        return this.each(function()
                         {
                             var $div = $(this);
                             var $covered = $(covered);
                             var offset = $covered.position();
                             var height = $covered.outerHeight();
                             var width = $covered.outerWidth();

                             $div
                             .css({top: offset.top + 'px', left: offset.left + 'px', width: width + 'px', height: height + 'px', lineHeight: height + 'px'});
                         });
    };


    // Dim an icon by displaying activity indicator over top
    $.dim = function(icon)
    {
        $(icon).each(function()
                     {
                         var $icon = $(this);
                         var is_toolbar = $icon.parent().attr('id').indexOf('toolbar') != -1;
                         var offset = $icon.position();
                         var activity_size = $icon.height() + 'x' + $icon.width();

                         $icon
                         .css({visibility: 'hidden'})
                         .data('activity', $('<img src="images/activity-'+activity_size+'.gif" style="position: absolute;"' + (is_toolbar ? ' class="toolbar-icon"' : '') + '>')
                                           .appendTo($icon.parent())
                                           .css({top: offset.top + 'px', left: offset.left + 'px'}));
                     });
    };


    // Undim an icon that had been previously dimmed
    $.undim = function(icon)
    {
        $(icon).each(function()
                    {
                        var $orig = $(this).css({visibility: 'visible'}).data('activity');

                        if( $orig )
                        {
                            $orig.remove();
                        }
                    });
    };


    // fnc can be "show" or "hide"
    $.lightbox = function(fnc, opts)
    {
        switch(fnc)
        {
            case 'activity-stop':
                 $('#lightbox-activity').hide();
                 break;

            case 'activity-start':
                $('#lightbox-activity').show();
                break;

            case 'hide':
            case 'close':
            case 'destroy':
            case 'remove':
                $(window)
                .unbind('resize', lightboxWindowResize)
                .unbind('scroll', lightboxWindowScroll);

                $('#lightbox')
                .remove();
                break;

            default:
                if( $('#lightbox').length < 1 )
                {
                    $('body')
                    .append('<div id="lightbox" style="display: none;"><img src="'+ACTIVITY32X32+'" id="lightbox-activity" /></div>');

                    $(window)
                    .resize(lightboxWindowResize)
                    .scroll(lightboxWindowScroll)
                    .triggerHandler('resize');
                }
                break;
        }
    };

    $.dialog = function(fnc, opts)
    {
        switch(fnc)
        {
            case 'visible':
            case 'isvisible':
            case 'is_visible':
                return $('#dialog:visisble').length > 0;
                break;

            case 'hide':
            case 'close':
            case 'destroy':
            case 'remove':
                $(window).unbind('resize');
                $(window).unbind('scroll');
                $('#dialog').draggable('destroy').hide();
                $('#dialog-close, #dialog-button-cancel').unbind('click');
                $('#dialog-content').empty();
                $.lightbox('hide');
                $('#dialog').trigger('closing');
                break;

            default:
                $.lightbox();

                if( opts.content )
                {
                    $(opts.content).appendTo('#dialog-content');
                }

                $('#dialog-close, #dialog-button-cancel').click(function() { $.dialog('close'); });
                $('#dialog').draggable({handle: '#dialog-header', containment: 'document'}).css({top: '-9999px', left: '-9999px'}).show();
                $('#dialog-panel').css({maxHeight: $(window).height() - 200 + 'px'});
                $(window).resize(function() { $('#dialog-panel').css({maxHeight: $(window).height() - 200 + 'px'}); $('#dialog').center(); }).triggerHandler('resize');
                $(window).scroll(function() { $('#dialog').center(); });
                $.lightbox('activity-stop');

                $('#dialog-panel').trigger('dialog-visible');
                break;
        }
    };

    $.modalLogout = function(options)
    {
        $.lightbox();
        $('#modal-logout').show().center();
        $.lightbox('activity-stop');
    };

    function lightboxWindowScroll(e)
    {
        $('#lightbox-activity').center();
    }

    function lightboxWindowResize(e)
    {
        $('#lightbox').hide();
        $('#lightbox').css({width: $(document).width() + 'px', height: $(document).height() + 'px'}).show();
        $('#lightbox-activity').center();
    }
})(jQuery);