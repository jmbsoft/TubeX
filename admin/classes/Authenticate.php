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


class Authenticate
{

    const FIELD_USERNAME = 'cp_username';

    const FIELD_PASSWORD = 'cp_password';

    const FIELD_REMEMBER = 'cp_remember';

    const FIELD_SESSION = 'cp_session';

    const TYPE_SUPERUSER = 'Superuser';

    const TYPE_EDITOR = 'Editor';

    const SESSION_LENGTH = 7776000;

    const COOKIE_NAME = 'tubexcp';

    private static $username;

    private static $authenticated = false;

    private static $superuser = false;

    private static $privileges = 0;

    private static $error = null;


    private static function GetCookieSettings()
    {
        return array('path' => isset($_SERVER['REQUEST_URI']) ? preg_replace('~/admin/.*~', '/admin/', $_SERVER['REQUEST_URI']) : '/',
                     'domain' => $defaults['cookie_domain'] = isset($_SERVER['HTTP_HOST']) ? preg_replace('~^www\.~i', '', $_SERVER['HTTP_HOST']) : null);
    }

    public static function Login()
    {
        $DB = GetDB();
        self::$authenticated = false;
        self::$superuser = false;
        self::$username = null;
        $cookie_settings = self::GetCookieSettings();


        if( isset($_REQUEST[self::FIELD_USERNAME]) )
        {
            if( String::IsEmpty($_REQUEST[self::FIELD_USERNAME]) )
            {
                self::$error = 'The username field was left blank';
                return;
            }

            if( String::IsEmpty($_REQUEST[self::FIELD_PASSWORD]) )
            {
                self::$error = 'The password field was left blank';
                return;
            }

            $account = $DB->Row('SELECT * FROM `tbx_administrator` WHERE `username`=? AND `password`=?',
                                         array($_REQUEST[self::FIELD_USERNAME],
                                               sha1($_REQUEST[self::FIELD_PASSWORD])));

            if( !$account )
            {
                self::$error = 'The supplied username/password combination is not valid';
                return;
            }
            else
            {
                $session = sha1(uniqid(rand(), true));

                $DB->Update('INSERT INTO `tbx_administrator_session` VALUES (?,?,?,?,?)',
                                     array($account['username'],
                                           $session,
                                           sha1($_SERVER['HTTP_USER_AGENT']),
                                           $_SERVER['REMOTE_ADDR'],
                                           time()));

                $DB->Update('INSERT INTO `tbx_administrator_login_history` VALUES (?,?,?)',
                                     array($account['username'],
                                           Database_MySQL::Now(),
                                           $_SERVER['REMOTE_ADDR']));

                setcookie(self::COOKIE_NAME,
                          self::FIELD_USERNAME . '=' . urlencode($account['username']) . '&' . self::FIELD_SESSION. '=' . urlencode($session),
                          $_REQUEST[self::FIELD_REMEMBER] ? time() + self::SESSION_LENGTH : null,
                          $cookie_settings['path'],
                          $cookie_settings['domain']);

                self::$username = $account['username'];
                self::$superuser = ($account['type'] == self::TYPE_SUPERUSER);
                self::$privileges = $account['privileges'];
                self::$authenticated = true;
            }
        }
        else if( isset($_COOKIE[self::COOKIE_NAME]) )
        {
            $cookie = array();
            parse_str($_COOKIE[self::COOKIE_NAME], $cookie);

            $DB->Update('DELETE FROM `tbx_administrator_session` WHERE `timestamp` < ?', array(time() - self::SESSION_LENGTH));
            $session = $DB->Row('SELECT * FROM `tbx_administrator_session` WHERE `username`=? AND `session`=? AND `browser`=? AND `ip_address`=?',
                                         array($cookie[self::FIELD_USERNAME],
                                               $cookie[self::FIELD_SESSION],
                                               sha1($_SERVER['HTTP_USER_AGENT']),
                                               $_SERVER['REMOTE_ADDR']));

            if( !$session )
            {
                setcookie(self::COOKIE_NAME, false, time() - self::SESSION_LENGTH, $cookie_settings['path'], $cookie_settings['domain']);
                self::$error = 'Your control panel session has expired';
                return;
            }
            else
            {
                $account = $DB->Row('SELECT * FROM `tbx_administrator` WHERE `username`=?', array($session['username']));

                if( !$account )
                {
                    setcookie(self::COOKIE_NAME, false, time() - self::SESSION_LENGTH, $cookie_settings['path'], $cookie_settings['domain']);
                    self::$error = 'Invalid control panel account';
                    return;
                }
                else
                {
                    self::$username = $account['username'];
                    self::$superuser = ($account['type'] == self::TYPE_SUPERUSER);
                    self::$privileges = $account['privileges'];
                    self::$authenticated = true;
                }
            }
        }

        return self::$authenticated;
    }

    public static function Logout()
    {
        $DB = GetDB();

        self::$authenticated = false;
        $cookie_settings = self::GetCookieSettings();

        if( isset($_COOKIE[self::COOKIE_NAME]) )
        {
            $cookie = array();
            parse_str($_COOKIE[self::COOKIE_NAME], $cookie);

            $DB->Update('DELETE FROM `tbx_administrator_session` WHERE `username`=? AND `session`=?',
                                 array($cookie[self::FIELD_USERNAME],
                                       $cookie[self::FIELD_SESSION]));
        }

        setcookie(self::COOKIE_NAME, false, time() - self::SESSION_LENGTH, $cookie_settings['path'], $cookie_settings['domain']);
    }

    public static function Authenticated()
    {
        return self::$authenticated;
    }

    public static function IsSuperUser()
    {
        return self::$superuser;
    }

    public static function GetPrivileges()
    {
        return self::$privileges;
    }

    public static function GetUsername()
    {
        return self::$username;
    }

    public static function GetError()
    {
        return self::$error;
    }
}

?>