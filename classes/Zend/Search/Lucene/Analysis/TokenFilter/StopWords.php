<?php


require_once 'Zend/Search/Lucene/Analysis/TokenFilter.php';


class Zend_Search_Lucene_Analysis_TokenFilter_StopWords extends Zend_Search_Lucene_Analysis_TokenFilter
{

    private $_stopSet;

    public function __construct($stopwords = array()) {
        $this->_stopSet = array_flip($stopwords);
    }

    public function normalize(Zend_Search_Lucene_Analysis_Token $srcToken) {
        if (array_key_exists($srcToken->getTermText(), $this->_stopSet)) {
            return null;
        } else {
            return $srcToken;
        }
    }

    public function loadFromFile($filepath = null) {
        if (! $filepath || ! file_exists($filepath)) {
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('You have to provide valid file path');
        }
        $fd = fopen($filepath, "r");
        if (! $fd) {
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Cannot open file ' . $filepath);
        }
        while (!feof ($fd)) {
            $buffer = trim(fgets($fd));
            if (strlen($buffer) > 0 && $buffer[0] != '#') {
                $this->_stopSet[$buffer] = 1;
            }
        }
        if (!fclose($fd)) {
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Cannot close file ' . $filepath);
        }
    }
}

