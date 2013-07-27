var center_timeout = null;

function thumbSelectContainerMousedown(e)
{
    if( e.shiftKey )
    {
        var id = $(this).attr('thumbid');

        $(this).remove();

        $.ajax({data: 'r=tbxThumbnailDelete&thumbnail_id=' + id,
                success: function(data)
                         {
                             if( data.html )
                             {
                                 $('.thumb-select-container').html(data.html);
                             }

                             if( data.display_thumbnail == 0 )
                             {
                                 $('#thumb-select').hide();
                                 $('#thumb-none').show();
                                 $('input[name="display_thumbnail"]').val('');
                             }
                             else
                             {
                                 $('.thumb-select-container img[thumbid="' + data.display_thumbnail + '"]').trigger('mousedown');
                             }
                         }
               });
    }
    else
    {
        $(this).addClass('thumb-selected').siblings().removeClass('thumb-selected');
        $('input[name="display_thumbnail"]').val($(this).attr('thumbid'));
    }
}

$('input.defaultvalue')
.defaultvalue();

$('.thumb-select-container img')
.mousedown(thumbSelectContainerMousedown)
.bind('selectstart', function() { return false; });

$('img[src$=add-16x16.png]')
.click(function()
       {
           $(this.parentNode)
           .clone(true)
           .insertAfter(this.parentNode);

           clearTimeout(center_timeout);
           center_timeout = setTimeout(function() { $('#dialog').center('vertical', true); }, 1000);
       });

$('img[src$=remove-16x16.png]')
.click(function()
       {
           if( $('input[name="' + $('input', this.parentNode).attr('name') +'"]').length > 1 )
           {
               $(this.parentNode).remove();

               clearTimeout(center_timeout);
               center_timeout = setTimeout(function() { $('#dialog').center('vertical', true); }, 1000);
           }
       });

$('#source-type')
.change(function()
        {
            var source = $(this).val();

            $('div.vs').hide();
            $('div.vs_'+source).show();
        })
.trigger('change');

$('#dialog-content form')
.bind('ajaxSuccess', function(e, xhr, opts, data)
                     {
                         $('#thumbnails div.thumb-uploads:gt(0)').remove();
                         $('#thumbnails div.thumb-uploads input').val('');

                         if( data.thumbs )
                         {
                             $('#thumb-select').show();
                             $('#thumb-none').hide();

                             for( var i = 0; i < data.thumbs.length; i++ )
                             {
                                 $('<img src="' + data.thumbs[i].uri + '" border="0" height="90" thumbid="' + data.thumbs[i].id + '" class="clickable" />')
                                 .appendTo('.thumb-select-container')
                                 .mousedown(thumbSelectContainerMousedown);
                             }
                         }
                     });

$('#dialog-content form')
.bind('reset', function()
               {
                   setTimeout(function()
                              {
                                  $('input[defaultvalue]').trigger('focus').trigger('blur');
                                  $('img[src$=remove-16x16.png]').trigger('click');
                                  $('div.vs').hide();
                                  $('#source-type').trigger('change');
                              }, 200);
               });