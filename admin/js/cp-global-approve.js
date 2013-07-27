for( var i = 0; i < data.ids.length; i++ )
{
    var $tr = $('#search-results-tbody #'+data.ids[i]).removeClass('Pending');
    $('img[title=Disable]', $tr).show();
    $('b:contains("Status") ~ span', $tr).text('Active');
    $('.process-pending-section', $tr).remove();
}