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


abstract class QueueProcessor
{
    const ARGUMENT = '--run';
    const MAX_PING_INTERVAL = 300;

    // Should be overridden by each descendant
    protected static $LOG = 'queue.log';
    protected static $TABLE = 'tbx_queue';
    protected static $SCRIPT = 'QueueProcessor.php';
    protected static $CACHE_STATS = 'queue-stats';
    protected static $CACHE_PID = 'queue-pid';
    protected static $CACHE_STOP = 'queue-stop';


    // Stats
    const STAT_LAST_STARTED = 'last_started';
    const STAT_LAST_PING = 'last_ping';
    const STAT_QUEUED_ITEMS = 'queued_items';
    const STAT_PROCESSED_ITEMS = 'processed_items';
    const STAT_FAILED_ITEMS = 'failed';
    const STAT_ESTIMATED_TIME = 'estimated_time';
    const STAT_AVERAGE_TIME = 'average_time';
    const STAT_AVERAGE_WAIT = 'average_wait';
    const STAT_TOTAL_TIME = 'total_time';
    const STAT_TOTAL_WAIT = 'total_wait';
    const STAT_RUNNING = 'running';

    protected static function Log($message)
    {
        // Using echo since output is redirected to a log file during execution
        echo '[' . date('r') . "] $message\n";
    }

    public static function Start()
    {
        // Setup log file
        self::$LOG = DATA_DIR . '/' . basename(self::$LOG);
        if( !file_exists(self::$LOG) )
        {
            File::Create(self::$LOG);
        }

        // Make sure not marked to stop
        Cache_MySQL::Cache(self::$CACHE_STOP, 0);

        // Start it up, if not already running
        if( !self::IsRunning() )
        {
            $si = ServerInfo::GetCached();
            $script = dirname(realpath(__FILE__)) . '/' . self::$SCRIPT;

            File::Create(self::$LOG);
            
            shell_exec($si->binaries[ServerInfo::BIN_PHP] . ' -q ' . $script . ' ' . self::ARGUMENT . ' >>' . escapeshellarg(self::$LOG) . ' 2>&1 &');
        }
    }

    public static function Stop()
    {
        Cache_MySQL::Cache(self::$CACHE_STOP, 1);
    }

    public static function Clear()
    {
        if( !self::IsRunning() )
        {
            $DB = GetDB();
            $DB->Update('DELETE FROM #', array(self::$TABLE));
        }
    }

    public static function IsStartable()
    {
        $si = ServerInfo::GetCached();
        return (!self::IsRunning() && !$si->shell_exec_disabled && !empty($si->binaries[ServerInfo::BIN_PHP]));
    }

    public static function IsRunning()
    {
        // Check for empty pid
        $pid = Cache_MySQL::Get(self::$CACHE_PID);
        if( empty($pid) )
        {
            return false;
        }


        // Try to use ps to determine if the process is running
        $si = ServerInfo::GetCached();
        if( !empty($si->binaries[ServerInfo::BIN_PS]) )
        {
            return preg_match('~'.$pid.'~', shell_exec($si->binaries[ServerInfo::BIN_PS] . " $pid")) > 0;
        }


        // Try to determine based on last ping
        $stats = self::LoadStats();
        if( $stats[self::STAT_RUNNING] )
        {
            if( $stats[self::STAT_LAST_PING] < time() - self::MAX_PING_INTERVAL )
            {
                self::MarkStopped();
                return false;
            }

            return true;
        }


        // Last resort, assume not running
        return false;
    }

    public static function Ping()
    {
        self::UpdateStats(array(self::STAT_LAST_PING => time()));
        return self::ShouldStop();
    }

    protected static function UpdateStatsProcessed($start, $end, $queued, $failed = false)
    {
        $stats = self::LoadStats();

        if( $failed )
        {
            $stats[self::STAT_FAILED_ITEMS]++;
        }
        else
        {
            $stats[self::STAT_PROCESSED_ITEMS]++;
        }

        $stats[self::STAT_TOTAL_TIME] += (float)$end - (float)$start;
        $stats[self::STAT_TOTAL_WAIT] += (float)$start - (float)$queued;
        $stats[self::STAT_AVERAGE_WAIT] = $stats[self::STAT_TOTAL_WAIT] == 0 || $stats[self::STAT_PROCESSED_ITEMS] == 0 ?
                                          '??:??:??' :
                                          Format::SecondsToLongDuration($stats[self::STAT_TOTAL_WAIT] / $stats[self::STAT_PROCESSED_ITEMS]);
        $stats[self::STAT_AVERAGE_TIME] = $stats[self::STAT_TOTAL_TIME] == 0 || $stats[self::STAT_PROCESSED_ITEMS] == 0 ?
                                          '??:??:??' :
                                          Format::SecondsToLongDuration($stats[self::STAT_TOTAL_TIME] / $stats[self::STAT_PROCESSED_ITEMS]);

        self::SaveStats($stats);
    }

    public static function LoadStats($for_display = false)
    {
        $stats = Cache_MySQL::Get(self::$CACHE_STATS);

        if( empty($stats) )
        {
            $stats = array();
            $stats[self::STAT_AVERAGE_TIME] = '??:??:??';
            $stats[self::STAT_AVERAGE_WAIT] = '??:??:??';
            $stats[self::STAT_FAILED_ITEMS] = 0;
            $stats[self::STAT_LAST_PING] = 0;
            $stats[self::STAT_LAST_STARTED] = '??:??:??';
            $stats[self::STAT_PROCESSED_ITEMS] = 0;
            $stats[self::STAT_RUNNING] = false;
            $stats[self::STAT_TOTAL_TIME] = 0;
            $stats[self::STAT_TOTAL_WAIT] = 0;
        }
        else
        {
            $stats = unserialize($stats);
        }

        if( $for_display )
        {
            $DB = GetDB();
            $stats[self::STAT_QUEUED_ITEMS] = $DB->QueryCount('SELECT COUNT(*) FROM #', array(self::$TABLE));
            $stats[self::STAT_ESTIMATED_TIME] = $stats[self::STAT_QUEUED_ITEMS] == 0 || $stats[self::STAT_PROCESSED_ITEMS] == 0 ?
                                                '??:??:??' :
                                                Format::SecondsToLongDuration($stats[self::STAT_TOTAL_TIME] / $stats[self::STAT_PROCESSED_ITEMS] * $stats[self::STAT_QUEUED_ITEMS]);
        }

        return $stats;
    }

    protected static function UpdateStats($new_stats)
    {
        self::SaveStats(array_merge(self::LoadStats(), $new_stats));
    }

    protected static function SaveStats($stats)
    {
        Cache_MySQL::Cache(self::$CACHE_STATS, serialize($stats));
    }

    protected static function ShouldStop()
    {
        return Cache_MySQL::Get(self::$CACHE_STOP);
    }

    protected static function MarkRunning()
    {
        self::UpdateStats(array(self::STAT_RUNNING => true,
                                self::STAT_LAST_STARTED => date(DATETIME_FRIENDLY)));

        Cache_MySQL::Cache(self::$CACHE_PID, getmypid());
    }

    protected static function MarkStopped()
    {
        self::UpdateStats(array(self::STAT_RUNNING => false));
        Cache_MySQL::Cache(self::$CACHE_PID, 0);
    }
}

?>