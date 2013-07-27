$(function()
{
    $('img.video-thumb')
    .hover(function()
           {
               var img = this;
               var src = img.src;
               var thumbs = $(this).attr('thumbs');
               img.original = src;

               if( img.thumb == undefined )
               {
                   img.thumb = 1;
               }

               if( thumbs > 1 )
               {
                   var updater = function()
                                 {
                                     img.src = img.src.replace(/\d+\.jpg/, zeroPad(img.thumb, 8) + '.jpg');

                                     if( ++img.thumb > thumbs )
                                     {
                                         img.thumb = 1;
                                     }
                                 };

                   updater();
                   this.interval = setInterval(updater, 750);
               }
           },
           function()
           {
               clearInterval(this.interval);
               this.src = this.original;
           });
});

function zeroPad(num, count)
{
    var numZeropad = num + '';
    while( numZeropad.length < count )
    {
        numZeropad = "0" + numZeropad;
    }

    return numZeropad;
}