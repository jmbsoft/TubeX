<?php


require_once 'Zend/Search/Lucene/Analysis/Analyzer/Common.php';


class Zend_Search_Lucene_Analysis_Analyzer_Common_Text extends Zend_Search_Lucene_Analysis_Analyzer_Common
{

    private $_position;

    public function reset()
    {
        $this->_position = 0;

        if ($this->_input === null) {
            return;
        }

        // convert input into ascii
        if (PHP_OS != 'AIX') {
            $this->_input = iconv($this->_encoding, 'ASCII//TRANSLIT', $this->_input);
        }
        $this->_encoding = 'ASCII';
    }

    public function nextToken()
    {
        if ($this->_input === null) {
            return null;
        }


        do {
            if (! preg_match('/[a-zA-Z]+/', $this->_input, $match, PREG_OFFSET_CAPTURE, $this->_position)) {
                // It covers both cases a) there are no matches (preg_match(...) === 0)
                // b) error occured (preg_match(...) === FALSE)
                return null;
            }

            $str = $match[0][0];
            $pos = $match[0][1];
            $endpos = $pos + strlen($str);

            $this->_position = $endpos;

            $token = $this->normalize(new Zend_Search_Lucene_Analysis_Token($str, $pos, $endpos));
        } while ($token === null); // try again if token is skipped

        return $token;
    }
}

