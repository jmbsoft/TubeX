for( var i = 0; i < data.ids.length; i++ )
{
    $('#search-results-tbody #'+data.ids[i] + ' img[title="Reasons Featured"]').attr('src', 'images/featured-22x22.png');
    $('#search-results-tbody #'+data.ids[i] + ' span:contains("Last Featured") > span').text(data.datetime);
}