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


final class Video_Tools
{

    private static $instance;

    public $mencoder;
    public $mplayer;
    public $ffmpeg;
    public $mp4box;
    public $yamdi;
    public $convert;
    public $nice;

    private function __construct()
    {
        $si = ServerInfo::GetCached();

        $this->mencoder = $si->binaries[ServerInfo::BIN_MENCODER];
        $this->mplayer = $si->binaries[ServerInfo::BIN_MPLAYER];
        $this->ffmpeg = $si->binaries[ServerInfo::BIN_FFMPEG];
        $this->mp4box = $si->binaries[ServerInfo::BIN_MP4BOX];
        $this->yamdi = $si->binaries[ServerInfo::BIN_YAMDI];
        $this->convert = $si->binaries[ServerInfo::BIN_CONVERT];
        $this->nice = $si->binaries[ServerInfo::BIN_NICE];
    }

    public static function Get()
    {
        if( empty(self::$instance) )
        {
            self::$instance = new Video_Tools();
        }

        return self::$instance;
    }

    public function __get($value)
    {
        return $this->tools[$value];
    }
}

?>