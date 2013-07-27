for( var i = 0; i < data.ids.length; i++ )
{
    var $tr = $('#search-results-tbody #'+data.ids[i]).removeClass('Disabled');
    $('img[title=Disable]', $tr).show();
    $('img[title=Enable]', $tr).hide();
    $('b:contains("Status") ~ span', $tr).text('Active');
}