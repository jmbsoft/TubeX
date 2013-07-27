/*------------------------------------------------------------------#
# TubeX - Copyright © 2009 JMB Software, Inc. All Rights Reserved.  #
# This file may not be redistributed in whole or significant part.  #
# TubeX IS NOT FREE SOFTWARE                                        #
#-------------------------------------------------------------------#
# http://www.jmbsoft.com/           http://www.jmbsoft.com/license/ #
#------------------------------------------------------------------*/


jQuery.searchform = function()
{
    if( $('#search-form').length == 0 )
    {
        return;
    }

    // Setup the search and sort fields on the search form
    $('select.search-connector').livequery('change', searchClone).trigger('change');
    $('select.sort-connector').livequery('change', sortClone).trigger('change');

    var $sstab = $('#saved-searches-tab');

    // Display/Hide saved searches tab
    $('#saved-searches-icon, img.toolbar-tab-close')
    .click(function()
           {
               if( $('select option', $sstab).length < 1 )
               {
                   $('#saved-searches-existing').hide();
               }

               $sstab
               .center('horizontal')
               .animate({height: ($sstab.is(':hidden') ? 'show' : 'hide')}, 'fast', 'swing');
           });

    // Saved searches delete icon
    $('.icon-delete', $sstab)
    .click(function()
           {
               if( confirm('Are you sure you want to delete this saved search?') )
               {
                   var $option = $('select option:selected', $sstab);

                   toolbarTabActivity($sstab, 'show');
                   $.ajax({data: 'r=tbxSavedSearchDelete&id='+$option.val(),
                           success: function()
                                    {
                                        $option.remove();

                                        if( $('select option', $sstab).length < 1 )
                                        {
                                            $('#saved-searches-existing:visible').hide();
                                            $sstab.center('horizontal', true);
                                        }

                                        toolbarTabActivity($sstab, 'hide');
                                    }});
               }
           });

    // Saved searches update icon
    $('.icon-update', $sstab)
    .click(function()
           {
               toolbarTabActivity($sstab, 'show');
               $.ajax({data: 'r=tbxSavedSearchEdit&id='+$('select option:selected', $sstab).val()+'&form='+escape($('#search-form').serialize()),
                       success: function(data)
                                {
                                    toolbarTabActivity($sstab, 'hide');
                                }});
           });

    // Saved searches new icon
    $('.icon-new', $sstab)
    .click(function()
           {
               toolbarTabActivity($sstab, 'show');
               $.ajax({data: $('#saved-searches-new > form').serialize() + '&form='+escape($('#search-form').serialize()),
                       success: function(data)
                                {
                                    if( data.status == JSON.SUCCESS )
                                    {
                                        var $select = $('#saved-searches-existing select');
                                        $('<option value="'+data.value+'">'+data.text+'</option>').appendTo($select).attr('selected', 'selected');
                                        $('#saved-searches-existing:hidden').show();
                                        $('#saved-searches-new input[name=identifier]').val('');
                                        $sstab.center('horizontal', true);
                                    }

                                    toolbarTabActivity($sstab, 'hide');
                                }});
           });

    // Saved searches load icon
    $('.icon-load', $sstab)
    .click(function()
           {
               toolbarTabActivity($sstab, 'show');
               $.ajax({data: 'r=tbxSavedSearchLoad&id='+$('select option:selected', $sstab).val(),
                       success: function(data)
                                {
                                    searchFormDeserialize(data.form);
                                    $('#search-form').submit();
                                    toolbarTabActivity($sstab, 'hide');
                                }});
           });

    if( saved_search )
    {
        searchFormDeserialize(saved_search);
        $('#saved-searches option[value='+saved_search_id+']').attr('selected', 'selected');
    }

    $('#page-jump input').bind('keyup', function(e)
                                          {
                                                $.event.fix(e);

                                                if( e.keyCode == 13 && $('#search-pages').text() != '0' )
                                                {
                                                    $('#search-form').setVal('input[name=page]', $(this).val()).submit();
                                                }

                                                return false;
                                          });


    $('#search-button').click(function() { $('#search-form input[name=page]').val(1); });
    $('#search-page-first').click(function() { $('#search-form').setVal('input[name=page]', 1).submit(); });
    $('#search-page-prev').click(function() { $('#search-form').decrement('input[name=page]').submit(); });
    $('#search-page-next').click(function() { $('#search-form').increment('input[name=page]').submit(); });
    $('#search-page-last').click(function() { $('#search-form').setVal('input[name=page]', 999999999).submit(); });

    // Setup the form to be submitted by AJAX
    $('#search-form')
    .ajaxForm({beforeSubmit: function()
                             {
                                 $('#search-no-results, #search-page-prev, #search-page-first, #search-page-next, #search-page-last, #search-results-container').hide();
                                 $('#search-activity, #search-page-prev-off, #search-page-first-off, #search-page-next-off, #search-page-last-off').show();
                                 $('#search-results-tbody').empty();
                             },
               success: function(data, status, form)
                        {
                            if( !data.formatted )
                            {
                                return;
                            }

                            $('#search-activity').hide();
                            $('#search-form input[name=page]').val(data.page);
                            $('#page-jump input').val(data.page);
                            $('span.search-start').text(data.formatted.start);
                            $('span.search-end').text(data.formatted.end);
                            $('span.search-total').text(data.formatted.total);
                            $('#search-pages').text(data.formatted.pages);

                            if( data.total == 0 )
                            {
                                $('#toolbar-pagination img[src*=pagination]').hide();
                                $('#toolbar-pagination img[src*=off]').show();
                                $('#search-no-results').show();
                            }
                            else
                            {
                                if( data.prev_page )
                                {
                                    $('#search-page-prev, #search-page-first').show();
                                    $('#search-page-prev-off, #search-page-first-off').hide();
                                }

                                if( data.next_page )
                                {
                                    $('#search-page-next, #search-page-last').show();
                                    $('#search-page-next-off, #search-page-last-off').hide();
                                }

                                $('#search-results-container').show();
                                $('#search-results-tbody').append(data.html);
                            }
                        }})
    .submit();


    $('.resizeable-column b.clickable')
    .livequery('click', function()
                        {
                            $('~ span', this).toggleClass('auto-height');
                        });

    // IE doesn't handle :last-child CSS
    if( $.browser.msie )
    {
        $('#search-results-title td:last-child').css({backgroundColor: 'transparent', borderRight: 'none', borderLeft: 'none', borderTop: 'none'});
        $('#search-result-tbody td:last-child').livequery(function() { $(this).css({borderRight: 'none'});  }, function() {});
    }
};


/**
* Deserialize the search form
*/
function searchFormDeserialize(form)
{
    if( form )
    {
        if( typeof form == 'string' )
        {
            form = eval("(" + form + ")");
        }

        var search_field_count = form.search_field.length;
        var sort_field_count = form.sort_field.length;

        $('#text-search').val(form.text_search);
        $('select[name="text_search_type"] option[value="' + form.text_search_type + '"]').attr('selected', 'selected');
        $('#cb-detailed').val(form.detailed ? 0 : 1).parents('div').mousedown();
        $('#per-page').val(form.per_page);
        $('#search-form input[name=page]').val(1);
        $('#search-fields').empty();
        $('#sort-fields').empty();

        for( var i = 0; i < search_field_count; i++ )
        {
            $div = searchClone();

            $('.search-field option[value='+form.search_field[i]+']', $div).attr('selected', 'selected');
            $('.search-operator option[value='+form.search_operator[i]+']', $div).attr('selected', 'selected');
            $('.search-term', $div).val(form.search_term[i]);
            $('.search-connector option[value='+form.search_connector[i]+']', $div).attr('selected', 'selected');
        }

        for( var i = 0; i < sort_field_count; i++ )
        {
            $div = sortClone();

            $('.sort-field option[value='+form.sort_field[i]+']', $div).attr('selected', 'selected');
            $('.sort-direction option[value='+form.sort_direction[i]+']', $div).attr('selected', 'selected');
            $('.sort-connector option[value='+form.sort_connector[i]+']', $div).attr('selected', 'selected');
        }
    }
}


/**
* Show activity indicator for toolbar tabs
*/
function toolbarTabActivity($tab, show)
{
    switch(show)
    {
        case 'show':
        case 'display':
        case 'visible':
            var $sstc = $('.toolbar-tab-content > div', $tab);
            var offset = $sstc.position();
            var padding_horiz = $sstc.outerWidth() / 2 - 8;
            var padding_vert = $sstc.outerHeight() / 2 - 8;

            $('.toolbar-tab-activity', $tab).css({top: offset.top + 'px', left: offset.left + 'px', padding: padding_vert + 'px ' + padding_horiz + 'px'}).show();
            break;

        case 'hide':
        case 'remove':
        case 'hidden':
            $('.toolbar-tab-activity', $tab).hide();
            break;
    }
}


/**
* Clone search form search fields
*/
function searchClone(e)
{
    var $parent = $(this.parentNode);

    if( $parent.attr('id') == '' )
    {
        if( $(this).val() == '' )
        {
            $parent.nextAll().remove();
            return;
        }

        if( $parent.next().length > 0 )
        {
            return;
        }
    }

    var $fields = $('#search-master').clone().appendTo('#search-fields').attr('id', '').show();

    $('input.search-term', $fields).autocomplete({buddy: 'select.search-field'});

    return $fields;
}



/**
* Clone search form sort fields
*/
function sortClone()
{
    var $parent = $(this.parentNode);

    if( $parent.attr('id') == '' && $(this).val() == '' )
    {
        $parent.nextAll().remove();
        return;
    }

    return $('#sort-master').clone().appendTo('#sort-fields').attr('id', '').show();
}