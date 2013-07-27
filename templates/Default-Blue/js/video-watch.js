function showMessage(message)
{
    this.panel.children('[class^="message-"]').remove();
    var $notice = $('<div class="message-notice" style="display: none; margin-bottom: 8px;">' + message + '</div>').prependTo(this.panel).fadeIn();

    setTimeout(function()
               {
                   $notice.fadeOut('normal', function() { $(this).remove(); });
               },
               5000);
}

function paginationLinkClick()
{
    var href = $(this).attr('href');

    $.ajax({
       url: href,
       type: 'get',
       success: function(data)
                {
                    $('#video-comments').html(data);
                    $('a.pagination-link').click(paginationLinkClick);
                }
    });

    return false;
}

$(function()
{
    // Preload stars
    var stars = new Array();
    for( var i = 0; i < 5; i++ )
    {
        stars[i] = new Image();
        stars[i].src = template_uri + '/images/' + (i+1) + '-stars-large.png';
    }

    $.ajaxSetup({dataType: 'html', type: 'post', cache: false, timeout: 0, success: showMessage});

    // Select clip to view
    $('#clips img:eq(0)').addClass('selected');
    $('#clips img')
    .click(function()
           {
               $(this).addClass('selected').siblings().removeClass('selected');
               loadClip($(this).attr('href'));
           });


    // Favorite, share, feature, flag Tabs
    $('div.inner-section-header span')
    .click(function()
           {
               $(this)
               .addClass('selected')
               .siblings()
               .removeClass('selected');

               $('div.inner-section-content > div').hide();
               $($(this).attr('href')).show();

               if( $(this).attr('href') == '#panel-comment' && $('.captcha-image').attr('src') == '' )
               {
                   $('.captcha-reload').click();
               }

               return false;
           })
    .filter(':first-child')
    .trigger('click');

    // Character counting
    $('#comment-text').bind('keyup', function() { $('#comment-length').html($(this).val().length); }).trigger('keyup');

    // Reload CAPTCHA image
    $('.captcha-reload')
    .click(function()
           {
               $(this)
               .siblings('.captcha-image')
               .attr('src', base_uri + '/code.php?' + Math.random());
           });


    // Comment
    $('#button-comment')
    .click(function()
           {
               $.ajax({
                       url: base_uri + '/comment.php',
                       data: { video_id: video_id, comment: $('#comment-text').val(), captcha: $('#comment-captcha').val()},
                       panel: $(this).parents('div[id^="panel-"]'),
                       complete: function()
                                 {
                                     $('.captcha-reload').click();
                                     $('#comment-captcha').val('');
                                 }
                      });
           });


    // Add favorite
    $('#button-fav-add')
    .click(function()
           {
               $.ajax({
                       url: base_uri + '/favorite.php',
                       data: { video_id: video_id, add: 1 },
                       panel: $(this).parents('div[id^="panel-"]')
                      });
           });


    // Remove favorite
    $('#button-fav-remove')
    .click(function()
           {
               $.ajax({
                       url: base_uri + '/favorite.php',
                       data: { video_id: video_id, add: 0 },
                       panel: $(this).parents('div[id^="panel-"]')
                      });
           });


    // Flag
    $('#button-flag')
    .click(function()
           {
               $.ajax({
                       url: base_uri + '/flag.php',
                       data: { video_id: video_id, reason_id: $('#reason-flagged').val() },
                       panel: $(this).parents('div[id^="panel-"]')
                      });
           });


    // Feature
    $('#button-feature')
    .click(function()
           {
               $.ajax({
                       url: base_uri + '/feature.php',
                       data: { video_id: video_id, reason_id: $('#reason-feature').val() },
                       panel: $(this).parents('div[id^="panel-"]')
                      });
           });


    // Rating by mouse over stars
    $('.rater-div')
    .hover(function()
           {
               $('#rater-stars').attr('src', template_uri + '/images/' + $(this).attr('stars') + '-stars-large.png');
               guest_rating || logged_in ? $('#rater-text').text(star_text[$(this).attr('stars') - 1]) : $('#rater-text').text(login_to_rate);
           },
           function()
           {
               $('#rater-text').text('');
               $('#rater-stars').attr('src', $('#rater-stars').attr('osrc'));
           })
    .click(function(e)
           {
               if( guest_rating || logged_in )
               {
                   $.ajax({
                           url: base_uri + '/rate.php',
                           data: { rating: $(this).attr('stars'), video_id: video_id },
                           panel: $('#panel-rating-message')
                          });
               }
           });

    // Comment pagination by AJAX
    $('a.pagination-link').click(paginationLinkClick);
});