/*------------------------------------------------------------------#
# TubeX - Copyright © 2009 JMB Software, Inc. All Rights Reserved.  #
# This file may not be redistributed in whole or significant part.  #
# TubeX IS NOT FREE SOFTWARE                                        #
#-------------------------------------------------------------------#
# http://www.jmbsoft.com/           http://www.jmbsoft.com/license/ #
#------------------------------------------------------------------*/

(function($)
{
    var calendar_initialized = false;
    var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    var days = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
    var one_minute = 60 * 1000;
    var one_hour = one_minute * 60;
    var one_day = one_hour * 24;
    var one_week = one_day * 7;
    var buddy = null;
    var hovering = false;

    // Plugin definition
    $.fn.calendar = function(options)
    {
        var global_opts = $.extend({}, $.fn.calendar.defaults, options);

        if( !calendar_initialized )
        {
            initializeCalendar();
            calendar_initialized = true;
        }

        this.each(function()
                  {
                      this.opts = global_opts;
                      $(this).after('&nbsp;<img src="images/calendar-22x22.png" border="0" class="calendar-icon clickable" />');
                      $(this).bind('keydown', function(e) { if( e.keyCode == 9 ) { hideCalendar(); }});
                  });

        $('.calendar-icon').click($.fn.calendar.toggleCalendar);

        return this;
    };


    // Toggle visibility of the calendar
    $.fn.calendar.toggleCalendar = function()
    {
        if( buddy )
        {
            hideCalendar();
        }
        else
        {
            $(this).prev('input[class=datepicker], input[class=datetimepicker]').each(showCalendar);
        }
    };


    // Show the calendar
    function showCalendar()
    {
        // Don't redisplay
        if( this == buddy )
        {
            return;
        }

        buddy = this;
        var pos = $(this).offset();
        var height = $(this).outerHeight();

        // See if the input field contains a date already
        var input = $(this).val();
        var matches = null;
        var selected = null;
        if( (matches = input.match(/(\d\d\d\d)-(\d\d)-(\d\d)/)) != null )
        {
            $('#cal_year option[value='+matches[1]+']').attr('selected', 'selected');
            $('#cal_month option[value='+matches[2]+']').attr('selected', 'selected');

            selected = matches[1] + '-' + matches[2] + '-' + matches[3];

            if( !this.opts.notime && (matches = input.match(/(\d\d):(\d\d):(\d\d)/)) != null )
            {
                $('#cal_hour option[value='+matches[1]+']').attr('selected', 'selected');
                $('#cal_minute option[value='+matches[2]+']').attr('selected', 'selected');
                $('#cal_second option[value='+matches[3]+']').attr('selected', 'selected');
            }
        }
        else
        {
            setToday();
        }

        updateCalendar();

        $('#cal')
        .css({top: pos.top + height + 'px', left: pos.left + 'px'})
        .show();

        if( this.opts.notime )
        {
            $('tr.time').hide();
        }
        else
        {
            $('tr.time').show();
        }

        if( selected )
        {
            $('#date-'+selected).removeClass('today').addClass('selected');
        }

        $(document).bind('mousedown', function(e)
                                    {
                                        e = $.event.fix(e);

                                        if( e.target.id != 'cal' && $(e.target).parents('#cal').length == 0 )
                                        {
                                            hideCalendar();
                                        }
                                    });
    };


    // Hide the calendar
    function hideCalendar()
    {
        buddy = null;
        $(document).unbind('mousedown');
        $('#cal').hide();
    };


    // Set the calendar to view today
    function setToday()
    {
        var today = new Date();
        var month = today.getMonth()+1;
        if( month < 10 ) month = '0' + month;

        $('#cal_year option[value='+today.getFullYear()+']').attr('selected', 'selected');
        $('#cal_month option[value='+month+']').attr('selected', 'selected');

        $('#cal_hour option[value=12]').attr('selected', 'selected');
        $('#cal_minute option[value=00]').attr('selected', 'selected');
        $('#cal_second option[value=00]').attr('selected', 'selected');
    };


    // Initialize the calendar markup
    function initializeCalendar()
    {
        var now = new Date();
        var year = now.getFullYear();
        var month = now.getMonth();
        var html_buffer = '<div id="cal" class="calendar" style="display: none;">' +
                          '<div>' +
                          '<table align="center" cellspacing="0">' +
                          '<tr>' +
                          '<td>' +
                          '<div class="prev"></div>' +
                          //'&lt;' +
                          '</td>' +
                          '<td colspan="5" align="center">' +
                          '<select id="cal_month">';

        for( var i = 0; i < months.length; i++ )
        {
            var value = i + 1;
            if( value < 10 ) value = '0' + value;
            html_buffer += '<option value="'+value+'"'+(i == month ? ' selected="selected"' : '')+'>'+months[i]+'</option>';
        }

        html_buffer += '</select> <select id="cal_year">';

        for( var i = year - 100; i < year + 25; i++ )
        {
            html_buffer += '<option value="'+i+'"'+(i == year ? ' selected="selected"' : '')+'>'+i+'</option>';
        }

        html_buffer += '</select>' +
                       '</td>' +
                       '<td align="right">' +
                       '<div class="next"></div>' +
                       //'&gt;' +
                       '</td>' +
                       '</tr>' +
                       '<tr class="days">';

        for( var i = 0; i < days.length; i++ )
        {
            html_buffer += '<td';

            if( i == 0 )
            {
                html_buffer += ' class="sunday"';
            }
            else if( i == 6 )
            {
                html_buffer += ' class="saturday"';
            }

            html_buffer += '>' + days[i] + '</td>';
        }

        html_buffer += '</tr>' +
                       '<tr class="time">' +
                       '<td colspan="7" align="center">' +
                       '<select id="cal_hour">';

        for( var i = 0; i < 24; i++ )
        {
            if( i < 10 ) i = '0' + i;
            html_buffer += '<option value="'+i+'"'+(i == 12 ? ' selected="selected"' : '')+'>'+i+'</option>';
        }

        html_buffer += '</select>:<select id="cal_minute">';

        for( var i = 0; i < 60; i++ )
        {
            if( i < 10 ) i = '0' + i;
            html_buffer += '<option value="'+i+'">'+i+'</option>';
        }

        html_buffer += '</select>:<select id="cal_second">';

        for( var i = 0; i < 60; i++ )
        {
            if( i < 10 ) i = '0' + i;
            html_buffer += '<option value="'+i+'">'+i+'</option>';
        }

        html_buffer += '</select></td></tr>' +
                       '</table>';

        $('body').append(html_buffer);

        $('#cal').hover(function() { hovering = true }, function() { hovering = false });
        $('#cal_month').bind('change', updateCalendar);
        $('#cal_year').bind('change', updateCalendar);
        $('#cal .prev').click(function() { jumpMonth(false); });
        $('#cal .next').click(function() { jumpMonth(true); });

        $('#cal_month').trigger('change');
    };


    // Update the calendar when the month or year change
    function updateCalendar()
    {
        var today = new Date();
        var selected_month = $('#cal_month').val();
        var selected_year = $('#cal_year').val();

        if( selected_month.charAt(0) == '0' )
            selected_month = selected_month.substr(1);

        selected_month = parseInt(selected_month);

        var start_date = new Date(selected_year, selected_month - 1, 1, 12);
        var days_in_month = new Date(start_date.getFullYear(), start_date.getMonth(), 0).getDate();

        var day_of_week = start_date.getDay();
        if( day_of_week == 0 )
        {
            day_of_week = 7;
        }
        start_date.setTime(start_date.getTime() - (day_of_week * one_day));

        var cur_date = new Date();
        var html_buffer = '<tr class="numbers">';
        for( var i = 0; i < 42; i++ )
        {
            cur_date.setTime(start_date.getTime() + (i * one_day));
            var month = (cur_date.getMonth()+1);
            if( month < 10 ) month = '0' + month;
            var date = cur_date.getDate();
            if( date < 10 ) date = '0' + date;
            var full_date = cur_date.getFullYear() + '-' + month + '-' + date;
            html_buffer += '<td id="date-'+full_date+'" class="' +
                           (cur_date.getMonth()+1 != selected_month ? ' other-month' : '') +
                           '">'+cur_date.getDate()+'</td>';

            if( (i + 1) % 7 == 0 )
            {
                html_buffer += '</tr><tr class="numbers">';
            }
        }

        $('#cal .numbers').unbind().remove();
        $('#cal .days').after(html_buffer);
        $('#cal .numbers > td').click(dateClicked);

        var month = (today.getMonth()+1);
        if( month < 10 ) month = '0' + month;
        var date = today.getDate();
        if( date < 10 ) date = '0' + date;

        $('#date-'+today.getFullYear() + '-' + month + '-' + date).addClass('today');
    };


    // A date was clicked on
    function dateClicked()
    {
        var now = new Date();
        var time = $('#cal_hour').val() + ':' + $('#cal_minute').val() + ':' + $('#cal_second').val();
        var matches = this.id.match(/(\d\d\d\d)-(\d\d)-(\d\d)/);
        var datetime = matches[1] + '-' + matches[2] + '-' + matches[3] + ($('tr.time:visible').length ? ' ' + time : '');

        if( buddy )
        {
            $(buddy).val(datetime);
        }

        hideCalendar();
    };


    // Jump forward or back one month
    function jumpMonth(forward)
    {
        var selected_month = $('#cal_month').val();
        var selected_year = parseInt($('#cal_year').val());

        if( selected_month.charAt(0) == '0' )
            selected_month = selected_month.substr(1);

        selected_month = parseInt(selected_month);

        // Adjust year
        if( forward )
        {
            if( selected_month == 12 )
            {
                selected_year++;
                selected_month = 0;
            }

            selected_month++;
        }
        else
        {
            if( selected_month == 1 )
            {
                selected_year--;
                selected_month = 13;
            }

            selected_month--;
        }

        if( selected_month < 10 ) selected_month = '0' + selected_month;

        $('#cal_year option[value='+selected_year+']').attr('selected', 'selected');
        $('#cal_month option[value='+selected_month+']').attr('selected', 'selected');
        $('#cal_month').trigger('change');

        return false;
    };

    // Default values
    $.fn.calendar.defaults =
    {
        notime: false
    };

})(jQuery);