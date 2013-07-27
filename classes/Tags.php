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


class Tags
{

    private static $min_length;

    private static $stop_words = array("a's", "able", "about", "above", "according", "accordingly", "across", "actually", "after", "afterwards", "again", "against", "ain't", "all",
                                       "allow", "allows", "almost", "alone", "along", "already", "also", "although", "always", "am", "among", "amongst", "an", "and", "another", "any",
                                       "anybody", "anyhow", "anyone", "anything", "anyway", "anyways", "anywhere", "apart", "appear", "appreciate", "appropriate", "are", "aren't",
                                       "around", "as", "aside", "ask", "asking", "associated", "at", "available", "away", "awfully", "be", "became", "because", "become", "becomes",
                                       "becoming", "been", "before", "beforehand", "behind", "being", "believe", "below", "beside", "besides", "best", "better", "between", "beyond",
                                       "both", "brief", "but", "by", "c'mon", "c's", "came", "can", "can't", "cannot", "cant", "cause", "causes", "certain", "certainly", "changes",
                                       "clearly", "co", "com", "come", "comes", "concerning", "consequently", "consider", "considering", "contain", "containing", "contains", "corresponding",
                                       "could", "couldn't", "course", "currently", "definitely", "described", "despite", "did", "didn't", "different", "do", "does", "doesn't", "doing",
                                       "don't", "done", "down", "downwards", "during", "each", "edu", "eg", "eight", "either", "else", "elsewhere", "enough", "entirely", "especially",
                                       "et", "etc", "even", "ever", "every", "everybody", "everyone", "everything", "everywhere", "ex", "exactly", "example", "except", "far", "few",
                                       "fifth", "first", "five", "followed", "following", "follows", "for", "former", "formerly", "forth", "four", "from", "further", "furthermore",
                                       "get", "gets", "getting", "given", "gives", "go", "goes", "going", "gone", "got", "gotten", "greetings", "had", "hadn't", "happens", "hardly",
                                       "has", "hasn't", "have", "haven't", "having", "he", "he's", "hello", "help", "hence", "her", "here", "here's", "hereafter", "hereby", "herein",
                                       "hereupon", "hers", "herself", "hi", "him", "himself", "his", "hither", "hopefully", "how", "howbeit", "however", "i'd", "i'll", "i'm", "i've",
                                       "ie", "if", "ignored", "immediate", "in", "inasmuch", "inc", "indeed", "indicate", "indicated", "indicates", "inner", "insofar", "instead", "into",
                                       "inward", "is", "isn't", "it", "it'd", "it'll", "it's", "its", "itself", "just", "keep", "keeps", "kept", "know", "knows", "known", "last", "lately",
                                       "later", "latter", "latterly", "least", "less", "lest", "let", "let's", "like", "liked", "likely", "little", "look", "looking", "looks", "ltd", "mainly",
                                       "many", "may", "maybe", "me", "mean", "meanwhile", "merely", "might", "more", "moreover", "most", "mostly", "much", "must", "my", "myself", "name",
                                       "namely", "nd", "near", "nearly", "necessary", "need", "needs", "neither", "never", "nevertheless", "new", "next", "nine", "no", "nobody", "non", "none",
                                       "noone", "nor", "normally", "not", "nothing", "novel", "now", "nowhere", "obviously", "of", "off", "often", "oh", "ok", "okay", "old", "on", "once", "one",
                                       "ones", "only", "onto", "or", "other", "others", "otherwise", "ought", "our", "ours", "ourselves", "out", "outside", "over", "overall", "own", "particular",
                                       "particularly", "per", "perhaps", "placed", "please", "plus", "possible", "presumably", "probably", "provides", "que", "quite", "qv", "rather", "rd", "re",
                                       "really", "reasonably", "regarding", "regardless", "regards", "relatively", "respectively", "right", "said", "same", "saw", "say", "saying", "says", "second",
                                       "secondly", "see", "seeing", "seem", "seemed", "seeming", "seems", "seen", "self", "selves", "sensible", "sent", "serious", "seriously", "seven", "several",
                                       "shall", "she", "should", "shouldn't", "since", "six", "so", "some", "somebody", "somehow", "someone", "something", "sometime", "sometimes", "somewhat",
                                       "somewhere", "soon", "sorry", "specified", "specify", "specifying", "still", "sub", "such", "sup", "sure", "t's", "take", "taken", "tell", "tends", "th",
                                       "than", "thank", "thanks", "thanx", "that", "that's", "thats", "the", "their", "theirs", "them", "themselves", "then", "thence", "there", "there's",
                                       "thereafter", "thereby", "therefore", "therein", "theres", "thereupon", "these", "they", "they'd", "they'll", "they're", "they've", "think", "third",
                                       "this", "thorough", "thoroughly", "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "took", "toward", "towards",
                                       "tried", "tries", "truly", "try", "trying", "twice", "two", "un", "under", "unfortunately", "unless", "unlikely", "until", "unto", "up", "upon", "us",
                                       "use", "used", "useful", "uses", "using", "usually", "value", "various", "very", "via", "viz", "vs", "want", "wants", "was", "wasn't", "way", "we", "we'd",
                                       "we'll", "we're", "we've", "welcome", "well", "went", "were", "weren't", "what", "what's", "whatever", "when", "whence", "whenever", "where", "where's",
                                       "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "who's", "whoever", "whole", "whom",
                                       "whose", "why", "will", "willing", "wish", "with", "within", "without", "won't", "wonder", "would", "would", "wouldn't", "yes", "yet", "you", "you'd",
                                       "you'll", "you're", "you've", "your", "yours", "yourself", "yourselves", "zero");

    private static $special_chars = array('~', '`', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '+', '=', '|', "\\", ']', '}', '[', '{', ':', ';', '"', "'", '<', ',', '>', '.', '?', '/');

    public static function GetMinLength()
    {
        if( !empty(self::$min_length) )
        {
            return self::$min_length;
        }

        $DB = GetDB();
        $row = $DB->Row("SHOW VARIABLES LIKE 'ft_min_word_len'");
        self::$min_length = $row['Value'];
        return self::$min_length;
    }

    public static function Count($string)
    {
        if( $string == String::BLANK )
        {
            return 0;
        }

        return substr_count($string, ' ') + 1;
    }

    public static function Format($string)
    {
        $min_length = self::GetMinLength();
        $string = str_replace(',', ' ', $string);
        $tags = array();

        foreach( explode(' ', $string) as $word )
        {
            $word = strtolower(trim($word));

            // Stop words filter
            if( in_array($word, self::$stop_words) )
            {
                continue;
            }

            // Special characters filter
            $word = str_replace(self::$special_chars, '', $word);

            if( strlen($word) >= $min_length )
            {
                $tags[] = $word;
            }
        }

        return join(' ', array_unique($tags));
    }

    public static function AddToFrequency($tags)
    {
        if( String::IsEmpty($tags) )
        {
            return;
        }

        $DB = GetDB();

        foreach( explode(' ', $tags) as $tag )
        {
            $tag = trim($tag);

            if( $DB->Update('UPDATE `tbx_video_tag` SET `frequency`=`frequency`+1 WHERE `tag`=?', array($tag)) < 1 )
            {
                $DB->Update('INSERT INTO `tbx_video_tag` VALUES (?,?)', array($tag, 1));
            }
        }
    }

    public static function UpdateFrequency($old_tags, $new_tags)
    {
        // No change
        if( $old_tags == $new_tags )
        {
            return;
        }

        $old_tags = explode(' ', $old_tags);
        $new_tags = explode(' ', $new_tags);

        self::RemoveFromFrequency(join(' ', array_diff($old_tags, $new_tags)));
        self::AddToFrequency(join(' ', array_diff($new_tags, $old_tags)));
    }

    public static function RemoveFromFrequency($tags)
    {
        if( String::IsEmpty($tags) )
        {
            return;
        }

        $DB = GetDB();

        foreach( explode(' ', $tags) as $tag )
        {
            $tag = trim($tag);
            $DB->Update('UPDATE `tbx_video_tag` SET `frequency`=`frequency`-1 WHERE `tag`=?', array($tag));
        }

        $DB->Update('DELETE FROM `tbx_video_tag` WHERE `frequency` < 1');
    }
}


?>