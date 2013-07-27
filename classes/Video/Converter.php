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

class Video_Converter
{

    const FORMAT_MP4 = 0;

    const FORMAT_VP6 = 1;

    const FORMAT_H263 = 2;

    const SAMPLE_RATE_AAC = 48000;

    const SAMPLE_RATE_MP3 = 44100;

    const CHANNELS_MP3 = 2;

    const CHANNELS_AAC = 2;

    const EXTENSION_FLV = 'flv';

    const EXTENSION_AVI = 'avi';

    const EXTENSION_MP4 = 'mp4';

    const THREADS = 1;

    const SHARPEN_THRESHOLD = 0.75;

    private static $logfile = null;

    public static function SetLogFile($filename)
    {
        self::$logfile = $filename;
        File::Create(self::$logfile);
    }

    public static function Log($string)
    {
        if( !empty(self::$logfile) )
        {
            file_put_contents(self::$logfile,
                              '[' . date('r') . "]\n" . str_replace(array("\r\n", "\r"), "\n", $string) . "\n\n\n",
                              FILE_APPEND | LOCK_EX);
        }
    }

    private static function ScaleFilter($output_size, $input_width, $input_height)
    {
        if( empty($output_size) || strpos($output_size, 'x') === false )
        {
            return null;
        }

        list($output_width, $output_height) = explode('x', $output_size);

        if( empty($output_height) || (!empty($output_width) && $input_width > $output_width) )
        {
            return 'scale=' . $output_width . ':-10' .
                   ($output_width / $input_width < self::SHARPEN_THRESHOLD ? ',unsharp=l3x3:1:c3x3:1' : '');
        }
        else if( empty($output_width) || (!empty($output_height) && $input_height > $output_height) )
        {
            return 'scale=-10:' . $output_height .
                   ($output_height / $input_height < self::SHARPEN_THRESHOLD ? ',unsharp=l3x3:1:c3x3:1' : '');
        }

        return null;
    }

    public static function Convert($filename, $directory, $format = self::FORMAT_MP4, $vbitrate = 900, $abitrate = 96, $dimensions = false, $callback = null)
    {
        if( !is_dir($directory) )
        {
            throw new BaseException('Video conversion output directory does not exist', $directory);
        }

        if( !is_writeable($directory) )
        {
            throw new BaseException('Video conversion output directory is not writeable', $directory);
        }

        $vi = new Video_Info($filename);
        $vi->Extract();

        // Scaling of the output video
        $scale = self::ScaleFilter($dimensions, $vi->video_width, $vi->video_height);

        // TODO: Don't convert the video if it is already the correct format
        //
        //  VP6/MP3 FLV
        //      ID_VIDEO_FORMAT=VP6F
        //      ID_VIDEO_CODEC=ffvp6f
        //      ID_AUDIO_CODEC=mp3
        //
        //  H263/MP3 FLV
        //      ID_VIDEO_FORMAT=FLV1
        //      ID_VIDEO_CODEC=ffflv
        //      ID_AUDIO_CODEC=mp3
        //
        //  H264/AAC FLV
        //      ID_VIDEO_FORMAT=H264
        //      ID_VIDEO_CODEC=ffh264
        //      ID_AUDIO_CODEC=faad
        //
        //      ID_VIDEO_FORMAT=avc1


        switch($format)
        {
            case self::FORMAT_MP4:
                return self::ConvertToMP4($vi, $filename, $directory, $vbitrate, $abitrate, $scale, $callback);

            case self::FORMAT_VP6:
                return self::ConvertToVP6($vi, $filename, $directory, $vbitrate, $abitrate, $scale, $callback);

            default:
                return self::ConvertToH263($vi, $filename, $directory, $vbitrate, $abitrate, $scale, $callback);
        }
    }

    private static function ExecuteCmdAsync($cmd, $callback = null, $interval = 1)
    {
        self::Log($cmd);

        $descriptorspec = array(1 => array('file', self::$logfile, 'a'), 2 => array('file', self::$logfile, 'a'));

        $process = proc_open($cmd, $descriptorspec, $pipes);
        $terminated = false;

        if( is_resource($process) )
        {
            do
            {
                sleep(1);

                if( !empty($callback) )
                {
                    $terminated = call_user_func($callback);
                }

                if( $terminated )
                {
                    proc_terminate($process);
                }

                $status = proc_get_status($process);
            }
            while( $status['running'] );

            proc_close($process);
        }

        return $terminated;
    }

    private static function ConvertToH263($vi, $filename, $directory , $vbitrate, $abitrate, $scale, $callback)
    {
        $tools = Video_Tools::Get();
        $tmp_file = File::Temporary($directory, self::EXTENSION_FLV);

        $cmd = (!empty($tools->nice) ? $tools->nice . ' ' : '') .
               $tools->mencoder . ' ' .
               escapeshellarg($filename) . ' ' .
               '-o ' . escapeshellarg($tmp_file) . ' ' .
               '-of lavf ' .
               ($vi->has_audio ? '-oac mp3lame -lameopts ' . escapeshellarg('abr:br=' . $abitrate) . ' -srate ' . self::SAMPLE_RATE_MP3 . ' -channels ' . self::CHANNELS_MP3 . ' ' : ' -nosound ') .
               '-ovc lavc -lavcopts ' . escapeshellarg('vcodec=flv:vbitrate=' . $vbitrate) . ' ' .
               '-vf ' . escapeshellarg((!empty($scale) ? $scale . ',' : '') . 'harddup') . ' ' .
               '-ofps ' . escapeshellarg($vi->video_fps);

        if( self::ExecuteCmdAsync($cmd, $callback) )
        {
            File::Delete($tmp_file);
            throw new BaseException('Video conversion was interrupted by user request');
        }



        if( !file_exists($tmp_file) || filesize($tmp_file) == 0 )
        {
            File::Delete($tmp_file);
            throw new BaseException('Unable to convert video file to H.263/MP3 FLV', $filename, $output);
        }

        $output_file = File::Temporary($directory, self::EXTENSION_FLV);

        $cmd = (!empty($tools->nice) ? $tools->nice . ' ' : '') .
               $tools->yamdi . ' ' .
               '-i ' . escapeshellarg($tmp_file) . ' ' .
               '-o ' . escapeshellarg($output_file);
        self::Log($cmd);

        $output = shell_exec($cmd);
        self::Log($output);

        File::Delete($tmp_file);

        return $output_file;
    }

    private static function ConvertToVP6($vi, $filename, $directory , $vbitrate, $abitrate, $scale, $callback)
    {
        $mcf_file = INCLUDES_DIR . '/vp6.mcf';

        if( !file_exists($mcf_file) )
        {
            throw new BaseException('The VP6 settings file could not be found', $mcf_file);
        }

        $tools = Video_Tools::Get();
        $tmp_file = File::Temporary($directory, self::EXTENSION_FLV);

        $cmd = (!empty($tools->nice) ? $tools->nice . ' ' : '') .
               $tools->mencoder . ' ' .
               escapeshellarg($filename) . ' ' .
               '-o ' . escapeshellarg($tmp_file) . ' ' .
               '-of lavf ' .
               ($vi->has_audio ? '-oac mp3lame -lameopts ' . escapeshellarg('abr:br=' . $abitrate) . ' -srate ' . self::SAMPLE_RATE_MP3 . ' -channels ' . self::CHANNELS_MP3 . ' ' : ' -nosound ') .
               '-ovc vfw -xvfwopts ' . escapeshellarg('codec=vp6vfw.dll:compdata=' . $mcf_file) . ' ' .
               '-vf ' . escapeshellarg((!empty($scale) ? $scale . ',' : '') . 'flip,harddup') . ' ' .
               '-ofps ' . escapeshellarg($vi->video_fps);

        if( self::ExecuteCmdAsync($cmd, $callback) )
        {
            File::Delete($tmp_file);
            throw new BaseException('Video conversion was interrupted by user request');
        }


        if( filesize($tmp_file) == 0 )
        {
            File::Delete($tmp_file);
            throw new BaseException('Unable to convert video file to VP6/MP3 FLV', $filename, $output);
        }

        $output_file = File::Temporary($directory, self::EXTENSION_FLV);

        $cmd = (!empty($tools->nice) ? $tools->nice . ' ' : '') .
               $tools->yamdi . ' ' .
               "-i " . escapeshellarg($tmp_file) . ' ' .
               "-o " . escapeshellarg($output_file);
        self::Log($cmd);

        $output = shell_exec($cmd);
        self::Log($output);

        File::Delete($tmp_file);

        return $output_file;
    }

    private static function ConvertToMp4($vi, $filename, $directory , $vbitrate, $abitrate, $scale, $callback)
    {
        $tools = Video_Tools::Get();
        $vbitrate = ($vbitrate <= 40 ? 'crf=' : 'ratetol=1.0:bitrate=') . $vbitrate;
        $tmp_file = File::Temporary($directory, self::EXTENSION_AVI);

        $cmd = (!empty($tools->nice) ? $tools->nice . ' ' : '') .
               $tools->mencoder . ' ' .
               escapeshellarg($filename) . ' ' .
               '-o ' . escapeshellarg($tmp_file) . ' ' .
               '-sws 9 ' .
               '-noskip ' .
               '-ovc x264 ' .
               '-x264encopts ' . escapeshellarg($vbitrate . ':bframes=1:me=umh:partitions=all:trellis=1:qp_step=4:qcomp=0.7:direct_pred=auto:keyint=300:threads=' . self::THREADS) . ' ' .
               '-vf ' . escapeshellarg((!empty($scale) ? $scale . ',' : '') . 'harddup') . ' ' .
               ($vi->has_audio ?
                  '-oac faac -faacopts ' . escapeshellarg('br=' . $abitrate . ':mpeg=4:object=2') . ' -channels ' . self::CHANNELS_AAC . ' -srate ' . self::SAMPLE_RATE_AAC . ' ' :
                  ' -nosound ') .
               '-ofps ' . escapeshellarg($vi->video_fps);

        if( self::ExecuteCmdAsync($cmd, $callback) )
        {
            File::Delete($tmp_file);
            throw new BaseException('Video conversion was interrupted by user request');
        }

        // Verify video file generated
        if( filesize($tmp_file) == 0 )
        {
            File::Delete($tmp_file);
            throw new BaseException('Unable to convert video file to H.264/AAC MP4', $filename, $output);
        }


        // Get the filenames of the extracted raw streams
        $directory = Dir::StripTrailingSlash($directory);
        $basename = basename($tmp_file, '.'.self::EXTENSION_AVI);
        $videofile = $directory . '/' . $basename . '_video.h264';
        $audiofile_raw = $directory . '/' . $basename . '_audio.raw';
        $audiofile = $directory . '/' . $basename . '_audio.aac';


        // Extract the raw streams
        $cmd = (!empty($tools->nice) ? $tools->nice . ' ' : '') . $tools->mp4box . ' -aviraw video ' . escapeshellarg($tmp_file) . ' 2>&1';
        self::Log($cmd);
        $output = shell_exec($cmd);
        self::Log($output);

        if( !file_exists($videofile) )
        {
            File::Delete($tmp_file);
            throw new BaseException('Unable to extract video from file using MP4Box', $videofile, $output);
        }

        $output_file = File::Temporary($directory, self::EXTENSION_MP4);


        // Process video files that do have an audio stream
        if( $vi->has_audio )
        {
            $cmd = (!empty($tools->nice) ? $tools->nice . ' ' : '') . $tools->mp4box . ' -aviraw audio ' . escapeshellarg($tmp_file) . ' 2>&1';
            self::Log($cmd);
            $output = shell_exec($cmd);
            self::Log($output);

            if( !file_exists($audiofile_raw) )
            {
                File::Delete($tmp_file);
                File::Delete($videofile);
                throw new BaseException('Unable to extract audio from file using MP4Box', $audiofile_raw, $output);
            }

            rename($audiofile_raw, $audiofile);

            $cmd = (!empty($tools->nice) ? $tools->nice . ' ' : '') .
                   $tools->mp4box .
                   ' -add ' . escapeshellarg($videofile) .
                   ' -add ' . escapeshellarg($audiofile) .
                   ' -fps ' . escapeshellarg($vi->video_fps) .
                   ' -inter 500 ' .
                   escapeshellarg($output_file) . ' 2>&1';
            self::Log($cmd);

            $output = shell_exec($cmd);
            self::Log($output);

            File::Delete($audiofile);
        }

        // Process video files that have no audio stream
        else
        {
            $cmd = (!empty($tools->nice) ? $tools->nice . ' ' : '') .
                   $tools->mp4box .
                   ' -add ' . escapeshellarg($videofile) .
                   ' -fps ' . escapeshellarg($vi->video_fps) .
                   ' -inter 500 ' . escapeshellarg($output_file)  . ' 2>&1';
            self::Log($cmd);

            $output = shell_exec($cmd);
            self::Log($output);
        }

        // Remove temporary files
        File::Delete($tmp_file);
        File::Delete($videofile);

        if( !file_exists($output_file) )
        {
            throw new BaseException('Unable to generate MP4 file using MP4Box', $output_file, $output);
        }

        return $output_file;
    }
}

?>