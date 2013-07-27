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


class AuthenticateUser
{

    const FIELD_USERNAME = 'username';

    const FIELD_PASSWORD = 'password';

    const FIELD_REMEMBER = 'remember';

    const FIELD_SESSION = 'session';

    const REMEMBER_PERIOD = 7776000;

    private static $username = null;

    private static $authenticated = false;

    private static $error = null;

    public static function Login($fail_function = null)
    {
        $DB = GetDB();
        self::$authenticated = false;
        self::$username = null;

        try
        {
            if( isset($_REQUEST[self::FIELD_USERNAME]) )
            {
                if( String::IsEmpty($_REQUEST[self::FIELD_USERNAME]) )
                {
                    throw new Exception(_T('Validation:Required', _T('Label:Username')));
                }

                if( String::IsEmpty($_REQUEST[self::FIELD_PASSWORD]) )
                {
                    throw new Exception(_T('Validation:Required', _T('Label:Password')));
                }

                $user = $DB->Row('SELECT * FROM `tbx_user` WHERE `username`=? AND `password`=?',
                                 array($_REQUEST[self::FIELD_USERNAME],
                                       sha1($_REQUEST[self::FIELD_PASSWORD])));

                if( !$user )
                {
                    throw new Exception(_T('Validation:Invalid Login'));
                }
                else
                {
                    if( $user['status'] != STATUS_ACTIVE )
                    {
                        throw new Exception(_T('Validation:Inactive Account'));
                    }

                    $session = sha1(uniqid(rand(), true));

                    $DB->Update('UPDATE `tbx_user_stat` SET `date_last_login`=? WHERE `username`=?', array(Database_MySQL::Now(), $user['username']));
                    $DB->Update('INSERT INTO `tbx_user_session` VALUES (?,?,?,?,?)',
                                         array($user['username'],
                                               $session,
                                               sha1($_SERVER['HTTP_USER_AGENT']),
                                               $_SERVER['REMOTE_ADDR'],
                                               time()));

                    setcookie(LOGIN_COOKIE,
                              self::FIELD_USERNAME . '=' . urlencode($user['username']) . '&' . self::FIELD_SESSION. '=' . urlencode($session),
                              $_REQUEST[self::FIELD_REMEMBER] ? time() + self::REMEMBER_PERIOD : null,
                              Config::Get('cookie_path'),
                              Config::Get('cookie_domain'));

                    self::$username = $user['username'];
                    self::$authenticated = true;
                }
            }
            else if( isset($_COOKIE[LOGIN_COOKIE]) )
            {
                $cookie = array();
                parse_str(html_entity_decode($_COOKIE[LOGIN_COOKIE]), $cookie);

                $DB->Update('DELETE FROM `tbx_user_session` WHERE `timestamp` < ?', array(time() - self::REMEMBER_PERIOD));
                $session = $DB->Row('SELECT * FROM `tbx_user_session` WHERE `username`=? AND `session`=?',
                                             array($cookie[self::FIELD_USERNAME],
                                                   $cookie[self::FIELD_SESSION]));

                if( !$session )
                {
                    setcookie(LOGIN_COOKIE, false, time() - 604800, Config::Get('cookie_path'), Config::Get('cookie_domain'));
                    throw new Exception(_T('Validation:Session Expired'));
                }
                else
                {
                    $user = $DB->Row('SELECT * FROM `tbx_user` WHERE `username`=?', array($session['username']));

                    if( !$user )
                    {
                        setcookie(LOGIN_COOKIE, false, time() - 604800, Config::Get('cookie_path'), Config::Get('cookie_domain'));
                        throw new Exception(_T('Validation:Invalid Account'));
                    }
                    else
                    {
                        if( $user['status'] != STATUS_ACTIVE )
                        {
                            throw new Exception(_T('Validation:Inactive Account'));
                        }

                        self::$username = $user['username'];
                        self::$authenticated = true;
                    }
                }
            }
        }
        catch(Exception $e)
        {
            self::$error = $e->getMessage();
            self::$authenticated = false;
        }

        if( !self::$authenticated && function_exists($fail_function) )
        {
            call_user_func($fail_function);
            exit;
        }

        return self::$authenticated;
    }

    public static function Logout()
    {
        $DB = GetDB();

        self::$authenticated = false;

        if( isset($_COOKIE[LOGIN_COOKIE]) )
        {
            $cookie = array();
            parse_str($_COOKIE[LOGIN_COOKIE], $cookie);

            $DB->Update('DELETE FROM `tbx_user_session` WHERE `username`=? AND `session`=?',
                                 array($cookie[self::FIELD_USERNAME],
                                       $cookie[self::FIELD_SESSION]));
        }

        setcookie(LOGIN_COOKIE, false, time() - 604800, Config::Get('cookie_path'), Config::Get('cookie_domain'));
    }

    public static function Authenticated()
    {
        return self::$authenticated;
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