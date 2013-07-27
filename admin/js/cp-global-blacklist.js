if( data.ids == null )
{
    $('#search-form').submit();
}
else
{
    var removed = 0;
    for( var i = 0; i < data.ids.length; i++ )
    {
        $('#search-results-tbody #'+data.ids[i]).remove();
        removed++;
    }

    $('span.search-end, span.search-total').decrementHTML(removed);

    if( parseInt($('span.search-end').html()) < 1 )
    {
        $('#search-form').submit();
    }
}