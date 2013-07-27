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

class Video_Info
{

    private $filename;

    private $cache = null;

    public function __construct($video_file)
    {
        $this->filename = $video_file;
    }

    public static function CanExtract()
    {
        $si = ServerInfo::GetCached();
        return !$si->shell_exec_disabled && $si->binaries[ServerInfo::BIN_MENCODER];
    }

    private static function ToSeconds($timecode)
    {
        if( preg_match('~(\d\d):(\d\d):(\d\d).(\d\d)~', $timecode, $matches) )
        {
            return $matches[3] + ($matches[2] * 60) + ($matches[1] * 60 * 60);
        }

        return null;
    }

    public function Extract()
    {
        if( !file_exists($this->filename) )
        {
            throw new BaseException(_T('Validation:Video file could not be found'), $this->filename);
        }

        $tools = Video_Tools::Get();

        // Check to see if file info has been cached
        if( $this->cache != null )
        {
            return $this->cache;
        }

        // Execute mplayer to get video file information
        $output = shell_exec($tools->mplayer . ' -identify ' . escapeshellarg($this->filename) . ' -ao null -vo null -frames 0 2>&1');
        $data = array();

        // Extract video file information
        if( preg_match_all('~^ID_([A-Z0-9_]+)=(.*)~m', $output, $matches, PREG_SET_ORDER) )
        {
            foreach( $matches as $match )
            {
                $data[strtolower($match[1])] = $match[2];
            }

            if( !isset($data['video_format']) )
            {
                throw new BaseException(_T('Validation:Video invalid format'), $this->filename, $output);
            }

            $data['video_frames'] = ceil($data['video_fps'] * $data['length']);
            $data['has_audio'] = isset($data['audio_id']) && isset($data['audio_rate']);


            // Length likely incorrect
            if( $data['length'] <= 0.0 )
            {
                $data['length'] = null;

                if( !empty($tools->ffmpeg) )
                {
                    $output = shell_exec($tools->ffmpeg . ' -i ' . escapeshellarg($this->filename) . ' 2>&1');

                    if( preg_match('~Duration: (\d+:\d+:\d+.\d+)~', $output, $matches) )
                    {
                        $data['length'] = self::ToSeconds($matches[1]);
                    }
                }

                // All we can do now is guess
                if( empty($data['length']) )
                {
                    $data['length'] = 5;
                }
            }

            // Video FPS likely incorrect
            if( $data['video_fps'] > 100 )
            {
                $data['video_fps'] = null;
                if( !empty($tools->ffmpeg) )
                {
                    $output = shell_exec($tools->ffmpeg . ' -i ' . escapeshellarg($this->filename) . ' 2>&1');

                    if( preg_match('~([0-9.]+) tbr~', $output, $matches) )
                    {
                        $data['video_fps'] = $matches[1];
                        $data['video_frames'] = ceil($data['video_fps'] * $data['length']);
                    }
                }

                // All we can do now is guess
                if( empty($data['video_fps']) )
                {
                    $data['video_fps'] = '29.97';
                }
            }
        }

        // Cache it
        $this->cache = $data;

        return $data;
    }

    public function __get($name)
    {
        return $this->cache[$name];
    }

    public function __toString()
    {
        $string = '';
        foreach( $this->cache as $name => $value )
        {
            $string .= "$name: $value\n";
        }

        return $string;
    }

    public function __isset($name)
    {
        return isset($this->cache[$name]);
    }
}

?>