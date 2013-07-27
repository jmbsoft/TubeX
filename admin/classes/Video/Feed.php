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

abstract class Video_Feed
{
    // Class constants
    const YOUTUBE = 'YouTube Feed';
    const XMLVIDEOS = 'XML Videos Feed';

    protected $feed;

    protected $defaults;

    public function __construct($feed)
    {
        $this->feed = $feed;
        $this->defaults = array('username' => $this->feed['username'],
                                'date_added' => Database_MySQL::Now(),
                                'date_recorded' => null,
                                'location_recorded' => null,
                                'source_url' => null,
                                'status' => $this->feed['status'],
                                'category_id' => $this->feed['category_id'],
                                'sponsor_id' => $this->feed['sponsor_id'],
                                'duration' => 0,
                                'is_private' => $this->feed['is_private'],
                                'allow_comments' => $this->feed['allow_comments'],
                                'allow_ratings' => $this->feed['allow_ratings'],
                                'allow_embedding' => $this->feed['allow_embedding']);

        $this->defaults['username'] = String::Nullify($this->defaults['username']);
    }

    public static function Create($feed)
    {
        switch($feed['type'])
        {
            case self::YOUTUBE:
                return new Video_Feed_YouTube($feed);

            case self::XMLVIDEOS:
                return new Video_Feed_XMLVideos($feed);

            default:
                throw new BaseException('Video feed type ' . $feed['type'] . ' is not supported');
        }
    }

    abstract public function Import();

    abstract public function Test();
}

?>
