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


class Format
{

    public static function BytesToString($bytes, $space = '')
    {
        $decr = 1024;
        $step = 0;
        $suffix = array('B','KB','MB','GB','TB','PB','EB','ZB');

        while( ($bytes / $decr) > 0.9 )
        {
            $bytes = $bytes / $decr;
            $step++;
        }

        $precision = $bytes > 99 ? 0 : 2;

        return round($bytes, $precision) . $space . $suffix[$step];
    }

    public static function StringToBytes($string)
    {
        if( preg_match('~(\d+)\s*([A-Z]+)~i', $string, $matches) )
        {
            $multiplier = 1;
            switch(strtoupper($matches[2]))
            {
                case 'T':
                case 'TB':
                    $multiplier *= 1024;

                case 'G':
                case 'GB':
                    $multiplier *= 1024;

                case 'M':
                case 'MB':
                    $multiplier *= 1024;

                case 'K':
                case 'KB':
                    $multiplier *= 1024;
            }

            return $matches[1] * $multiplier;
        }

        return $string;
    }

    public static function DurationToSeconds($duration)
    {
        if( String::IsEmpty($duration) )
        {
            return 0;
        }
        
        list($hours, $minutes, $seconds) = explode(':', $duration);

        return $hours * 3600 + $minutes * 60 + $seconds;
    }

    public static function SecondsToDuration($seconds)
    {
        return gmdate('H:i:s', $seconds);
    }

    public static function SecondsToLongDuration($seconds)
    {
        if( $seconds >= 86400 )
        {
            $days = floor($seconds / 86400);
            $seconds = $seconds % 86400;

            return $days . 'd ' . gmdate('H:i:s', $seconds);
        }

        return self::SecondsToDuration($seconds);
    }
}

?>