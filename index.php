<?php
#-------------------------------------------------------------------#
# TubeX - Copyright � 2009 JMB Software, Inc. All Rights Reserved.  #
# This file may not be redistributed in whole or significant part.  #
# TubeX IS NOT FREE SOFTWARE                                        #
#-------------------------------------------------------------------#
# http://www.jmbsoft.com/           http://www.jmbsoft.com/license/ #
#-------------------------------------------------------------------#

require_once('classes/Config.php');
require_once('classes/Template.php');



$video_sorters = array('popular_today' => '`today_num_views` DESC',
                       'popular_week' => '`week_num_views` DESC',
                       'popular_month' => '`month_num_views` DESC',
                       'popular_all-time' => '`total_num_views` DESC',
                       'top-rated_today' => '`today_avg_rating` DESC',
                       'top-rated_week' => '`week_avg_rating` DESC',
                       'top-rated_month' => '`month_avg_rating` DESC',
                       'top-rated_all-time' => '`total_avg_rating` DESC',
                       'most-rated_today' => '`today_num_ratings` DESC',
                       'most-rated_week' => '`week_num_ratings` DESC',
                       'most-rated_month' => '`month_num_ratings` DESC',
                       'most-rated_all-time' => '`total_num_ratings` DESC',
                       'most-discussed_today' => '`today_num_comments` DESC',
                       'most-discussed_week' => '`week_num_comments` DESC',
                       'most-discussed_month' => '`month_num_comments` DESC',
                       'most-discussed_all-time' => '`total_num_comments` DESC',
                       'top-favorited_today' => '`today_num_favorited` DESC',
                       'top-favorited_week' => '`week_num_favorited` DESC',
                       'top-favorited_month' => '`month_num_favorited` DESC',
                       'top-favorited_all-time' => '`total_num_favorited` DESC');

$t = new Template(true, 3600);
$t->Assign('g_config', Config::GetAll());
$t->Assign('g_logged_in', isset($_COOKIE[LOGIN_COOKIE]));

$cookie = null;
if( isset($_COOKIE[LOGIN_COOKIE]) )
{
    parse_str($_COOKIE[LOGIN_COOKIE], $cookie);
    $t->Assign('g_username', $cookie['username']);
}

$functions = array('videos'=> 'tbxDisplayVideos',
                   'video-comments'=> 'tbxDisplayVideoComments',
                   'tag'=> 'tbxDisplayVideosByTag',
                   'search'=> 'tbxDisplayVideosBySearch',
                   'category'=> 'tbxDisplayVideosByCategory',
                   'videos-newest'=> 'tbxDisplayVideosNewest',
                   'categories'=> 'tbxDisplayCategories',
                   'profile'=> 'tbxDisplayProfile',
                   'video'=> 'tbxDisplayVideo',
                   'private'=> 'tbxDisplayPrivateVideo');

if( isset($functions[$_REQUEST['r']]) )
{
    call_user_func($functions[$_REQUEST['r']]);
}
else
{
    tbxDisplayIndex();
}

function tbxDisplayIndex()
{
    global $t;

    $t->cache_lifetime = Config::Get('cache_main');
    $t->Assign('g_loc_home', true);
    $t->Display('index.tpl');
}

function tbxDisplayProfile()
{
    global $t;

    $count_as_view = true;
    $stats = array('pv' => '');
    if( isset($_COOKIE[STATS_COOKIE]) )
    {
        $stats = unserialize($_COOKIE[STATS_COOKIE]);

        if( strstr(",{$stats['pv']},", ",{$_GET['u']},") )
        {
            $count_as_view = false;
        }
    }

    if( $count_as_view )
    {
        StatsRollover();

        $DB = GetDB();
        $DB->Update('UPDATE `tbx_user_stat` SET ' .
                    '`today_profile_views`=`today_profile_views`+1,' .
                    '`week_profile_views`=`week_profile_views`+1,' .
                    '`month_profile_views`=`month_profile_views`+1,' .
                    '`total_profile_views`=`total_profile_views`+1 ' .
                    'WHERE `username`=?',
                    array($_GET['u']));

        $stats['pv'] .= ",{$_GET['u']}";
        setcookie(STATS_COOKIE, serialize($stats), time() + 90 * 86400, Config::Get('cookie_path'), Config::Get('cookie_domain'));
    }

    $t->cache_lifetime = Config::Get('cache_profile');
    $t->Assign('g_username', $_GET['u']);
    $t->Display('user-profile.tpl', $_GET['u']);
}

function tbxDisplayCategories()
{
    global $t;

    $t->cache_lifetime = Config::Get('cache_categories');
    $t->Assign('g_loc_categories', true);
    $t->Display('categories.tpl');
}

function tbxDisplayVideo()
{
    global $t;

    tbxUpdateStats($_GET['id']);

    $t->cache_lifetime = Config::Get('cache_video');
    $t->Assign('g_private', false);
    $t->Assign('g_video_id', $_GET['id']);
    $t->Display('video-watch.tpl', $_GET['id']);
}

function tbxDisplayPrivateVideo()
{
    global $t;

    tbxUpdateStats($_GET['id']);

    $t->cache_lifetime = Config::Get('cache_video');
    $t->Assign('g_private', true);
    $t->Assign('g_video_id', $_GET['id']);
    $t->Assign('g_private_id', $_GET['pid']);
    $t->Display('video-watch.tpl', $_GET['id']);
}

function tbxUpdateStats($video_id)
{
    global $cookie, $t;

    $count_as_view = true;
    $stats = array('vv' => '');
    if( isset($_COOKIE[STATS_COOKIE]) )
    {
        $stats = unserialize($_COOKIE[STATS_COOKIE]);

        if( strstr(",{$stats['vv']},", ",$video_id,") )
        {
            $count_as_view = false;
        }
    }

    if( $count_as_view )
    {
        StatsRollover();

        $DB = GetDB();
        $DB->Update('UPDATE `tbx_video_stat` SET ' .
                    '`date_last_view`=?,' .
                    '`today_num_views`=`today_num_views`+1,' .
                    '`week_num_views`=`week_num_views`+1,' .
                    '`month_num_views`=`month_num_views`+1,' .
                    '`total_num_views`=`total_num_views`+1 ' .
                    'WHERE `video_id`=?',
                    array(Database_MySQL::Now(),
                          $video_id));

        $DB->Update('UPDATE `tbx_user_stat` JOIN `tbx_video` USING (`username`) SET ' .
                    '`today_video_views`=`today_video_views`+1,' .
                    '`week_video_views`=`week_video_views`+1,' .
                    '`month_video_views`=`month_video_views`+1,' .
                    '`total_video_views`=`total_video_views`+1 ' .
                    'WHERE `video_id`=?',
                    array($video_id));

        if( !empty($cookie) && isset($cookie['username']) )
        {
            $DB->Update('UPDATE `tbx_user_stat` SET ' .
                        '`today_videos_watched`=`today_videos_watched`+1,' .
                        '`week_videos_watched`=`week_videos_watched`+1,' .
                        '`month_videos_watched`=`month_videos_watched`+1,' .
                        '`total_videos_watched`=`total_videos_watched`+1 ' .
                        'WHERE `username`=?',
                        array($cookie['username']));
        }
        else
        {
            $ip = sprintf('%u', ip2long($_SERVER['REMOTE_ADDR']));
            if( $DB->Update('UPDATE `tbx_guest_usage` SET `watched`=`watched`+1 WHERE `ip`=?', array($ip)) == 0 )
            {
                $DB->Update('INSERT INTO `tbx_guest_usage` VALUES (?,0,1)', array($ip));
            }
        }

        $stats['vv'] .= ",{$_GET['id']}";
        setcookie(STATS_COOKIE, serialize($stats), time() + 90 * 86400, Config::Get('cookie_path'), Config::Get('cookie_domain'));
    }
}

function tbxDisplayVideos()
{
    global $t, $video_sorters;

    $_GET['p'] = empty($_GET['p']) ? 1 : $_GET['p'];
    $_GET['s'] = empty($_GET['s']) ? 'popular' : $_GET['s'];
    $_GET['t'] = empty($_GET['t']) ? 'today' : $_GET['t'];

    $t->cache_lifetime = Config::Get('cache_browse');
    $t->Assign('g_loc_videos', true);
    $t->Assign(str_replace('-', '_', 'g_loc_videos_' . $_GET['s']), true);
    $t->Assign(str_replace('-', '_', 'g_loc_' . $_GET['t']), true);
    $t->Assign('g_page_number', $_GET['p']);
    $t->Assign('g_sort', $_GET['s']);
    $t->Assign('g_timeframe', $_GET['t']);
    $t->Assign('g_videos_sorter', $video_sorters[$_GET['s']. '_' . $_GET['t']]);
    $t->Display('videos-browse.tpl', $_GET['p'] . $_GET['s'] . $_GET['t']);
}

function tbxDisplayVideosNewest()
{
    global $t;

    $_GET['p'] = empty($_GET['p']) ? 1 : $_GET['p'];

    $t->cache_lifetime = Config::Get('cache_browse');
    $t->Assign('g_loc_newest', true);
    $t->Assign('g_page_number', $_GET['p']);
    $t->Display('videos-newest.tpl', $_GET['p']);
}

function tbxDisplayVideosByCategory()
{
    global $t;

    $_GET['p'] = empty($_GET['p']) ? 1 : $_GET['p'];

    $t->cache_lifetime = Config::Get('cache_browse');
    $t->Assign('g_category_url', $_GET['c']);
    $t->Assign('g_page_number', $_GET['p']);
    $t->Display('videos-by-category.tpl', $_GET['c'] . $_GET['p']);
}

function tbxDisplayVideosBySearch()
{
    global $t;

    // Search term tracking
    if( empty($_GET['p']) && !empty($_GET['term']) )
    {
        $DB = GetDB();
        if( $DB->Update('UPDATE `tbx_search_term_new` SET `frequency`=`frequency`+1 WHERE `term`=?', array($_GET['term'])) == 0 )
        {
            $DB->Update('INSERT INTO `tbx_search_term_new` VALUES (?,?,?)', array(null, $_GET['term'], 1));
        }
    }

    $_GET['p'] = empty($_GET['p']) ? 1 : $_GET['p'];

    $t->cache_lifetime = Config::Get('cache_search');
    $t->Assign('g_term', $_GET['term']);
    $t->Assign('g_category_id', $_GET['c']);
    $t->Assign('g_page_number', $_GET['p']);
    $t->Assign('g_searchmode', true); // Set search to boolean mode
    $t->Display('videos-by-search.tpl', $_GET['term'] . $_GET['p'] . $_GET['c']);
}

function tbxDisplayVideosByTag()
{
    global $t;

    $_GET['p'] = empty($_GET['p']) ? 1 : $_GET['p'];

    $t->cache_lifetime = Config::Get('cache_browse');
    $t->Assign('g_tag', $_GET['tag']);
    $t->Assign('g_page_number', $_GET['p']);
    $t->Display('videos-by-tag.tpl', $_GET['tag'] . $_GET['p']);
}

function tbxDisplayVideoComments()
{
    global $t;

    $_GET['p'] = empty($_GET['p']) ? 1 : $_GET['p'];
    $cache_id = $_GET['id'] . $_GET['p'];
    $template = 'video-comments' . (preg_match('~^(iframe|ajax)$~', $_GET['d']) ? '-' . $_GET['d'] : '') . '.tpl';

    $t->cache_lifetime = Config::Get('cache_comments');
    $t->Assign('g_video_id', $_GET['id']);
    $t->Assign('g_page_number', $_GET['p']);
    $t->Display($template, $cache_id);
}

?>