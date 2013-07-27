for( var i = 0; i < data.ids.length; i++ )
{
    var $tr = $('#search-results-tbody #'+data.ids[i]).addClass('Disabled');
    $('img[title=Disable]', $tr).hide();
    $('img[title=Enable]', $tr).show();
    $('b:contains("Status") ~ span', $tr).text('Disabled');
}