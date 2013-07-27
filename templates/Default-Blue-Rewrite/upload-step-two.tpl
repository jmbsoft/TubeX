{template file="global-header.tpl" title="_Text:Upload a Video"}

<script language="JavaScript" type="text/javascript">
var swfu;

function flashLoadFailed()
{
    alert('This interface requires Flash version 9.0.28+');
}

function uploadStart()
{
    $('div.message-error').remove();
    $('#progress').show();
    $('#upload-button').attr('disabled', 'disabled');
    $('#cancel-button').removeAttr('disabled');
    swfu.setButtonDisabled(true);
}

function uploadProgress(file, complete, total)
{
    var percent = SWFUpload.speed.formatPercent(file.percentUploaded);
    $('#progress-bar').css({width: percent});
    $('#progress-percent').text(percent);

    $('#progress-transferred').text(SWFUpload.speed.formatBytes(file.sizeUploaded));
    $('#progress-speed').text(SWFUpload.speed.formatBytesPS(file.movingAverageSpeed));
    $('#progress-time-remaining').text(SWFUpload.speed.formatTime(file.timeRemaining));

    if( file.percentUploaded >= 100 )
    {
        $('#cancel-button').attr('disabled', 'disabled');
        $('#form').css({visibility: 'hidden'});
        $('#processing').show();
    }
}

function uploadSuccess(file, data, response)
{
    $('#processing').hide();
    $(data).insertBefore('#form');
}

function uploadError(file, errorCode, message)
{
    $('#progress').hide();
    $('#cancel-button').attr('disabled', 'disabled');
    $('#upload-button').attr('disabled', 'disabled');
    $('#video-file').text('').css({marginRight: '0px'});
    swfu.setButtonDisabled(false);
}

function uploadComplete()
{
    $('#progress-bar').css({width: 0});
    $('#progress-percent').text('0%');

    $('#progress-transferred').text('');
    $('#progress-speed').text('');
    $('#progress-time-remaining').text('');
}

function fileDialogStart()
{
    this.cancelUpload();
}

function fileQueued(file)
{
    $('#video-file').text(file.name + ' (' + SWFUpload.speed.formatBytes(file.size) + ')').css({marginRight: '8px'});
    $('#upload-button').removeAttr('disabled');
}

function fileQueueError(file, errorCode, message)
{
    switch( errorCode )
    {
        case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED:
            alert('You can only upload one video');
            return;

        case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
            alert('The video file you selected exceeds the maximum allowed size');
            return;

        case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
            alert('The video file you selected is 0 bytes and cannot be uploaded');
            return;

        case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
            alert('The file you selected is not an allowed file type');
            return;

        default:
            alert('Unknown upload error condition');
            return;
    }
}

$(function()
{
    $('#upload-button, #cancel-button').show();
    var height = $('#select-file-button').outerHeight();
    var width = $('#select-file-button').outerWidth();

    swfu = new SWFUpload({
                          minimum_flash_version: '9.0.28',
                          swfupload_load_failed_handler: flashLoadFailed,

                          upload_url: '{$g_config.base_uri}/upload.php?flash=true',
                          file_post_name: 'video_file',

                          file_size_limit : '{$g_config.max_upload_size}',
                          file_types: '{$g_file_types}',
                          file_types_description: 'Videos',
                          file_upload_limit: 0,
                          file_queue_limit: 1,

                          file_dialog_start_handler: fileDialogStart,
                          file_queue_error_handler: fileQueueError,
                          file_queued_handler: fileQueued,

                          upload_start_handler: uploadStart,
                          upload_progress_handler: uploadProgress,
                          upload_success_handler: uploadSuccess,
                          upload_complete_handler: uploadComplete,
                          upload_error_handler: uploadError,

                          button_image_url: '{$g_config.template_uri}/images/select-video-button-134x88.png',
                          button_placeholder_id: 'upload-swf',
                          button_action: SWFUpload.BUTTON_ACTION.SELECT_FILE,
                          button_width: 134,
                          button_height: 22,
                          button_text : "<span class=\"redText\">" + '{"_Button:Select Video File"}' + "</span>",
                          button_text_style : ".redText { font-family: Arial; size: 12; font-weight: bold; text-align: center; }",
                          button_text_top_padding: 2,

                          flash_url: '{$g_config.base_uri}/swfupload/swfupload.swf',

                          debug: false
                          });


    $('#cancel-button')
    .click(function()
           {
               swfu.stopUpload();
           });

    $('#upload-button')
    .click(function()
           {
               var stats = swfu.getStats();

               if( stats.files_queued == 0 )
               {
                   alert('{"_Text:Please select a file"}');
                   return;
               }

               swfu.setPostParams($(this).parents('form').formToObject());
               swfu.addPostParam('cookie', '{$g_cookie}');
               swfu.startUpload();
           });

});
</script>

    <div class="main-content" style="margin-top: 30px;">
      <span class="section-whole">

        <div class="section-header">{"_Text:Upload a Video"}</div>
        <div class="section-content">

          {if !empty($g_errors)}
          <div class="message-error">
            {"_Text:Please Fix"}
            <ul>
            {foreach var=$error from=$g_errors}
            <li>{$error}</li>
            {/foreach}
            </ul>
          </div>
          {/if}

          <div id="processing" style="display: none; font-size: 130%; font-weight: bold;">
            <img src="{$g_config.template_uri}/images/activity-22x22.gif" style="vertical-align: middle;" />
            <span style="vertical-align: middle;">{"_Text:Processing uploaded video"}</span>
          </div>

          <form method="post" action="{$g_config.base_uri}/upload/" id="form" enctype="multipart/form-data">

            <div style="font-size: 115%; margin: 10px;">
              {"_Text:Select video file to upload"}

              <ul>
                <li> {"_Text:Video size restriction",$g_config.max_upload_size}</li>
                <li> {"_Text:Video duration restriction",$g_config.max_upload_duration}</li>
                <li> {"_Text:Video extension restriction",$g_config.upload_extensions}</li>
              </ul>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Video"}:</span>
              <span class="input-container">
                <noscript><input type="file" name="video_file" /></noscript>
                <span id="video-file" style="vertical-align: middle;"></span>
                <span id="upload-swf"></span>
              </span>
            </div>

            <div class="field" id="progress" style="display: none;">
              <span class="label wider"></span>
              <span class="input-container">
                <div id="progress-container">
                  <div id="progress-bar"></div>
                  <div id="progress-percent">75%</div>
                </div>

                <span class="label">{"_Label:Transferred"}:</span> <span class="text-container" id="progress-transferred"></span><br />
                <span class="label">{"_Label:Speed"}:</span> <span class="text-container" id="progress-speed"></span><br />
                <span class="label">{"_Label:Time Left"}:</span> <span class="text-container" id="progress-time-remaining"></span><br />
              </span>
            </div>

            <div class="field" id="upload-button-div">
              <span class="label wider"></span>
              <span class="input-container">
                <noscript><input type="submit" value="{"_Button:Upload"}" /></noscript>
                <input type="button" id="upload-button" value="{"_Button:Upload"}" disabled="disabled" style="display: none;" />
                <input type="button" id="cancel-button" value="{"_Button:Cancel"}" disabled="disabled" style="display: none;" />
              </span>
            </div>

            <input type="hidden" name="step_one_data" value="{$g_form.step_one_data}" />
            <input type="hidden" name="step_one_sig" value="{$g_form.step_one_sig}" />
            <input type="hidden" name="r" value="upload-step-two" />
          </form>

        </div>

      </span>
    </div>

{template file="global-footer.tpl"}