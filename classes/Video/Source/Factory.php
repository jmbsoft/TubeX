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


class Video_Source_Factory
{

    public static function Create($source)
    {
        if( !isset($source[Video_Source::FIELD_TYPE]) )
        {
            throw new BaseException("The field '" . Video_Source::FIELD_TYPE . "' is not set; cannot determine source type");
        }

        switch($source[Video_Source::FIELD_TYPE])
        {
            case Video_Source::URL:
                return new Video_Source_URL($source);

            case Video_Source::GALLERY:
                return new Video_Source_Gallery($source);

            case Video_Source::UPLOAD:
                return new Video_Source_Upload($source);

            case Video_Source::EMBED:
                return new Video_Source_Embed($source);
        }
    }
}

?>