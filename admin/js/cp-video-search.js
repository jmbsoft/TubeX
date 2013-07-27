$('.preview-thumb')
.livequery(function()
           {
                $(this)
                .addClass('clickable')
                .click(videoPlayerPopup);
           },
           function() {});


$('.view-log')
.livequery('click',
           function()
           {
               var $icon = $(this);
               var $container = $icon.parent();
               var video_id = $icon.parents('tr.search-result').attr('id');

               $icon.unbind('click').click(function()
                                           {
                                               $('.conversion-log', $container).toggle();
                                           });

               $.ajax({data: 'r=tbxConvesionLogView&video_id='+video_id,
                       success: function(data)
                                {
                                    $(data.html).appendTo($container);
                                }
                      });
           });