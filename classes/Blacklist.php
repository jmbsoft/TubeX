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


class Blacklist
{

    const TYPE_USER_IP = 'User IP';
    const TYPE_EMAIL = 'E-mail';
    const TYPE_URL = 'Domain/URL';
    const TYPE_DOMAIN_IP = 'Domain IP';
    const TYPE_WORD = 'Word';
    const TYPE_HTML = 'HTML';
    const TYPE_HTTP_HEADER = 'HTTP Header';
    const TYPE_DNS = 'DNS Server';

    const ITEM_USER = 0;
    const ITEM_COMMENT = 1;
    const ITEM_VIDEO = 2;

    private static $blacklist;

    private static function Load()
    {
        if( !isset(self::$blacklist) )
        {
            self::$blacklist = array();
            $DB = GetDB();
            $result = $DB->Query('SELECT * FROM `tbx_blacklist`');
            while( $row = $DB->NextRow($result) )
            {
                if( !$row['regex'] )
                {
                    $row['value'] = preg_quote($row['value'], '~');
                }

                self::$blacklist[] = $row;
            }
        }
    }

    public static function Match($item, $item_type)
    {
        self::Load();

        // Setup the fields to check
        $fields = array();
        switch( $item_type )
        {
            case self::ITEM_COMMENT:
                $fields[self::TYPE_USER_IP] = $item['ip_address'];
                $fields[self::TYPE_WORD] = $item['comment'];
                break;

            case self::ITEM_USER:
                $fields[self::TYPE_USER_IP] = $item['ip_address'];
                $fields[self::TYPE_EMAIL] = $item['email'];
                $fields[self::TYPE_URL] = $item['website_url'];
                $fields[self::TYPE_WORD] = join(' ', array($item['username'],
                                                           $item['name'],
                                                           $item['about'],
                                                           $item['hometown'],
                                                           $item['current_city'],
                                                           $item['current_country'],
                                                           $item['occupations'],
                                                           $item['companies'],
                                                           $item['schools'],
                                                           $item['hobbies'],
                                                           $item['movies'],
                                                           $item['music'],
                                                           $item['books']));
                break;

            case self::ITEM_VIDEO:
                $fields[self::TYPE_USER_IP] = $item['ip_address'];
                $fields[self::TYPE_WORD] = join(' ', array($item['title'],
                                                           $item['description'],
                                                           $item['tags'],
                                                           $item['location_recorded']));
                break;
        }


        // Nothing to check
        if( empty($fields) )
        {
            return false;
        }


        // Check
        foreach( self::$blacklist as $b )
        {
            if( preg_match('~(' . $b['value'] . ')~i', $fields[$b['type']], $matches) )
            {
                $b['match'] = $matches[1];
                return $b;
            }
        }

        return false;
    }

    public static function FilterSearchTerms()
    {
        $DB = GetDB();
        $words = $DB->FetchAll('SELECT `regex`,`value` FROM `tbx_blacklist` WHERE `type`=?', array(self::TYPE_WORD));

        $result = $DB->Query('SELECT * FROM `tbx_search_term_new`');
        while( $term = $DB->NextRow($result) )
        {
            $blacklisted = false;
            foreach( $words as $w )
            {
                if( !$w['regex'] )
                {
                    $w['value'] = preg_quote($w['value'], '~');
                }

                if( preg_match('~(' . $w['value'] . ')~i', $term['term']) )
                {
                    $blacklisted = true;
                    break;
                }
            }

            if( !$blacklisted )
            {
                if( $DB->Update('UPDATE `tbx_search_term` SET `frequency`=`frequency`+? WHERE `term`=?', array($term['frequency'], $term['term'])) == 0 )
                {
                    $DB->Update('INSERT INTO `tbx_search_term` VALUES (?,?,?)', array(null, $term['term'], 1));
                }
            }

            $DB->Update('DELETE FROM `tbx_search_term_new` WHERE `term_id`=?', array($term['term_id']));
        }
        $DB->Free($result);
    }
}

?>