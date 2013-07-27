<?php
// Copyright 2011 JMB Software, Inc.
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//    http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.

class Video_Source_Embed extends Video_Source
{

    public function PreProcess()
    {
        $v = Validator::Create();

        $v->Register($this->source[Video_Source::FIELD_EMBED], Validator_Type::NOT_EMPTY, 'The Embed Code field is required');
        $v->Register($this->source[Video_Source::FIELD_DURATION], Validator_Type::VALID_TIME, 'The Video Duration field must be in HH:MM:SS format');

        $this->duration = Format::DurationToSeconds($this->source[Video_Source::FIELD_DURATION]);
        $this->video_dir = new Video_Dir(null, 0700);

        Request::FixFiles();

        // No thumbnails uploaded
        if( !isset($_FILES[Video_Source::FIELD_THUMBNAILS]) )
        {
            return;
        }


        // Process each uploaded file
        foreach( $_FILES[Video_Source::FIELD_THUMBNAILS] as $upload )
        {
            // No file uploaded in this field
            if( $upload['error'] == UPLOAD_ERR_NO_FILE )
            {
                continue;
            }

            // Check for other errors
            if( $upload['error'] != UPLOAD_ERR_OK )
            {
                throw new BaseException(Uploads::CodeToMessage($upload['error']));
            }

            switch( File::Type($upload['name']) )
            {
                case File::TYPE_ZIP:
                    foreach( Zip::ExtractEntries($upload['tmp_name'], File::TYPE_JPEG) as $name => $data )
                    {
                        $thumbs[] = $this->video_dir->AddTempFromVar($data, JPG_EXTENSION);
                    }
                    break;

                case File::TYPE_JPEG:
                    $thumbs[] = $this->video_dir->AddTempFromFile($upload['tmp_name'], JPG_EXTENSION);
                    break;
            }
        }


        // Resize (if possible) and move images to the correct directory
        if( Video_Thumbnail::CanResize() )
        {
            $this->thumbs = Video_Thumbnail::ResizeDirectory($this->video_dir->GetTempDir(),
                                                             $this->video_dir->GetThumbsDir(),
                                                             Config::Get('thumb_size'),
                                                             Config::Get('thumb_quality'));
        }
        else
        {
            $this->thumbs = $this->video_dir->MoveFiles(Video_Dir::TEMP, Video_Dir::THUMBS, JPG_EXTENSION);
        }

        // Cleanup temp and processing dirs
        $this->video_dir->ClearTemp();
        $this->video_dir->ClearProcessing();
    }

    public function PostProcessSuccess($video_id)
    {
        // Adjust permissions and move directory
        $old_directory = $this->video_dir->GetBaseDir();
        @chmod($old_directory, 0777);
        $directory = Video_Dir::DirNameFromId($video_id);
        $this->video_dir->MoveTo($directory);


        // Add the embed code to the database
        DatabaseAdd('tbx_video_clip', array('video_id' => $video_id,
                                            'clip' => $this->source[Video_Source::FIELD_EMBED],
                                            'type' => 'Embed'));

        // Get the relative URL for each thumb and add to database
        $thumb_ids = array();
        foreach( $this->thumbs as $thumb )
        {
            $thumb = str_replace(array($old_directory, Config::Get('document_root')), array($directory, ''), $thumb);
            $thumb_ids[] = DatabaseAdd('tbx_video_thumbnail', array('video_id' => $video_id,
                                                                    'thumbnail' => $thumb));
        }

        // Determine number of thumbnails and select random display thumbnail
        $num_thumbnails = count($this->thumbs);
        $display_thumbnail = null;
        if( $num_thumbnails > 0 )
        {
            // Select display thumbnail randomly from the first 40%
            $display_thumbnail = $thumb_ids[rand(0, floor(0.40 * $num_thumbnails))];
        }

        $update = array('video_id' => $video_id,
                        'num_thumbnails' => $num_thumbnails,
                        'display_thumbnail' => $display_thumbnail,
                        'duration' => $this->duration);

        $DB = GetDB();
        $DB->Update('DELETE FROM `tbx_conversion_queue` WHERE `video_id`=?', array($video_id));
        DatabaseUpdate('tbx_video', $update);
    }

    public function PostProcessFailure()
    {
        if( $this->video_dir instanceof Video_Dir )
        {
            $this->video_dir->Remove();
        }
    }
}

?>