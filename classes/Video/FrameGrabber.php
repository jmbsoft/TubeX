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

class Video_FrameGrabber
{
    const FILTER_UNSHARP = 'unsharp=l5x5:1.4:c5x5:1.4';
    const FILTER_EQ2 = 'eq2=1.4::0.2:1.5';

    private static $logfile = null;

    private function __construct() { }

    public static function SetLogFile($filename)
    {
        self::$logfile = $filename;
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

    public static function CanGrab()
    {
        $si = ServerInfo::GetCached();

        return !$si->shell_exec_disabled && $si->binaries[ServerInfo::BIN_MPLAYER];
    }

    private static function ScaleFilter($output_size, $input_width, $input_height)
    {
        if( empty($output_size) || strpos($output_size, 'x') === false )
        {
            return self::FILTER_EQ2;
        }

        list($output_width, $output_height) = explode('x', $output_size);
        $input_aspect = sprintf('%.3f', $input_width / $input_height);
        $output_aspect = sprintf('%.3f', $output_width / $output_height);

        $letterbox = Config::Get('flag_letterbox');

        if( $input_aspect == $output_aspect )
        {
            return self::FILTER_EQ2 . ",scale=$output_width:$output_height," . self::FILTER_UNSHARP;
        }
        else if( $input_aspect > $output_aspect )
        {
            if( $letterbox )
            {
                return self::FILTER_EQ2 . ",scale=$output_width:-10," . self::FILTER_UNSHARP . ",expand=:::::$output_aspect,scale=$output_width:$output_height";
            }
            else
            {
                return self::FILTER_EQ2 . ",crop=" . round($input_height * $output_aspect) . ",scale=$output_width:$output_height," . self::FILTER_UNSHARP;
            }
        }
        else if( $input_aspect < $output_aspect )
        {
            if( $letterbox )
            {
                return self::FILTER_EQ2 . ",scale=-10:$output_height," . self::FILTER_UNSHARP . ",expand=:::::$output_aspect,scale=$output_width:$output_height";
            }
            else
            {
                return self::FILTER_EQ2 . ",crop=:" . round($input_width / $output_aspect) . ",scale=$output_width:$output_height," . self::FILTER_UNSHARP;
            }
        }

        return null;
    }

    public static function Grab($filename, $directory, $num_frames = 10, $quality = 90, $dimensions = false, $vi = null)
    {
        if( !is_dir($directory) || !is_writeable($directory) )
        {
            throw new BaseException('Output directory is missing or not writeable', $directory);
        }

        if( !file_exists($filename) )
        {
            throw new BaseException('Input file is missing', $filename);
        }

        // Get video info if it was not provided
        if( !($vi instanceof Video_Info) )
        {
            $vi = new Video_Info($filename);
            $vi->Extract();
        }

        $output = null;
        $tools = Video_Tools::Get();
        $filter = self::ScaleFilter($dimensions, $vi->video_width, $vi->video_height);

        // Extract frames from short videos (less than 1 minute)
        if( $vi->length < 60 )
        {
            self::Log('Using short video frame extraction method');

            $framestep = floor($vi->video_frames / $num_frames);
            $end = floor($vi->length);

            $cmd = $tools->mplayer .
                   ' -nosound' .
                   ' -vo ' . escapeshellarg('jpeg:quality=' . $quality . ':outdir=' . $directory) .
                   ' -endpos ' . escapeshellarg($end) .
                   ' -sws 9' .
                   ' -speed 100' .
                   ' -vf ' . escapeshellarg('framestep=' . $framestep . ',' . $filter) .
                   ' ' . escapeshellarg($filename) . ' 2>&1';
            self::Log($cmd);

            $output = shell_exec($cmd);
            self::Log($output);

            $frames = glob($directory . '/*.' . JPG_EXTENSION);
            $generated = count($frames);

            self::Log('Total frames generated: ' . $generated);
        }

        // Extract frames from longer videos (1 minute+)
        else
        {
            self::Log('Using long video frame extraction method');

            $start = min(ceil($vi->length * 0.01), 15);
            $end = floor($vi->length - $start);
            $interval = floor(($end - $start) / ($num_frames - 1));

            // Attempt to use the quick frame grab method
            $cmd = $tools->mplayer .
                   ' -nosound' .
                   ' -vo ' . escapeshellarg('jpeg:quality=' . $quality . ':outdir=' . $directory) .
                   ' -frames ' . escapeshellarg($num_frames) .
                   ' -ss ' . escapeshellarg($start) .
                   ' -sstep ' . escapeshellarg($interval) .
                   ' -endpos ' . escapeshellarg($end) .
                   ' -sws 9 ' .
                   ' -vf ' . escapeshellarg($filter) .
                   ' ' . escapeshellarg($filename) . ' 2>&1';
            self::Log($cmd);

            $output = shell_exec($cmd);
            self::Log($output);

            $frames = glob($directory . '/*.' . JPG_EXTENSION);
            $generated = count($frames);
            self::Log('Total frames generated: ' . $generated);

            // Fall back to the slow frame grab method
            if( $generated < 1 || ($num_frames > 1 && $generated == 1) || stristr($output, 'first frame is no keyframe') )
            {
                self::Log('Falling back to long video SLOW frame extraction method');

                // Reset values and directory contents
                $generated = 0;
                if( is_array($frames) )
                {
                    foreach( $frames as $frame )
                    {
                        unlink($frame);
                    }
                }

                // Grab each frame individually
                for( $i = 0; $i < $num_frames; $i++ )
                {
                    $cmd = $tools->mplayer .
                           ' -nosound' .
                           ' -vo ' . escapeshellarg('jpeg:quality=' . $quality . ':outdir=' . $directory) .
                           ' -frames 1 ' .
                           ' -sws 9 ' .
                           ' -ss ' . ($start + $i * $interval) .
                           ' -vf ' . escapeshellarg($filter) .
                           ' ' . escapeshellarg($filename) . ' 2>&1';
                    self::Log($cmd);

                    $this_output = shell_exec($cmd);
                    $output .= $this_output;
                    self::Log($this_output);

                    if( file_exists("$directory/00000001.jpg") )
                    {
                        $generated++;
                        rename("$directory/00000001.jpg", $directory . sprintf('/%s%08d.jpg', ($generated == 1 ? 't': ''), $generated));
                    }
                }

                if( file_exists("$directory/t00000001.jpg") )
                {
                    rename("$directory/t00000001.jpg", "$directory/00000001.jpg");
                }

                $frames = glob($directory . '/*.' . JPG_EXTENSION);

                self::Log('Total frames generated: ' . $generated);
            }
        }

        if( $generated < 1 )
        {
            throw new BaseException('Could not grab frames from this video file', $filename, $output);
        }

        if( Video_Thumbnail::CanResize() )
        {
            $frames = Video_Thumbnail::DiscardBlack($directory);

            self::Log('Total frames generated after black frame removal: ' . count($frames));
        }

        return $frames;
    }
}

?>