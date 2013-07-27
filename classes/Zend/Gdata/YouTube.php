<?php


require_once 'Zend/Gdata/Media.php';

require_once 'Zend/Gdata/YouTube/VideoEntry.php';

require_once 'Zend/Gdata/YouTube/VideoFeed.php';

require_once 'Zend/Gdata/YouTube/CommentFeed.php';

require_once 'Zend/Gdata/YouTube/PlaylistListFeed.php';

require_once 'Zend/Gdata/YouTube/SubscriptionFeed.php';

require_once 'Zend/Gdata/YouTube/ContactFeed.php';

require_once 'Zend/Gdata/YouTube/PlaylistVideoFeed.php';

require_once 'Zend/Gdata/YouTube/ActivityFeed.php';

require_once 'Zend/Gdata/YouTube/InboxFeed.php';

class Zend_Gdata_YouTube extends Zend_Gdata_Media
{

    const AUTH_SERVICE_NAME = 'youtube';
    const CLIENTLOGIN_URL = 'https://www.google.com/youtube/accounts/ClientLogin';

    const STANDARD_TOP_RATED_URI = 'http://gdata.youtube.com/feeds/standardfeeds/top_rated';
    const STANDARD_MOST_VIEWED_URI = 'http://gdata.youtube.com/feeds/standardfeeds/most_viewed';
    const STANDARD_RECENTLY_FEATURED_URI = 'http://gdata.youtube.com/feeds/standardfeeds/recently_featured';
    const STANDARD_WATCH_ON_MOBILE_URI = 'http://gdata.youtube.com/feeds/standardfeeds/watch_on_mobile';

    const STANDARD_TOP_RATED_URI_V2 =
        'http://gdata.youtube.com/feeds/api/standardfeeds/top_rated';
    const STANDARD_MOST_VIEWED_URI_V2 =
        'http://gdata.youtube.com/feeds/api/standardfeeds/most_viewed';
    const STANDARD_RECENTLY_FEATURED_URI_V2 =
        'http://gdata.youtube.com/feeds/api/standardfeeds/recently_featured';
    const STANDARD_WATCH_ON_MOBILE_URI_V2 =
        'http://gdata.youtube.com/feeds/api/standardfeeds/watch_on_mobile';

    const USER_URI = 'http://gdata.youtube.com/feeds/api/users';
    const VIDEO_URI = 'http://gdata.youtube.com/feeds/api/videos';
    const PLAYLIST_REL = 'http://gdata.youtube.com/schemas/2007#playlist';
    const USER_UPLOADS_REL = 'http://gdata.youtube.com/schemas/2007#user.uploads';
    const USER_PLAYLISTS_REL = 'http://gdata.youtube.com/schemas/2007#user.playlists';
    const USER_SUBSCRIPTIONS_REL = 'http://gdata.youtube.com/schemas/2007#user.subscriptions';
    const USER_CONTACTS_REL = 'http://gdata.youtube.com/schemas/2007#user.contacts';
    const USER_FAVORITES_REL = 'http://gdata.youtube.com/schemas/2007#user.favorites';
    const VIDEO_RESPONSES_REL = 'http://gdata.youtube.com/schemas/2007#video.responses';
    const VIDEO_RATINGS_REL = 'http://gdata.youtube.com/schemas/2007#video.ratings';
    const VIDEO_COMPLAINTS_REL = 'http://gdata.youtube.com/schemas/2007#video.complaints';
    const ACTIVITY_FEED_URI = 'http://gdata.youtube.com/feeds/api/events';
    const FRIEND_ACTIVITY_FEED_URI =
        'http://gdata.youtube.com/feeds/api/users/default/friendsactivity';

    const INBOX_FEED_URI =
        'http://gdata.youtube.com/feeds/api/users/default/inbox';

    const ACTIVITY_FEED_MAX_USERS = 20;

    const FAVORITES_URI_SUFFIX = 'favorites';

    const UPLOADS_URI_SUFFIX = 'uploads';

    const RESPONSES_URI_SUFFIX = 'responses';

    const RELATED_URI_SUFFIX = 'related';

    const INBOX_URI_SUFFIX = 'inbox';

    public static $namespaces = array(
        array('yt', 'http://gdata.youtube.com/schemas/2007', 1, 0),
        array('georss', 'http://www.georss.org/georss', 1, 0),
        array('gml', 'http://www.opengis.net/gml', 1, 0),
        array('media', 'http://search.yahoo.com/mrss/', 1, 0)
    );

    public function __construct($client = null,
        $applicationId = 'MyCompany-MyApp-1.0', $clientId = null,
        $developerKey = null)
    {
        $this->registerPackage('Zend_Gdata_YouTube');
        $this->registerPackage('Zend_Gdata_YouTube_Extension');
        $this->registerPackage('Zend_Gdata_Media');
        $this->registerPackage('Zend_Gdata_Media_Extension');

        // NOTE This constructor no longer calls the parent constructor
        $this->setHttpClient($client, $applicationId, $clientId, $developerKey);
    }

    public function setHttpClient($client,
        $applicationId = 'MyCompany-MyApp-1.0', $clientId = null,
        $developerKey = null)
    {
        if ($client === null) {
            $client = new Zend_Http_Client();
        }
        if (!$client instanceof Zend_Http_Client) {
            require_once 'Zend/Gdata/App/HttpException.php';
            throw new Zend_Gdata_App_HttpException(
                'Argument is not an instance of Zend_Http_Client.');
        }

        if ($clientId != null) {
            $client->setHeaders('X-GData-Client', $clientId);
        }

        if ($developerKey != null) {
            $client->setHeaders('X-GData-Key', 'key='. $developerKey);
        }

        return parent::setHttpClient($client, $applicationId);
    }

    public function getVideoFeed($location = null)
    {
        if ($location == null) {
            $uri = self::VIDEO_URI;
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_VideoFeed');
    }

    public function getVideoEntry($videoId = null, $location = null,
        $fullEntry = false)
    {
        if ($videoId !== null) {
            if ($fullEntry) {
                return $this->getFullVideoEntry($videoId);
            } else {
                $uri = self::VIDEO_URI . "/" . $videoId;
            }
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getEntry($uri, 'Zend_Gdata_YouTube_VideoEntry');
    }

    public function getFullVideoEntry($videoId)
    {
        $uri = self::USER_URI . "/default/" .
            self::UPLOADS_URI_SUFFIX . "/$videoId";
        return parent::getEntry($uri, 'Zend_Gdata_YouTube_VideoEntry');
    }

    public function getRelatedVideoFeed($videoId = null, $location = null)
    {
        if ($videoId !== null) {
            $uri = self::VIDEO_URI . "/" . $videoId . "/" .
                self::RELATED_URI_SUFFIX;
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_VideoFeed');
    }

    public function getVideoResponseFeed($videoId = null, $location = null)
    {
        if ($videoId !== null) {
            $uri = self::VIDEO_URI . "/" . $videoId . "/" .
                self::RESPONSES_URI_SUFFIX;
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_VideoFeed');
    }

    public function getVideoCommentFeed($videoId = null, $location = null)
    {
        if ($videoId !== null) {
            $uri = self::VIDEO_URI . "/" . $videoId . "/comments";
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_CommentFeed');
    }

    public function getTopRatedVideoFeed($location = null)
    {
        $standardFeedUri = self::STANDARD_TOP_RATED_URI;

        if ($this->getMajorProtocolVersion() == 2) {
            $standardFeedUri = self::STANDARD_TOP_RATED_URI_V2;
        }

        if ($location == null) {
            $uri = $standardFeedUri;
        } else if ($location instanceof Zend_Gdata_Query) {
            if ($location instanceof Zend_Gdata_YouTube_VideoQuery) {
                if (!isset($location->url)) {
                    $location->setFeedType('top rated');
                }
            }
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_VideoFeed');
    }

    public function getMostViewedVideoFeed($location = null)
    {
        $standardFeedUri = self::STANDARD_MOST_VIEWED_URI;

        if ($this->getMajorProtocolVersion() == 2) {
            $standardFeedUri = self::STANDARD_MOST_VIEWED_URI_V2;
        }

        if ($location == null) {
            $uri = $standardFeedUri;
        } else if ($location instanceof Zend_Gdata_Query) {
            if ($location instanceof Zend_Gdata_YouTube_VideoQuery) {
                if (!isset($location->url)) {
                    $location->setFeedType('most viewed');
                }
            }
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_VideoFeed');
    }

    public function getRecentlyFeaturedVideoFeed($location = null)
    {
        $standardFeedUri = self::STANDARD_RECENTLY_FEATURED_URI;

        if ($this->getMajorProtocolVersion() == 2) {
            $standardFeedUri = self::STANDARD_RECENTLY_FEATURED_URI_V2;
        }

        if ($location == null) {
            $uri = $standardFeedUri;
        } else if ($location instanceof Zend_Gdata_Query) {
            if ($location instanceof Zend_Gdata_YouTube_VideoQuery) {
                if (!isset($location->url)) {
                    $location->setFeedType('recently featured');
                }
            }
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_VideoFeed');
    }

    public function getWatchOnMobileVideoFeed($location = null)
    {
        $standardFeedUri = self::STANDARD_WATCH_ON_MOBILE_URI;

        if ($this->getMajorProtocolVersion() == 2) {
            $standardFeedUri = self::STANDARD_WATCH_ON_MOBILE_URI_V2;
        }

        if ($location == null) {
            $uri = $standardFeedUri;
        } else if ($location instanceof Zend_Gdata_Query) {
            if ($location instanceof Zend_Gdata_YouTube_VideoQuery) {
                if (!isset($location->url)) {
                    $location->setFeedType('watch on mobile');
                }
            }
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_VideoFeed');
    }

    public function getPlaylistListFeed($user = null, $location = null)
    {
        if ($user !== null) {
            $uri = self::USER_URI . '/' . $user . '/playlists';
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_PlaylistListFeed');
    }

    public function getPlaylistVideoFeed($location)
    {
        if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_PlaylistVideoFeed');
    }

    public function getSubscriptionFeed($user = null, $location = null)
    {
        if ($user !== null) {
            $uri = self::USER_URI . '/' . $user . '/subscriptions';
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_SubscriptionFeed');
    }

    public function getContactFeed($user = null, $location = null)
    {
        if ($user !== null) {
            $uri = self::USER_URI . '/' . $user . '/contacts';
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_ContactFeed');
    }

    public function getUserUploads($user = null, $location = null)
    {
        if ($user !== null) {
            $uri = self::USER_URI . '/' . $user . '/' .
                   self::UPLOADS_URI_SUFFIX;
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_VideoFeed');
    }

    public function getUserFavorites($user = null, $location = null)
    {
        if ($user !== null) {
            $uri = self::USER_URI . '/' . $user . '/' .
                   self::FAVORITES_URI_SUFFIX;
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_VideoFeed');
    }

    public function getUserProfile($user = null, $location = null)
    {
        if ($user !== null) {
            $uri = self::USER_URI . '/' . $user;
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getEntry($uri, 'Zend_Gdata_YouTube_UserProfileEntry');
    }

    public static function parseFormUploadTokenResponse($response)
    {
        // Load the feed as an XML DOMDocument object
        @ini_set('track_errors', 1);
        $doc = new DOMDocument();
        $success = @$doc->loadXML($response);
        @ini_restore('track_errors');

        if (!$success) {
            require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception(
                "Zend_Gdata_YouTube::parseFormUploadTokenResponse - " .
                "DOMDocument cannot parse XML: $php_errormsg");
        }
        $responseElement = $doc->getElementsByTagName('response')->item(0);

        $urlText = null;
        $tokenText = null;
        if ($responseElement != null) {
            $urlElement =
                $responseElement->getElementsByTagName('url')->item(0);
            $tokenElement =
                $responseElement->getElementsByTagName('token')->item(0);

            if ($urlElement && $urlElement->hasChildNodes() &&
                $tokenElement && $tokenElement->hasChildNodes()) {

                $urlText = $urlElement->firstChild->nodeValue;
                $tokenText = $tokenElement->firstChild->nodeValue;
            }
        }

        if ($tokenText != null && $urlText != null) {
            return array('token' => $tokenText, 'url' => $urlText);
        } else {
            require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception(
                'Form upload token not found in response');
        }
    }

    public function getFormUploadToken($videoEntry,
        $url='http://gdata.youtube.com/action/GetUploadToken')
    {
        if ($url != null && is_string($url)) {
            // $response is a Zend_Http_response object
            $response = $this->post($videoEntry, $url);
            return self::parseFormUploadTokenResponse($response->getBody());
        } else {
            require_once 'Zend/Gdata/App/HttpException.php';
            throw new Zend_Gdata_App_Exception(
                'Url must be provided as a string URL');
        }
    }

    public function getActivityForUser($username)
    {
        if ($this->getMajorProtocolVersion() == 1) {
            require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('User activity feeds ' .
                'are not available in API version 1.');
        }

        $uri = null;
        if ($username instanceof Zend_Gdata_Query) {
            $uri = $username->getQueryUrl();
        } else {
            if (count(explode(',', $username)) >
                self::ACTIVITY_FEED_MAX_USERS) {
                require_once 'Zend/Gdata/App/InvalidArgumentException.php';
                throw new Zend_Gdata_App_InvalidArgumentException(
                    'Activity feed can only retrieve for activity for up to ' .
                    self::ACTIVITY_FEED_MAX_USERS .  ' users per request');
            }
            $uri = self::ACTIVITY_FEED_URI . '?author=' . $username;
        }

        return parent::getFeed($uri, 'Zend_Gdata_YouTube_ActivityFeed');
    }

    public function getFriendActivityForCurrentUser()
    {
        if (!$this->isAuthenticated()) {
            require_once 'Zend/Gdata/YouTube/App/Exception.php';
            throw new Zend_Gdata_App_Exception('You must be authenticated to ' .
                'use the getFriendActivityForCurrentUser function in Zend_' .
                'Gdata_YouTube.');
        }
        return parent::getFeed(self::FRIEND_ACTIVITY_FEED_URI,
            'Zend_Gdata_YouTube_ActivityFeed');
    }

    public function getInboxFeedForCurrentUser()
    {
        if (!$this->isAuthenticated()) {
            require_once 'Zend/Gdata/YouTube/App/Exception.php';
            throw new Zend_Gdata_App_Exception('You must be authenticated to ' .
                'use the getInboxFeedForCurrentUser function in Zend_' .
                'Gdata_YouTube.');
        }

        return parent::getFeed(self::INBOX_FEED_URI,
            'Zend_Gdata_YouTube_InboxFeed');
    }

    public function sendVideoMessage($body, $videoEntry = null,
        $videoId = null, $recipientUserName)
    {
        if (!$videoId && !$videoEntry) {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                'Expecting either a valid videoID or a videoEntry object in ' .
                'Zend_Gdata_YouTube->sendVideoMessage().');
        }

        $messageEntry = new Zend_Gdata_YouTube_InboxEntry();
        
        if ($this->getMajorProtocolVersion() == null ||
            $this->getMajorProtocolVersion() == 1) {

            if (!$videoId) {
                $videoId = $videoEntry->getVideoId();
            } elseif (strlen($videoId) < 12) {
                //Append the full URI
                $videoId = self::VIDEO_URI . '/' . $videoId;
            }

            $messageEntry->setId($this->newId($videoId));
            // TODO there seems to be a bug where v1 inbox entries dont
            // retain their description...
            $messageEntry->setDescription(
                new Zend_Gdata_YouTube_Extension_Description($body));

        } else {
            if (!$videoId) {
                $videoId = $videoEntry->getVideoId();
                $videoId = substr($videoId, strrpos($videoId, ':'));
            }
            $messageEntry->setId($this->newId($videoId));
            $messageEntry->setSummary($this->newSummary($body));
        }

        $insertUrl = 'http://gdata.youtube.com/feeds/api/users/' .
            $recipientUserName . '/inbox';
        $response = $this->insertEntry($messageEntry, $insertUrl,
            'Zend_Gdata_YouTube_InboxEntry');
        return $response;
    }

}
