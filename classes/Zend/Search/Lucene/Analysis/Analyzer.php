<?php


require_once 'Zend/Search/Lucene/Analysis/Token.php';

require_once 'Zend/Search/Lucene/Analysis/Analyzer/Common/Utf8.php';

require_once 'Zend/Search/Lucene/Analysis/Analyzer/Common/Utf8/CaseInsensitive.php';

require_once 'Zend/Search/Lucene/Analysis/Analyzer/Common/Utf8Num.php';

require_once 'Zend/Search/Lucene/Analysis/Analyzer/Common/Utf8Num/CaseInsensitive.php';

require_once 'Zend/Search/Lucene/Analysis/Analyzer/Common/Text.php';

require_once 'Zend/Search/Lucene/Analysis/Analyzer/Common/Text/CaseInsensitive.php';

require_once 'Zend/Search/Lucene/Analysis/Analyzer/Common/TextNum.php';

require_once 'Zend/Search/Lucene/Analysis/Analyzer/Common/TextNum/CaseInsensitive.php';

require_once 'Zend/Search/Lucene/Analysis/TokenFilter/StopWords.php';

require_once 'Zend/Search/Lucene/Analysis/TokenFilter/ShortWords.php';


abstract class Zend_Search_Lucene_Analysis_Analyzer
{

    private static $_defaultImpl;

    protected $_input = null;

    protected $_encoding = '';

    public function tokenize($data, $encoding = '')
    {
        $this->setInput($data, $encoding);

        $tokenList = array();
        while (($nextToken = $this->nextToken()) !== null) {
            $tokenList[] = $nextToken;
        }

        return $tokenList;
    }

    public function setInput($data, $encoding = '')
    {
        $this->_input    = $data;
        $this->_encoding = $encoding;
        $this->reset();
    }

    abstract public function reset();

    abstract public function nextToken();

    public static function setDefault(Zend_Search_Lucene_Analysis_Analyzer $analyzer)
    {
        self::$_defaultImpl = $analyzer;
    }

    public static function getDefault()
    {
        if (!self::$_defaultImpl instanceof Zend_Search_Lucene_Analysis_Analyzer) {
            self::$_defaultImpl = new Zend_Search_Lucene_Analysis_Analyzer_Common_Text_CaseInsensitive();
        }

        return self::$_defaultImpl;
    }
}

