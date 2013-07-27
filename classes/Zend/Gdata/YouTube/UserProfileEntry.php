<?php


require_once 'Zend/Gdata/Entry.php';

require_once 'Zend/Gdata/Extension/FeedLink.php';

require_once 'Zend/Gdata/YouTube/Extension/Description.php';

require_once 'Zend/Gdata/YouTube/Extension/AboutMe.php';

require_once 'Zend/Gdata/YouTube/Extension/Age.php';

require_once 'Zend/Gdata/YouTube/Extension/Username.php';

require_once 'Zend/Gdata/YouTube/Extension/Books.php';

require_once 'Zend/Gdata/YouTube/Extension/Company.php';

require_once 'Zend/Gdata/YouTube/Extension/Hobbies.php';

require_once 'Zend/Gdata/YouTube/Extension/Hometown.php';

require_once 'Zend/Gdata/YouTube/Extension/Location.php';

require_once 'Zend/Gdata/YouTube/Extension/Movies.php';

require_once 'Zend/Gdata/YouTube/Extension/Music.php';

require_once 'Zend/Gdata/YouTube/Extension/Occupation.php';

require_once 'Zend/Gdata/YouTube/Extension/School.php';

require_once 'Zend/Gdata/YouTube/Extension/Gender.php';

require_once 'Zend/Gdata/YouTube/Extension/Relationship.php';

require_once 'Zend/Gdata/YouTube/Extension/FirstName.php';

require_once 'Zend/Gdata/YouTube/Extension/LastName.php';

require_once 'Zend/Gdata/YouTube/Extension/Statistics.php';

require_once 'Zend/Gdata/Media/Extension/MediaThumbnail.php';

class Zend_Gdata_YouTube_UserProfileEntry extends Zend_Gdata_Entry
{

    protected $_entryClassName = 'Zend_Gdata_YouTube_UserProfileEntry';

    protected $_feedLink = array();

    protected $_username = null;

    protected $_description = null;

    protected $_aboutMe = null;

    protected $_age = null;

    protected $_books = null;

    protected $_company = null;

    protected $_hobbies = null;

    protected $_hometown = null;

    protected $_location = null;

    protected $_movies = null;

    protected $_music = null;

    protected $_occupation = null;

    protected $_school = null;

    protected $_gender = null;

    protected $_relationship = null;

    protected $_firstName = null;

    protected $_lastName = null;

    protected $_statistics = null;

    protected $_thumbnail = null;

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_description != null) {
            $element->appendChild($this->_description->getDOM($element->ownerDocument));
        }
        if ($this->_aboutMe != null) {
            $element->appendChild($this->_aboutMe->getDOM($element->ownerDocument));
        }
        if ($this->_age != null) {
            $element->appendChild($this->_age->getDOM($element->ownerDocument));
        }
        if ($this->_username != null) {
            $element->appendChild($this->_username->getDOM($element->ownerDocument));
        }
        if ($this->_books != null) {
            $element->appendChild($this->_books->getDOM($element->ownerDocument));
        }
        if ($this->_company != null) {
            $element->appendChild($this->_company->getDOM($element->ownerDocument));
        }
        if ($this->_hobbies != null) {
            $element->appendChild($this->_hobbies->getDOM($element->ownerDocument));
        }
        if ($this->_hometown != null) {
            $element->appendChild($this->_hometown->getDOM($element->ownerDocument));
        }
        if ($this->_location != null) {
            $element->appendChild($this->_location->getDOM($element->ownerDocument));
        }
        if ($this->_movies != null) {
            $element->appendChild($this->_movies->getDOM($element->ownerDocument));
        }
        if ($this->_music != null) {
            $element->appendChild($this->_music->getDOM($element->ownerDocument));
        }
        if ($this->_occupation != null) {
            $element->appendChild($this->_occupation->getDOM($element->ownerDocument));
        }
        if ($this->_school != null) {
            $element->appendChild($this->_school->getDOM($element->ownerDocument));
        }
        if ($this->_gender != null) {
            $element->appendChild($this->_gender->getDOM($element->ownerDocument));
        }
        if ($this->_relationship != null) {
            $element->appendChild($this->_relationship->getDOM($element->ownerDocument));
        }
        if ($this->_firstName != null) {
            $element->appendChild($this->_firstName->getDOM($element->ownerDocument));
        }
        if ($this->_lastName != null) {
            $element->appendChild($this->_lastName->getDOM($element->ownerDocument));
        }
        if ($this->_statistics != null) {
            $element->appendChild($this->_statistics->getDOM($element->ownerDocument));
        }
        if ($this->_thumbnail != null) {
            $element->appendChild($this->_thumbnail->getDOM($element->ownerDocument));
        }
        if ($this->_feedLink != null) {
            foreach ($this->_feedLink as $feedLink) {
                $element->appendChild($feedLink->getDOM($element->ownerDocument));
            }
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('yt') . ':' . 'description':
            $description = new Zend_Gdata_YouTube_Extension_Description();
            $description->transferFromDOM($child);
            $this->_description = $description;
            break;
        case $this->lookupNamespace('yt') . ':' . 'aboutMe':
            $aboutMe = new Zend_Gdata_YouTube_Extension_AboutMe();
            $aboutMe->transferFromDOM($child);
            $this->_aboutMe = $aboutMe;
            break;
        case $this->lookupNamespace('yt') . ':' . 'age':
            $age = new Zend_Gdata_YouTube_Extension_Age();
            $age->transferFromDOM($child);
            $this->_age = $age;
            break;
        case $this->lookupNamespace('yt') . ':' . 'username':
            $username = new Zend_Gdata_YouTube_Extension_Username();
            $username->transferFromDOM($child);
            $this->_username = $username;
            break;
        case $this->lookupNamespace('yt') . ':' . 'books':
            $books = new Zend_Gdata_YouTube_Extension_Books();
            $books->transferFromDOM($child);
            $this->_books = $books;
            break;
        case $this->lookupNamespace('yt') . ':' . 'company':
            $company = new Zend_Gdata_YouTube_Extension_Company();
            $company->transferFromDOM($child);
            $this->_company = $company;
            break;
        case $this->lookupNamespace('yt') . ':' . 'hobbies':
            $hobbies = new Zend_Gdata_YouTube_Extension_Hobbies();
            $hobbies->transferFromDOM($child);
            $this->_hobbies = $hobbies;
            break;
        case $this->lookupNamespace('yt') . ':' . 'hometown':
            $hometown = new Zend_Gdata_YouTube_Extension_Hometown();
            $hometown->transferFromDOM($child);
            $this->_hometown = $hometown;
            break;
        case $this->lookupNamespace('yt') . ':' . 'location':
            $location = new Zend_Gdata_YouTube_Extension_Location();
            $location->transferFromDOM($child);
            $this->_location = $location;
            break;
        case $this->lookupNamespace('yt') . ':' . 'movies':
            $movies = new Zend_Gdata_YouTube_Extension_Movies();
            $movies->transferFromDOM($child);
            $this->_movies = $movies;
            break;
        case $this->lookupNamespace('yt') . ':' . 'music':
            $music = new Zend_Gdata_YouTube_Extension_Music();
            $music->transferFromDOM($child);
            $this->_music = $music;
            break;
        case $this->lookupNamespace('yt') . ':' . 'occupation':
            $occupation = new Zend_Gdata_YouTube_Extension_Occupation();
            $occupation->transferFromDOM($child);
            $this->_occupation = $occupation;
            break;
        case $this->lookupNamespace('yt') . ':' . 'school':
            $school = new Zend_Gdata_YouTube_Extension_School();
            $school->transferFromDOM($child);
            $this->_school = $school;
            break;
        case $this->lookupNamespace('yt') . ':' . 'gender':
            $gender = new Zend_Gdata_YouTube_Extension_Gender();
            $gender->transferFromDOM($child);
            $this->_gender = $gender;
            break;
        case $this->lookupNamespace('yt') . ':' . 'relationship':
            $relationship = new Zend_Gdata_YouTube_Extension_Relationship();
            $relationship->transferFromDOM($child);
            $this->_relationship = $relationship;
            break;
        case $this->lookupNamespace('yt') . ':' . 'firstName':
            $firstName = new Zend_Gdata_YouTube_Extension_FirstName();
            $firstName->transferFromDOM($child);
            $this->_firstName = $firstName;
            break;
        case $this->lookupNamespace('yt') . ':' . 'lastName':
            $lastName = new Zend_Gdata_YouTube_Extension_LastName();
            $lastName->transferFromDOM($child);
            $this->_lastName = $lastName;
            break;
        case $this->lookupNamespace('yt') . ':' . 'statistics':
            $statistics = new Zend_Gdata_YouTube_Extension_Statistics();
            $statistics->transferFromDOM($child);
            $this->_statistics = $statistics;
            break;
        case $this->lookupNamespace('media') . ':' . 'thumbnail':
            $thumbnail = new Zend_Gdata_Media_Extension_MediaThumbnail();
            $thumbnail->transferFromDOM($child);
            $this->_thumbnail = $thumbnail;
            break;
        case $this->lookupNamespace('gd') . ':' . 'feedLink':
            $feedLink = new Zend_Gdata_Extension_FeedLink();
            $feedLink->transferFromDOM($child);
            $this->_feedLink[] = $feedLink;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    public function setAboutMe($aboutMe = null)
    {
        if (($this->getMajorProtocolVersion() == null) ||
           ($this->getMajorProtocolVersion() == 1)) {
            require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('The setAboutMe ' .
                ' method is only supported as of version 2 of the YouTube ' .
                'API.');
        } else {
            $this->_aboutMe = $aboutMe;
            return $this;
        }
    }

    public function getAboutMe()
    {
        if (($this->getMajorProtocolVersion() == null) ||
           ($this->getMajorProtocolVersion() == 1)) {
            require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('The getAboutMe ' .
                ' method is only supported as of version 2 of the YouTube ' .
                'API.');
        } else {
            return $this->_aboutMe;
        }
    }

    public function setFirstName($firstName = null)
    {
        if (($this->getMajorProtocolVersion() == null) ||
           ($this->getMajorProtocolVersion() == 1)) {
            require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('The setFirstName ' .
                ' method is only supported as of version 2 of the YouTube ' .
                'API.');
        } else {
            $this->_firstName = $firstName;
            return $this;
        }
    }

    public function getFirstName()
    {
        if (($this->getMajorProtocolVersion() == null) ||
           ($this->getMajorProtocolVersion() == 1)) {
            require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('The getFirstName ' .
                ' method is only supported as of version 2 of the YouTube ' .
                'API.');
        } else {
            return $this->_firstName;
        }
    }

    public function setLastName($lastName = null)
    {
        if (($this->getMajorProtocolVersion() == null) ||
           ($this->getMajorProtocolVersion() == 1)) {
            require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('The setLastName ' .
                ' method is only supported as of version 2 of the YouTube ' .
                'API.');
        } else {
            $this->_lastName = $lastName;
            return $this;
        }
    }

    public function getLastName()
    {
        if (($this->getMajorProtocolVersion() == null) ||
           ($this->getMajorProtocolVersion() == 1)) {
            require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('The getLastName ' .
                ' method is only supported as of version 2 of the YouTube ' .
                'API.');
        } else {
            return $this->_lastName;
        }
    }

    public function getStatistics()
    {
        if (($this->getMajorProtocolVersion() == null) ||
           ($this->getMajorProtocolVersion() == 1)) {
            require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('The getStatistics ' .
                ' method is only supported as of version 2 of the YouTube ' .
                'API.');
        } else {
            return $this->_statistics;
        }
    }

    public function getThumbnail()
    {
        if (($this->getMajorProtocolVersion() == null) ||
           ($this->getMajorProtocolVersion() == 1)) {
            require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('The getThumbnail ' .
                ' method is only supported as of version 2 of the YouTube ' .
                'API.');
        } else {
            return $this->_thumbnail;
        }
    }

    public function setAge($age = null)
    {
        $this->_age = $age;
        return $this;
    }

    public function getAge()
    {
        return $this->_age;
    }

    public function setUsername($username = null)
    {
        $this->_username = $username;
        return $this;
    }

    public function getUsername()
    {
        return $this->_username;
    }

    public function setBooks($books = null)
    {
        $this->_books = $books;
        return $this;
    }

    public function getBooks()
    {
        return $this->_books;
    }

    public function setCompany($company = null)
    {
        $this->_company = $company;
        return $this;
    }

    public function getCompany()
    {
        return $this->_company;
    }

    public function setHobbies($hobbies = null)
    {
        $this->_hobbies = $hobbies;
        return $this;
    }

    public function getHobbies()
    {
        return $this->_hobbies;
    }

    public function setHometown($hometown = null)
    {
        $this->_hometown = $hometown;
        return $this;
    }

    public function getHometown()
    {
        return $this->_hometown;
    }

    public function setLocation($location = null)
    {
        $this->_location = $location;
        return $this;
    }

    public function getLocation()
    {
        return $this->_location;
    }

    public function setMovies($movies = null)
    {
        $this->_movies = $movies;
        return $this;
    }

    public function getMovies()
    {
        return $this->_movies;
    }

    public function setMusic($music = null)
    {
        $this->_music = $music;
        return $this;
    }

    public function getMusic()
    {
        return $this->_music;
    }

    public function setOccupation($occupation = null)
    {
        $this->_occupation = $occupation;
        return $this;
    }

    public function getOccupation()
    {
        return $this->_occupation;
    }

    public function setSchool($school = null)
    {
        $this->_school = $school;
        return $this;
    }

    public function getSchool()
    {
        return $this->_school;
    }

    public function setGender($gender = null)
    {
        $this->_gender = $gender;
        return $this;
    }

    public function getGender()
    {
        return $this->_gender;
    }

    public function setRelationship($relationship = null)
    {
        $this->_relationship = $relationship;
        return $this;
    }

    public function getRelationship()
    {
        return $this->_relationship;
    }

    public function setFeedLink($feedLink = null)
    {
        $this->_feedLink = $feedLink;
        return $this;
    }

    public function getFeedLink($rel = null)
    {
        if ($rel == null) {
            return $this->_feedLink;
        } else {
            foreach ($this->_feedLink as $feedLink) {
                if ($feedLink->rel == $rel) {
                    return $feedLink;
                }
            }
            return null;
        }
    }

    public function getFeedLinkHref($rel)
    {
        $feedLink = $this->getFeedLink($rel);
        if ($feedLink !== null) {
            return $feedLink->href;
        } else {
            return null;
        }
    }

    public function getPlaylistListFeedUrl()
    {
        return getFeedLinkHref(Zend_Gdata_YouTube::USER_PLAYLISTS_REL);
    }

    public function getUploadsFeedUrl()
    {
        return getFeedLinkHref(Zend_Gdata_YouTube::USER_UPLOADS_REL);
    }

    public function getSubscriptionsFeedUrl()
    {
        return getFeedLinkHref(Zend_Gdata_YouTube::USER_SUBSCRIPTIONS_REL);
    }

    public function getContactsFeedUrl()
    {
        return getFeedLinkHref(Zend_Gdata_YouTube::USER_CONTACTS_REL);
    }

    public function getFavoritesFeedUrl()
    {
        return getFeedLinkHref(Zend_Gdata_YouTube::USER_FAVORITES_REL);
    }

}
