<?php
#-------------------------------------------------------------------#
# TubeX - Copyright � 2009 JMB Software, Inc. All Rights Reserved.  #
# This file may not be redistributed in whole or significant part.  #
# TubeX IS NOT FREE SOFTWARE                                        #
#-------------------------------------------------------------------#
# http://www.jmbsoft.com/           http://www.jmbsoft.com/license/ #
#-------------------------------------------------------------------#

class CompilerException extends Exception
{
    public function __construct($message)
    {
        $this->message = $message . ' [line #' . Template_Compiler::GetCurrentLine() . ']';
    }
}

class Template_Compiler
{

    const PHP_START = '<?php ';

    const PHP_START_ECHO = '<?php echo ';

    const PHP_END = ' ?>';

    const DELIMITER_START = '{';

    const DELIMITER_END = '}';

    const COMMENT_TAG = '*';

    const VARIABLE_TAG = '$';

    const STRING_TAG = '"';

    const TRANSLATION_TAG = '_';

    private static $current_line;

    private static $tag_stack;

    private static $literal_stack;

    private static $capture_stack;

    private static $errors;

    private static $defines;

    private static $has_nocache;

    private static $literal_close_tags = array('/php', '/phpcode', '/literal');

    private function __construct()
    {
    }

    public static function Compile($template)
    {
        // Initialization
        self::$current_line = 1;
        self::$tag_stack = array();
        self::$literal_stack = array();
        self::$capture_stack = array();
        self::$errors = array();
        self::$defines = array();
        self::$has_nocache = false;

        // Convert to Unix newline characters
        $template = String::FormatNewlines($template);

        // Mark with line numbers
        $line_number = 0;
        $marked_template = '';
        foreach( explode(String::NEWLINE_UNIX, $template) as $line )
        {
            $line_number++;
            $marked_template .= preg_replace('~{([/$a-z"]+)~i', "(*$line_number*){\\1", $line) . String::NEWLINE_UNIX;
        }
        unset($template);

        // Strip comment sections
        $marked_template = preg_replace('~{\*.*?\*}~msi', '', $marked_template);

        // Create regular expression
        $regex = '~\(\*(\d+)\*\)'.self::DELIMITER_START.'([/*$a-z"]+.*?)'.self::DELIMITER_END.'~msi';

        // Compile the code
        $compiled = preg_replace_callback($regex, array('self', 'CompileTag'), $marked_template);

        // Second pass to handle {nocache} sections
        $compiled = preg_replace_callback('~\{nocache\}(.*?)\{/nocache\}~msi', array('self', 'NocachePass'), $compiled);

        // Check for unclosed tag(s)
        foreach( self::$tag_stack as $unclosed_tag )
        {
            self::$errors[] = 'Unclosed {' . $unclosed_tag . '} tag at end of template';
        }

        // Check for unclosed literal sections
        foreach( self::$literal_stack as $unclosed_tag )
        {
            self::$errors[] = 'Unclosed {' . $unclosed_tag . '} tag at end of template';
        }

        // Code cleanup
        $compiled = preg_replace("~\n+~", "\n", trim($compiled));

        if( self::$has_nocache )
        {
            $compiled = self::PHP_START . '$this->nocache = true;' . self::PHP_END . $compiled;
        }

        return count(self::$errors) ? false : $compiled;
    }

    public static function CompileFile($filename, $directory = TEMPLATES_DIR)
    {
        return self::Compile(file_get_contents($directory . '/' . $filename));
    }

    private static function NocachePass($matches)
    {
        return self::PHP_START_ECHO . "base64_decode('" . base64_encode($matches[1]) . "');" . self::PHP_END;
    }

    private static function CompileTag($matches)
    {
        try
        {
            $original = preg_replace('~^\(\*\d+\*\)~', '', $matches[0]);
            self::$current_line = $matches[1];
            $tag = $matches[2];

            // Remove template comments
            if( $tag[0] === self::COMMENT_TAG )
            {
                return String::BLANK;
            }

            $tag = self::ParseTag($tag);

            // Inside a literal section, don't parse code
            if( !in_array($tag['tag'], self::$literal_close_tags) && count(self::$literal_stack) > 0 )
            {
                return $original;
            }

            // Tag name is a variable
            if( $tag['tag'][0] == self::VARIABLE_TAG )
            {
                return self::PHP_START_ECHO . self::ParseVars($tag['tag'], $tag['modifiers'], true) . ';' . self::PHP_END;
            }

            switch( $tag['tag'] )
            {
                case 'string':
                    return $tag['term'];

                case 'translate':
                    if( empty($tag['args']) )
                    {
                        return _T($tag['term']);
                    }
                    else
                    {
                        return self::PHP_START_ECHO . "_T('" . $tag['term'] . "'" . $tag['args'] . ");" . self::PHP_END;
                    }

                case 'define':
                    self::CompileDefineTag($tag['attributes']);
                    return;

                case 'if':
                    self::PushTag(self::$tag_stack, 'if');
                    return self::PHP_START . 'if( ' . self::ParseVars($tag['attributes']) . ' ):' . self::PHP_END;


                case 'elsif':
                case 'elseif':
                    self::CheckForUnexpected(self::$tag_stack, 'if', 'elseif');
                    return self::PHP_START . 'elseif( ' . self::ParseVars($tag['attributes']) . ' ):' . self::PHP_END;


                case 'else':
                    self::CheckForUnexpected(self::$tag_stack, 'if', 'else');
                    return self::PHP_START . 'else:' . self::PHP_END;


                case '/if':
                    self::PopTag(self::$tag_stack, 'if');
                    return self::PHP_START . 'endif;' . self::PHP_END;


                case 'capture':
                    self::VerifyHasAttributes($tag['attributes'], $tag['tag']);
                    self::VerifyRequiredAttributes(array('var'), $tag['attributes'], $tag['tag']);
                    self::PushTag(self::$tag_stack, 'capture');
                    self::$capture_stack[] = $tag['attributes']['var'];
                    return self::PHP_START . 'ob_start();' . self::PHP_END;


                case '/capture':
                    self::PopTag(self::$tag_stack, 'capture');
                    return self::PHP_START . self::ParseVars(array_pop(self::$capture_stack)) . ' = ob_get_clean();' . self::PHP_END;


                case 'nocache':
                    self::$has_nocache = true;
                    return '{nocache}';


                case '/nocache':
                    return '{/nocache}';


                case 'literal':
                    self::PushTag(self::$literal_stack, 'literal');
                    return String::BLANK;


                case '/literal':
                    self::PopTag(self::$literal_stack, 'literal');
                    return String::BLANK;


                case 'insert':
                    self::PushTag(self::$tag_stack, 'insert');
                    return self::CompileInsertTag($tag['attributes']);


                case '/insert':
                    self::PopTag(self::$tag_stack, 'insert');
                    return self::PHP_START . 'endif;' . self::PHP_END;


                case 'range':
                    self::PushTag(self::$tag_stack, 'range');
                    return self::CompileRangeStart($tag['attributes']);


                case '/range':
                    self::PopTag(self::$tag_stack, 'range');
                    return self::PHP_START . 'endforeach;' . self::PHP_END;


                case 'foreach':
                    self::PushTag(self::$tag_stack, 'foreach');
                    return self::CompileForeachStart($tag['attributes']);


                case 'foreachdone':
                    return self::PHP_START . 'break;' . self::PHP_END;


                case '/foreach':
                    self::PopTag(self::$tag_stack, 'foreach');
                    return self::PHP_START . String::NEWLINE_UNIX .
                           '    endforeach;' . String::NEWLINE_UNIX .
                           'endif;' . String::NEWLINE_UNIX .
                           self::PHP_END;


                case 'php':
                    self::PushTag(self::$literal_stack, 'php');
                    return self::PHP_START;


                case '/php':
                    self::PopTag(self::$literal_stack, 'php');
                    return self::PHP_END;


                case 'phpcode':
                    self::PushTag(self::$literal_stack, 'phpcode');
                    return self::PHP_START . "echo '" . self::PHP_START . " ';" . self::PHP_END;


                case '/phpcode':
                    self::PopTag(self::$literal_stack, 'phpcode');
                    return self::PHP_START. "echo ' " . self::PHP_END . "';" . self::PHP_END;


                case 'setlocale':
                    self::VerifyHasAttributes($tag['attributes'], $tag['tag']);
                    self::VerifyRequiredAttributes(array('value'), $tag['attributes'], $tag['tag']);
                    return self::PHP_START . 'setlocale(LC_TIME, \'' . $tag['attributes']['value'] . '\');' . self::PHP_END;


                case 'datelocale':
                    self::VerifyRequiredAttributes(array('value', 'format'), $tag['attributes'], $tag['tag']);

                    switch( strtolower($tag['attributes']['value']) )
                    {
                        case 0:
                        case '0':
                        case '-0':
                        case '-0 day':
                        case '-0 days':
                        case '+0':
                        case '+0 day':
                        case '+0 days':
                        case 'today':
                        case 'now':
                            $tag['attributes']['value'] = null;
                            break;

                        default:
                            $tag['attributes']['value'] = ' ' . $tag['attributes']['value'];
                            break;
                    }

                    return self::PHP_START_ECHO .
                           "ucwords(strftime('" . $tag['attributes']['format'] . "', strtotime(date('Y-m-d H:i:s')" .
                           (empty($tag['attributes']['value']) ? '' : " . '" . $tag['attributes']['value'] . "'") . ")));" .
                           self::PHP_END;


                case 'date':
                    self::VerifyRequiredAttributes(array('value', 'format'), $tag['attributes'], $tag['tag']);

                    switch( strtolower($tag['attributes']['value']) )
                    {
                        case 0:
                        case '0':
                        case '-0':
                        case '-0 day':
                        case '-0 days':
                        case '+0':
                        case '+0 day':
                        case '+0 days':
                        case 'today':
                        case 'now':
                            $tag['attributes']['value'] = null;
                            break;

                        default:
                            $tag['attributes']['value'] = ' ' . $tag['attributes']['value'];
                            break;
                    }

                    return self::PHP_START_ECHO .
                           "date('" . $tag['attributes']['format'] . "', strtotime(date('Y-m-d H:i:s')" .
                           (empty($tag['attributes']['value']) ? '' : " . '" . $tag['attributes']['value'] . "'") . "));" .
                           self::PHP_END;


                case 'assign':
                    return self::CompileAssignTag($tag['attributes']);


                case 'include':
                    self::VerifyRequiredAttributes(array('file'), $tag['attributes'], $tag['tag']);
                    return self::PHP_START . "readfile('" . $tag['attributes']['file'] . "');" . self::PHP_END;

                case 'options':
                    return self::CompileOptionsTag($tag['attributes']);

                case 'reasons':
                    return self::CompileReasonsTag($tag['attributes']);

                case 'template':
                    return self::CompileTemplateTag($tag['attributes']);

                case 'tags':
                    return self::CompileTagsTag($tag['attributes']);

                case 'categories':
                    return self::CompileCategoriesTag($tag['attributes']);

                case 'category':
                    return self::CompileCategoryTag($tag['attributes']);

                case 'searchterms':
                    return self::CompileSearchTermsTag($tag['attributes']);

                case 'videos':
                    return self::CompileVideosTag($tag['attributes']);

                case 'video':
                    return self::CompileVideoTag($tag['attributes']);

                case 'clips':
                    return self::CompileClipsTag($tag['attributes']);

                case 'player':
                    return self::CompilePlayerTag($tag['attributes']);

                case 'sponsors':
                    return self::CompileSponsorsTag($tag['attributes']);

                case 'sponsor':
                    return self::CompileSponsorTag($tag['attributes']);

                case 'banner':
                    return self::CompileBannerTag($tag['attributes']);

                case 'stats':
                    return self::CompileStatsTag($tag['attributes']);

                case 'ratings':
                    return self::CompileRatingsTag($tag['attributes']);

                case 'comments':
                    return self::CompileCommentsTag($tag['attributes']);

                case 'user':
                    return self::CompileUserTag($tag['attributes']);
            }

            return $original;
        }
        catch(CompilerException $e)
        {
            self::$errors[] = $e->getMessage();
        }
    }

    private static function CompileReasonsTag($attributes)
    {
        self::VerifyHasAttributes($attributes, 'reasons');
        self::VerifyRequiredAttributes(array('var', 'type'), $attributes, 'reasons');
        self::VerifyVariableAttributes(array('var'), $attributes, 'reasons');

        $attributes['var'] = self::ParseVars($attributes['var']);

        return self::PHP_START .
               "\$DB = GetDB();" . String::NEWLINE_UNIX .
               $attributes['var'] . " = \$DB->FetchAll('SELECT * FROM `tbx_reason` WHERE `type`=? ORDER BY `short_name`', array('" . $attributes['type'] . "'));" . String::NEWLINE_UNIX .
               self::PHP_END;
    }

    private static function CompileOptionsTag($attributes)
    {
        self::VerifyHasAttributes($attributes, 'options');
        self::VerifyRequiredAttributes(array('from', 'key', 'value'), $attributes, 'options');
        self::VerifyVariableAttributes(array('from'), $attributes, 'options');

        $attributes['from'] = self::ParseVars($attributes['from']);
        $attributes['selected'] = isset($attributes['selected']) ? self::ParseVars($attributes['selected']) : 'null';

        return self::PHP_START .
               "foreach( " . $attributes['from'] . " as \$x_options )" . String::NEWLINE_UNIX .
               "{" . String::NEWLINE_UNIX .
               'echo "<option value=\"" . htmlspecialchars($x_options[\'' . $attributes['key'] . '\']) . "\"" . ' . String::NEWLINE_UNIX .
               '($x_options[\'' . $attributes['key'] . '\'] == ' . $attributes['selected'] . ' ? " selected=\"selected\"" : "") . ' . String::NEWLINE_UNIX .
               '">" . htmlspecialchars($x_options[\'' . $attributes['value'] . '\']) . "</option>";' . String::NEWLINE_UNIX .
               "}" . String::NEWLINE_UNIX .
               self::PHP_END;
    }

    private static function CompileUserTag($attributes)
    {
        self::VerifyHasAttributes($attributes, 'user');
        self::VerifyRequiredAttributes(array('var', 'username'), $attributes, 'user');
        self::VerifyVariableAttributes(array('var'), $attributes, 'user');

        $attributes['var'] = self::ParseVars($attributes['var']);
        $attributes['username'] = self::ParseVars($attributes['username']);

        $query = "SELECT * FROM `tbx_user` JOIN `tbx_user_custom` USING (`username`) JOIN `tbx_user_stat` USING (`username`) LEFT JOIN `tbx_upload` ON `upload_id`=`avatar_id` WHERE `tbx_user`.`username`=?";

        return self::PHP_START . String::NEWLINE_UNIX .
               '$DB = GetDB();' . String::NEWLINE_UNIX .
               $attributes['var'] . " = \$DB->Row('$query', array(" . $attributes['username'] . "));" . String::NEWLINE_UNIX .
               self::PHP_END;
    }

    private static function CompilePlayerTag($attributes)
    {
        self::VerifyHasAttributes($attributes, 'player');

        // Set defaults
        $attributes = array_merge(array('flv' => 'video-player-flv.tpl',
                                        'wmv' => 'video-player-wmv.tpl',
                                        'qt' => 'video-player-quicktime.tpl'),
                                  $attributes);

        self::VerifyRequiredAttributes(array('clips'), $attributes, 'player');
        self::VerifyVariableAttributes(array('clips'), $attributes, 'player');

        $attributes['clips'] = self::ParseVars($attributes['clips']);

        return self::PHP_START . String::NEWLINE_UNIX .
               "\$x_clip =& " . $attributes['clips'] . "[0];" . String::NEWLINE_UNIX .
               "\$this->vars['g_clip'] =& \$x_clip;" . String::NEWLINE_UNIX .
               "if( \$x_clip['type'] == 'Embed' )" . String::NEWLINE_UNIX .
               "{" . String::NEWLINE_UNIX .
               "echo \$x_clip['clip'];" . String::NEWLINE_UNIX .
               "}" . String::NEWLINE_UNIX .
               "else if( preg_match('~\\.' . FLASH_EXTENSIONS . '\$~i', \$x_clip['clip']) )" . String::NEWLINE_UNIX .
               "{" . String::NEWLINE_UNIX .
               "include(TEMPLATE_COMPILE_DIR . '/" . $attributes['flv'] . "');" . String::NEWLINE_UNIX .
               "}" . String::NEWLINE_UNIX .
               "else if( preg_match('~\\.' . QT_EXTENSIONS . '\$~i', \$x_clip['clip']) )" . String::NEWLINE_UNIX .
               "{" . String::NEWLINE_UNIX .
               "include(TEMPLATE_COMPILE_DIR . '/" . $attributes['qt'] . "');" . String::NEWLINE_UNIX .
               "}" . String::NEWLINE_UNIX .
               "else if( preg_match('~\\.' . WM_EXTENSIONS . '\$~i', \$x_clip['clip']) )" . String::NEWLINE_UNIX .
               "{" . String::NEWLINE_UNIX .
               "include(TEMPLATE_COMPILE_DIR . '/" . $attributes['wmv'] . "');" . String::NEWLINE_UNIX .
               "}" . String::NEWLINE_UNIX .
               "else" . String::NEWLINE_UNIX .
               "{" . String::NEWLINE_UNIX .
               "include(TEMPLATE_COMPILE_DIR . '/" . $attributes['other'] . "');" . String::NEWLINE_UNIX .
               "}" . String::NEWLINE_UNIX .
               self::PHP_END;
    }

    private static function CompileCommentsTag($attributes)
    {
        self::VerifyHasAttributes($attributes, 'comments');

        // Set defaults
        $attributes = array_merge(array('amount' => 25,
                                        'paginate' => true,
                                        'page' => '$g_page_number',
                                        'sort' => '`date_commented` DESC'),
                                  $attributes);

        self::VerifyRequiredAttributes(array('var', 'videoid'), $attributes, 'comments');
        self::VerifyVariableAttributes(array('var'), $attributes, 'comments');

        $attributes['var'] = self::ParseVars($attributes['var']);
        $attributes['paginate'] = self::ToBoolean($attributes['paginate']);

        // Build the query
        $binds = array(self::ParseVarsInString($attributes['videoid']), 'Active');
        $query = 'SELECT * FROM `tbx_video_comment` WHERE `video_id`=? AND `status`=? ORDER BY ' . $attributes['sort'];


        // Handle paginate option
        if( $attributes['paginate'] === true )
        {
            if( isset($attributes['pagination']) )
            {
                $attributes['pagination'] = self::ParseVars($attributes['pagination']);
            }

            $attributes['page'] = self::ParseVars($attributes['page']);

            return self::PHP_START . String::NEWLINE_UNIX .
                   '$DB = GetDB();' . String::NEWLINE_UNIX .
                   "\$x_result = \$DB->QueryWithPagination('$query', array(" . join(',', $binds) . "), " . $attributes['page'] . ", " . $attributes['amount'] . ");" . String::NEWLINE_UNIX .
                   (isset($attributes['pagination']) ? $attributes['pagination'] . ' = $x_result;' . String::NEWLINE_UNIX : '') .
                   $attributes['var'] . " = \$DB->FetchAll(\$x_result['handle']);" . String::NEWLINE_UNIX .
                   self::PHP_END;
        }
        else
        {
            $query .= ' LIMIT ' . $attributes['amount'];

            return self::PHP_START . String::NEWLINE_UNIX .
                   '$DB = GetDB();' . String::NEWLINE_UNIX .
                   $attributes['var'] . " = \$DB->FetchAll('$query', array(" . join(',', $binds) . "));" . String::NEWLINE_UNIX .
                   self::PHP_END;
        }
    }

    private static function CompileClipsTag($attributes)
    {
        self::VerifyHasAttributes($attributes, 'clips');

        // Set defaults
        $attributes = array_merge(array('sort' => '`clip_id`'),
                                  $attributes);

        self::VerifyRequiredAttributes(array('var', 'videoid'), $attributes, 'clips');
        self::VerifyVariableAttributes(array('var'), $attributes, 'clips');

        $attributes['var'] = self::ParseVars($attributes['var']);
        $attributes['videoid'] = self::ParseVars($attributes['videoid']);

        $query = "SELECT * FROM `tbx_video_clip` WHERE `video_id`=? ORDER BY " . $attributes['sort'];

        return self::PHP_START . String::NEWLINE_UNIX .
               '$DB = GetDB();' . String::NEWLINE_UNIX .
               $attributes['var'] . " = \$DB->FetchAll('$query', array(" . $attributes['videoid'] . "));" . String::NEWLINE_UNIX .
               "\$x_thumbs = \$DB->FetchAll('SELECT * FROM `tbx_video_thumbnail` WHERE `video_id`=? ORDER BY `thumbnail_id`', array(" . $attributes['videoid'] . "));" . String::NEWLINE_UNIX .
               "\$x_offset = round(count(\$x_thumbs)/count(" . $attributes['var'] . "));" . String::NEWLINE_UNIX .
               "foreach( " . $attributes['var'] . " as \$x_i => \$x_clip )" . String::NEWLINE_UNIX .
               "{" . String::NEWLINE_UNIX .
               $attributes['var'] . "[\$x_i]['thumbnail'] = \$x_thumbs[\$x_i * \$x_offset]['thumbnail'];" . String::NEWLINE_UNIX .
               "}" . String::NEWLINE_UNIX .
               self::PHP_END;
    }

    private static function CompileRatingsTag($attributes)
    {
        self::VerifyHasAttributes($attributes, 'ratings');

        // Set defaults
        $attributes = array_merge(array('amount' => 4,
                                        'sort' => '`date_rated` DESC'),
                                  $attributes);

        self::VerifyRequiredAttributes(array('var', 'videoid'), $attributes, 'ratings');
        self::VerifyVariableAttributes(array('var'), $attributes, 'ratings');

        $attributes['var'] = self::ParseVars($attributes['var']);
        $attributes['videoid'] = self::ParseVars($attributes['videoid']);

        $query = "SELECT * FROM `tbx_video_rating` WHERE `video_id`=? " .
                 "ORDER BY " . $attributes['sort'] . " LIMIT " . $attributes['amount'];

        return self::PHP_START . String::NEWLINE_UNIX .
               '$DB = GetDB();' . String::NEWLINE_UNIX .
               $attributes['var'] . " = \$DB->FetchAll('$query', array(" . $attributes['videoid'] . "));" . String::NEWLINE_UNIX .
               self::PHP_END;
    }

    private static function CompileStatsTag($attributes)
    {
        self::VerifyHasAttributes($attributes, 'stats');
        self::VerifyRequiredAttributes(array('var'), $attributes, 'stats');
        self::VerifyVariableAttributes(array('var'), $attributes, 'stats');

        $attributes['var'] = self::ParseVars($attributes['var']);

        return self::PHP_START . String::NEWLINE_UNIX .
               '$DB = GetDB();' . String::NEWLINE_UNIX .
               $attributes['var'] . "['users'] = \$DB->QueryCount('SELECT COUNT(*) FROM `tbx_user` WHERE `status`=?', array('Active'));"  . String::NEWLINE_UNIX .
               $attributes['var'] . "['videos'] = \$DB->QueryCount('SELECT COUNT(*) FROM `tbx_video` WHERE `status`=? AND `is_private`=0', array('Active'));"  . String::NEWLINE_UNIX .
               $attributes['var'] . "['categories'] = \$DB->QueryCount('SELECT COUNT(*) FROM `tbx_category`');"  . String::NEWLINE_UNIX .
               $attributes['var'] . "['comments'] = \$DB->QueryCount('SELECT COUNT(*) FROM `tbx_video_comment` WHERE `status`=?', array('Active'));"  . String::NEWLINE_UNIX .
               self::PHP_END;
    }

    private static function CompileBannerTag($attributes)
    {
        // Set defaults
        $attributes = array_merge(array('zone' => null,
                                        'tags' => null,
                                        'sponsor' => null,
                                        'sort' => '`times_displayed`, RAND()'),
                                  $attributes);

        $DB = GetDB();
        $wheres = array();
        $binds = array();


        // Handle zone option
        if( !empty($attributes['zone']) )
        {
            if( $DB->QueryCount('SELECT COUNT(*) FROM `tbx_banner` WHERE `zone`=?', array($attributes['zone'])) < 1 )
            {
                throw new CompilerException("The zone '" . $attributes['zone'] . "' does not exist");
            }

            $wheres[] = '`zone`=?';
            $binds[] = self::Quote($attributes['zone']);
        }


        // Handle sponsor option
        if( !empty($attributes['sponsor']) )
        {
            if( $attributes['sponsor'][0] == self::VARIABLE_TAG )
            {
                $attributes['sponsor'] = self::ParseVars($attributes['sponsor']);
            }
            else if( preg_match('~^\d+$~', $attributes['sponsor']) )
            {
                $sponsor_id = $attributes['sponsor'];
                $attributes['sponsor'] = $DB->QuerySingleColumn('SELECT `sponsor_id` FROM `tbx_sponsor` WHERE `sponsor_id`=?', array($attributes['sponsor']));

                if( empty($attributes['sponsor']) )
                {
                    throw new CompilerException("The sponsor with ID '" . $sponsor_id . "' does not exist");
                }
            }
            else
            {
                $sponsor_name = $attributes['sponsor'];
                $attributes['sponsor'] = $DB->QuerySingleColumn('SELECT `sponsor_id` FROM `tbx_sponsor` WHERE `name`=?', array($attributes['sponsor']));

                if( empty($attributes['sponsor']) )
                {
                    throw new CompilerException("The sponsor with name '" . $sponsor_name . "' does not exist");
                }
            }

            $wheres[] = '`sponsor_id`=?';
            $binds[] = $attributes['sponsor'];
        }


        // Handle tags option
        if( !empty($attributes['tags']) )
        {
            $wheres[] = 'MATCH(`tags`) AGAINST (? IN BOOLEAN MODE)';
            $binds[] = self::ParseVarsInString($attributes['tags']);
        }


        $query = 'SELECT * FROM `tbx_banner` ' .
                 (count($wheres) > 0 ? 'WHERE ' . join(' AND ', $wheres) . ' ' : '') .
                 'ORDER BY ' . $attributes['sort'] . ' LIMIT 1';

        return self::PHP_START . String::NEWLINE_UNIX .
               '$DB = GetDB();' . String::NEWLINE_UNIX .
               "\$x_banner = \$DB->Row('$query', array(" . join(',', $binds) . "));" . String::NEWLINE_UNIX .
               "\$DB->Update('UPDATE `tbx_banner` SET `times_displayed`=`times_displayed`+1 WHERE `banner_id`=?', array(\$x_banner['banner_id']));" . String::NEWLINE_UNIX .
               "echo \$x_banner['banner_html'];" . String::NEWLINE_UNIX .
               self::PHP_END;
    }

    private static function CompileSponsorTag($attributes)
    {
        self::VerifyHasAttributes($attributes, 'sponsor');
        self::VerifyRequiredAttributes(array('var', 'sponsorid'), $attributes, 'sponsor');
        self::VerifyVariableAttributes(array('var'), $attributes, 'sponsor');

        $attributes['var'] = self::ParseVars($attributes['var']);
        $attributes['sponsorid'] = self::ParseVars($attributes['sponsorid']);

        $query = "SELECT * FROM `tbx_sponsor` JOIN `tbx_sponsor_custom` USING (`sponsor_id`) WHERE `tbx_sponsor`.`sponsor_id`=?";

        return self::PHP_START . String::NEWLINE_UNIX .
               '$DB = GetDB();' . String::NEWLINE_UNIX .
               $attributes['var'] . " = \$DB->Row('$query', array(" . $attributes['sponsorid'] . "));" . String::NEWLINE_UNIX .
               self::PHP_END;
    }

    private static function CompileSponsorsTag($attributes)
    {
        self::VerifyHasAttributes($attributes, 'categories');

        // Set defaults
        $attributes = array_merge(array('amount' => null,
                                        'startswith' => null,
                                        'alphabetize' => false,
                                        'sort' => '`name`'),
                                  $attributes);

        self::VerifyRequiredAttributes(array('var'), $attributes, 'sponsors');
        self::VerifyVariableAttributes(array('var'), $attributes, 'sponsors');

        $attributes['var'] = self::ParseVars($attributes['var']);
        $attributes['alphabetize'] = self::ToBoolean($attributes['alphabetize']);

        $wheres = array();
        $binds = array();

        // Handle startswith option
        if( !empty($attributes['startswith']) )
        {
            $wheres[] = '`name` LIKE ?';
            $binds[] = self::ParseVarsInString($attributes['startswith']) . " . '%'";
        }

        $query = "SELECT * FROM `tbx_sponsor` JOIN `tbx_sponsor_custom` USING (`sponsor_id`) " .
                 (!empty($wheres) ? 'WHERE ' . join(' AND ', $wheres) . ' ' : '') .
                 "ORDER BY " . $attributes['sort'] . (!empty($attributes['amount']) ? " LIMIT " . $attributes['amount'] : '');

        return self::PHP_START . String::NEWLINE_UNIX .
               '$DB = GetDB();' . String::NEWLINE_UNIX .
               $attributes['var'] . " = \$DB->FetchAll('$query', array(" . join(',', $binds) . "));"  . String::NEWLINE_UNIX .
               ($attributes['alphabetize'] ? "usort(" . $attributes['var'] . ", create_function('\$a, \$b', 'return strcmp(\$a[\\'name\\'], \$b[\\'name\\']);'));". String::NEWLINE_UNIX : '') .
               self::PHP_END;
    }

    private static function CompileVideoTag($attributes)
    {
        self::VerifyHasAttributes($attributes, 'video');
        self::VerifyRequiredAttributes(array('var', 'videoid'), $attributes, 'video');
        self::VerifyVariableAttributes(array('var'), $attributes, 'video');

        $attributes['var'] = self::ParseVars($attributes['var']);
        $attributes['videoid'] = self::ParseVars($attributes['videoid']);

        $query = "SELECT *,`tbx_video`.`video_id` AS `video_id` FROM `tbx_video` JOIN `tbx_video_custom` USING (`video_id`) JOIN `tbx_video_stat` USING (`video_id`) " .
                 "LEFT JOIN `tbx_video_thumbnail` ON `display_thumbnail`=`thumbnail_id` WHERE `status`=? AND `tbx_video`.`video_id`=?";
        $npquery = "SELECT *,`tbx_video`.`video_id` AS `video_id` FROM `tbx_video` JOIN `tbx_video_custom` USING (`video_id`) JOIN `tbx_video_stat` USING (`video_id`) " .
                   "LEFT JOIN `tbx_video_thumbnail` ON `display_thumbnail`=`thumbnail_id` WHERE `is_private`=? AND `status`=? AND `tbx_video`.`video_id`=?";
        $pquery = "SELECT *,`tbx_video`.`video_id` AS `video_id` FROM `tbx_video` JOIN `tbx_video_custom` USING (`video_id`) JOIN `tbx_video_stat` USING (`video_id`) JOIN `tbx_video_private` USING (`video_id`) " .
                  "LEFT JOIN `tbx_video_thumbnail` ON `display_thumbnail`=`thumbnail_id` WHERE `is_private`=? AND `status`=? AND `private_id`=? AND `tbx_video`.`video_id`=?";

        return self::PHP_START . String::NEWLINE_UNIX .
               '$DB = GetDB();' . String::NEWLINE_UNIX .
               "if( !isset(\$this->vars['g_private']) )" . String::NEWLINE_UNIX .
               '{' . String::NEWLINE_UNIX .
               $attributes['var'] . " = \$DB->Row('$query', array('Active', " . $attributes['videoid'] . "));" . String::NEWLINE_UNIX .
               '}' . String::NEWLINE_UNIX .
               "else if( !\$this->vars['g_private'] )" . String::NEWLINE_UNIX .
               '{' . String::NEWLINE_UNIX .
               $attributes['var'] . " = \$DB->Row('$npquery', array(0, 'Active', " . $attributes['videoid'] . "));" . String::NEWLINE_UNIX .
               '}' . String::NEWLINE_UNIX .
               'else' . String::NEWLINE_UNIX .
               '{' . String::NEWLINE_UNIX .
               $attributes['var'] . " = \$DB->Row('$pquery', array(1, 'Active', \$this->vars['g_private_id'], " . $attributes['videoid'] . "));" . String::NEWLINE_UNIX .
               '}' . String::NEWLINE_UNIX .
               self::PHP_END;
    }

    private static function CompileVideosTag($attributes)
    {
        self::VerifyHasAttributes($attributes, 'videos');

        // Set defaults
        $attributes = array_merge(array('amount' => 25,
                                        'featured' => null,
                                        'category' => null,
                                        'sponsor' => null,
                                        'tags' => null,
                                        'username' => null,
                                        'related' => null,
                                        'favorites' => null,
                                        'not' => null,
                                        'private' => false,
                                        'paginate' => false,
                                        'page' => '$g_page_number',
                                        'searchterm' => null,
                                        'emptysearch' => false,
                                        'sort' => '`date_added` DESC'),
                                  $attributes);

        self::VerifyRequiredAttributes(array('var'), $attributes, 'videos');
        self::VerifyVariableAttributes(array('var', 'pagination', 'related'), $attributes, 'videos');

        $attributes['var'] = self::ParseVars($attributes['var']);
        $attributes['related'] = self::ParseVars($attributes['related']);
        $attributes['featured'] = self::ToBoolean($attributes['featured']);
        $attributes['paginate'] = self::ToBoolean($attributes['paginate']);

        $DB = GetDB();
        $wheres = array("`status`=?");
        $binds = array("'Active'");


        // Handle private option
        $attributes['private'] = self::ToBoolean($attributes['private']);

        if( $attributes['private'] === true )
        {
            $wheres[] = "`is_private`=?";
            $binds[] = 1;
        }
        else if( $attributes['private'] === false )
        {
            $wheres[] = "`is_private`=?";
            $binds[] = 0;
        }



        // Handle username option
        if( !empty($attributes['username']) )
        {
            if( $attributes['username'][0] == self::VARIABLE_TAG )
            {
                $attributes['username'] = self::ParseVars($attributes['username']);
            }
            else
            {
                if( $DB->QueryCount('SELECT COUNT(*) FROM `tbx_user` WHERE `username`=?', array($attributes['username'])) < 1 )
                {
                    throw new CompilerException("The account with username '" . $attributes['username'] . "' does not exist");
                }

                $attributes['username'] = self::Quote($attributes['username']);
            }

            $wheres[] = !empty($attributes['favorites']) ? '`tbx_user_favorite`.`username`=?' : '`tbx_video`.`username`=?';
            $binds[] = $attributes['username'];
        }


        // Handle featured option
        if( $attributes['featured'] === true )
        {
            $wheres[] = "`is_featured`=1";
        }
        else if( $attributes['featured'] === false )
        {
            $wheres[] = "`is_featured`=0";
        }


        // Handle category option
        if( !empty($attributes['category']) )
        {
            if( $attributes['category'][0] == self::VARIABLE_TAG )
            {
                $attributes['category'] = self::ParseVars($attributes['category']);
                $wheres[] = 'IF(?, 1, `category_id`=?)';
                $binds[] = 'empty(' . $attributes['category'] . ')';
                $binds[] = $attributes['category'];
            }
            else if( preg_match('~^\d+$~', $attributes['category']) )
            {
                $category_id = $attributes['category'];
                $attributes['category'] = $DB->QuerySingleColumn('SELECT `category_id` FROM `tbx_category` WHERE `category_id`=?', array($attributes['category']));

                if( empty($attributes['category']) )
                {
                    throw new CompilerException("The category with ID '" . $category_id . "' does not exist");
                }

                $wheres[] = '`category_id`=?';
                $binds[] = $attributes['category'];
            }
            else
            {
                $category_name = $attributes['category'];
                $attributes['category'] = $DB->QuerySingleColumn('SELECT `category_id` FROM `tbx_category` WHERE `name`=?', array($attributes['category']));

                if( empty($attributes['category']) )
                {
                    throw new CompilerException("The category with name '" . $category_name . "' does not exist");
                }

                $wheres[] = '`category_id`=?';
                $binds[] = $attributes['category'];
            }
        }


        // Handle sponsor option
        if( !empty($attributes['sponsor']) )
        {
            if( $attributes['sponsor'][0] == self::VARIABLE_TAG )
            {
                $attributes['sponsor'] = self::ParseVars($attributes['sponsor']);
            }
            else if( preg_match('~^\d+$~', $attributes['sponsor']) )
            {
                $sponsor_id = $attributes['sponsor'];
                $attributes['sponsor'] = $DB->QuerySingleColumn('SELECT `sponsor_id` FROM `tbx_sponsor` WHERE `sponsor_id`=?', array($attributes['sponsor']));

                if( empty($attributes['sponsor']) )
                {
                    throw new CompilerException("The sponsor with ID '" . $sponsor_id . "' does not exist");
                }
            }
            else
            {
                $sponsor_name = $attributes['sponsor'];
                $attributes['sponsor'] = $DB->QuerySingleColumn('SELECT `sponsor_id` FROM `tbx_sponsor` WHERE `name`=?', array($attributes['sponsor']));

                if( empty($attributes['sponsor']) )
                {
                    throw new CompilerException("The sponsor with name '" . $sponsor_name . "' does not exist");
                }
            }

            $wheres[] = '`sponsor_id`=?';
            $binds[] = $attributes['sponsor'];
        }

        // Handle not option
        if( !empty($attributes['not']) )
        {
            $wheres[] = '`tbx_video`.`video_id`!=?';
            $binds[] = self::ParseVarsInString($attributes['not']);
        }

        // Handle tags option
        if( !empty($attributes['tags']) )
        {
            $wheres[] = 'MATCH(`tags`) AGAINST (? IN BOOLEAN MODE)';
            $binds[] = self::ParseVarsInString($attributes['tags']);
        }


        // Handle related option
        if( !empty($attributes['related']) )
        {
            // Sort by relavence
            $attributes['sort'] = null;

            $wheres[] = '`tbx_video`.`video_id`!=?';
            $binds[] = $attributes['related'] . "['video_id']";

            $wheres[] = 'MATCH(`title`,`description`) AGAINST (?)';
            $binds[] = "(empty(" . $attributes['related'] . "['tags']) ? " . $attributes['related'] . "['title'] : " . $attributes['related'] . "['tags'])";
        }


        // Handle searchterm option
        if( !empty($attributes['searchterm']) )
        {
            $attributes['emptysearch'] = self::ToBoolean($attributes['emptysearch']);
            $wheres[] = $attributes['emptysearch'] ?
                        'IF(?, 1, MATCH(`title`,`description`) AGAINST (? \' . ($this->vars[\'g_searchmode\'] ? \'IN BOOLEAN MODE\' : \'\'). \'))' :
                        'MATCH(`title`,`description`) AGAINST (? \' . ($this->vars[\'g_searchmode\'] ? \'IN BOOLEAN MODE\' : \'\'). \')';

            if( $attributes['emptysearch'] )
            {
                $binds[] = 'empty(' . self::ParseVarsInString($attributes['searchterm']) . ')';
            }

            $binds[] = self::ParseVarsInString($attributes['searchterm']);
        }


        // Handle custom fields
        foreach( preg_grep('~^_~', array_keys($attributes)) as $field )
        {
            $field = substr($field, 1);

            if( $DB->QueryCount('SELECT COUNT(*) FROM `tbx_video_custom_schema` WHERE `name`=?', array($field)) < 1 )
            {
                throw new CompilerException("Custom field with name '" . $field . "' does not exist");
            }

            $wheres[] = "`$field`=" . self::ParseVarsInString($attributes["_$field"]);
        }


        // Build the query
        $query = '\'SELECT *,`tbx_video`.`video_id` AS `video_id` FROM `tbx_video` JOIN `tbx_video_custom` USING (`video_id`) JOIN `tbx_video_stat` USING (`video_id`) ';
        if( !empty($attributes['favorites']) && !empty($attributes['username']) )
        {
            $query .= 'JOIN `tbx_user_favorite` USING (`video_id`) ';
        }

        if( $attributes['private'] !== false )
        {
            $query .= 'LEFT JOIN `tbx_video_private` USING (`video_id`) ';
        }
        $query .= 'LEFT JOIN `tbx_video_thumbnail` ON `display_thumbnail`=`thumbnail_id` WHERE ' . join(' AND ', $wheres) .
                  (!empty($attributes['sort']) ? ' ORDER BY \' . ' . self::ParseVarsInString($attributes['sort']) : "'");


        // Handle paginate option
        if( $attributes['paginate'] === true )
        {
            if( isset($attributes['pagination']) )
            {
                $attributes['pagination'] = self::ParseVars($attributes['pagination']);
            }

            $attributes['page'] = self::ParseVars($attributes['page']);

            return self::PHP_START . String::NEWLINE_UNIX .
                   '$DB = GetDB();' . String::NEWLINE_UNIX .
                   "\$x_result = \$DB->QueryWithPagination($query, array(" . join(',', $binds) . "), " . $attributes['page'] . ", " . $attributes['amount'] . ");" . String::NEWLINE_UNIX .
                   (isset($attributes['pagination']) ? $attributes['pagination'] . ' = $x_result;' . String::NEWLINE_UNIX : '') .
                   $attributes['var'] . " = \$DB->FetchAll(\$x_result['handle']);" . String::NEWLINE_UNIX .
                   self::PHP_END;
        }
        else
        {
            $query .= ' . \' LIMIT ' . $attributes['amount'] . '\'';

            return self::PHP_START . String::NEWLINE_UNIX .
                   '$DB = GetDB();' . String::NEWLINE_UNIX .
                   $attributes['var'] . " = \$DB->FetchAll($query, array(" . join(',', $binds) . "));" . String::NEWLINE_UNIX .
                   self::PHP_END;
        }
    }

    private static function CompileCategoryTag($attributes)
    {
        self::VerifyHasAttributes($attributes, 'category');

        // Set defaults
        $attributes = array_merge(array('id' => null,
                                        'url' => null),
                                  $attributes);

        self::VerifyRequiredAttributes(array('var'), $attributes, 'category');
        self::VerifyVariableAttributes(array('var'), $attributes, 'category');

        $attributes['var'] = self::ParseVars($attributes['var']);
        $attributes['id'] = self::ParseVars($attributes['id']);
        $attributes['url'] = self::ParseVars($attributes['url']);

        $wheres = array();
        $binds = array();

        if( !empty($attributes['id']) )
        {
            $wheres[] = "`tbx_category`.`category_id`=?";
            $binds[] = $attributes['id'];
        }
        else if( !empty($attributes['url']) )
        {
            $wheres[] = "`url_name`=?";
            $binds[] = $attributes['url'];
        }

        $query = "SELECT * FROM `tbx_category` JOIN `tbx_category_custom` USING (`category_id`) LEFT JOIN `tbx_upload` ON `upload_id`=`image_id` WHERE " . join(' AND ', $wheres);

        return self::PHP_START . String::NEWLINE_UNIX .
               '$DB = GetDB();' . String::NEWLINE_UNIX .
               $attributes['var'] . " = \$DB->Row('$query', array(" . join(',', $binds) . "));" . String::NEWLINE_UNIX .
               self::PHP_END;
    }

    private static function CompileCategoriesTag($attributes)
    {
        self::VerifyHasAttributes($attributes, 'categories');

        // Set defaults
        $attributes = array_merge(array('amount' => null,
                                        'startswith' => null,
                                        'alphabetize' => false,
                                        'sort' => '`name`'),
                                  $attributes);

        self::VerifyRequiredAttributes(array('var'), $attributes, 'categories');
        self::VerifyVariableAttributes(array('var'), $attributes, 'categories');

        $attributes['var'] = self::ParseVars($attributes['var']);
        $attributes['alphabetize'] = self::ToBoolean($attributes['alphabetize']);

        $wheres = array();
        $binds = array();

        // Handle startswith option
        if( !empty($attributes['startswith']) )
        {
            $wheres[] = '`name` LIKE ?';
            $binds[] = self::ParseVarsInString($attributes['startswith']) . " . '%'";
        }

        $query = "SELECT * FROM `tbx_category` JOIN `tbx_category_custom` USING (`category_id`) LEFT JOIN `tbx_upload` ON `upload_id`=`image_id` " .
                 (!empty($wheres) ? 'WHERE ' . join(' AND ', $wheres) . ' ' : '') .
                 "ORDER BY " . $attributes['sort'] . (!empty($attributes['amount']) ? " LIMIT " . $attributes['amount'] : '');

        return self::PHP_START . String::NEWLINE_UNIX .
               '$DB = GetDB();' . String::NEWLINE_UNIX .
               $attributes['var'] . " = \$DB->FetchAll('$query', array(" . join(',', $binds) . "));"  . String::NEWLINE_UNIX .
               ($attributes['alphabetize'] ? "usort(" . $attributes['var'] . ", create_function('\$a, \$b', 'return strcmp(\$a[\\'name\\'], \$b[\\'name\\']);'));". String::NEWLINE_UNIX : '') .
               self::PHP_END;
    }

    private static function CompileSearchTermsTag($attributes)
    {
        self::VerifyHasAttributes($attributes, 'searchterms');

        // Set defaults
        $attributes = array_merge(array('minscore' => 100,
                                        'maxscore' => 200,
                                        'alphabetize' => false,
                                        'amount' => null,
                                        'sort' => '`frequency` DESC'),
                                  $attributes);

        self::VerifyRequiredAttributes(array('var'), $attributes, 'searchterms');
        self::VerifyVariableAttributes(array('var'), $attributes, 'searchterms');

        $attributes['alphabetize'] = self::ToBoolean($attributes['alphabetize']);
        $attributes['var'] = self::ParseVars($attributes['var']);

        $queries = '';
        if( !empty($attributes['amount']) )
        {
            $queries = "\$x_count = max(1, \$DB->QueryCount('SELECT COUNT(*) FROM `tbx_search_term`'));" . String::NEWLINE_UNIX .
                       "\$DB->QuerySingleColumn('SELECT @MAX:=`frequency` FROM `tbx_search_term` ORDER BY `frequency` DESC LIMIT 1');" . String::NEWLINE_UNIX .
                       "\$DB->QuerySingleColumn('SELECT @MIN:=`frequency` FROM `tbx_search_term` ORDER BY `frequency` DESC LIMIT ' . (min(\$x_count, " . $attributes['amount'] . ") - 1) . ',1');" . String::NEWLINE_UNIX .
                       $attributes['var'] . " = \$DB->FetchAll('SELECT *,IF(@MAX=@MIN, 100, " .
                          "ROUND(((`frequency`-@MIN) * (" . $attributes['maxscore'] . "-" . $attributes['minscore'] . ")/(@MAX-@MIN) + " . $attributes['minscore'] . ")/10) * 10) AS `score` " .
                          "FROM `tbx_search_term` ORDER BY " . $attributes['sort'] . " LIMIT " . $attributes['amount'] ."');";
        }
        else
        {
            $queries = $attributes['var'] . " = \$DB->FetchAll('SELECT * FROM `tbx_search_term` ORDER BY " . $attributes['sort'] . "');";
        }

        return self::PHP_START . String::NEWLINE_UNIX .
               '$DB = GetDB();' . String::NEWLINE_UNIX .
               'Blacklist::FilterSearchTerms();' . String::NEWLINE_UNIX .
               $queries . String::NEWLINE_UNIX .
               ($attributes['alphabetize'] ? "usort(" . $attributes['var'] . ", create_function('\$a, \$b', 'return strcmp(\$a[\\'term\\'], \$b[\\'term\\']);'));". String::NEWLINE_UNIX : '') .
               self::PHP_END;
    }

    private static function CompileTagsTag($attributes)
    {
        self::VerifyHasAttributes($attributes, 'tags');

        // Set defaults
        $attributes = array_merge(array('minscore' => 100,
                                        'maxscore' => 200,
                                        'alphabetize' => false,
                                        'amount' => null,
                                        'sort' => '`frequency` DESC'),
                                  $attributes);

        self::VerifyRequiredAttributes(array('var'), $attributes, 'tags');
        self::VerifyVariableAttributes(array('var'), $attributes, 'tags');

        $attributes['alphabetize'] = self::ToBoolean($attributes['alphabetize']);
        $attributes['var'] = self::ParseVars($attributes['var']);

        $queries = '';
        if( !empty($attributes['amount']) )
        {
            $queries = "\$x_count = max(1, \$DB->QueryCount('SELECT COUNT(*) FROM `tbx_video_tag`'));" . String::NEWLINE_UNIX .
                       "\$DB->QuerySingleColumn('SELECT @MAX:=`frequency` FROM `tbx_video_tag` ORDER BY `frequency` DESC LIMIT 1');" . String::NEWLINE_UNIX .
                       "\$DB->QuerySingleColumn('SELECT @MIN:=`frequency` FROM `tbx_video_tag` ORDER BY `frequency` DESC LIMIT ' . (min(\$x_count, " . $attributes['amount'] . ") - 1) . ',1');" . String::NEWLINE_UNIX .
                       $attributes['var'] . " = \$DB->FetchAll('SELECT *,IF(@MAX=@MIN, 100, " .
                          "ROUND(((`frequency`-@MIN) * (" . $attributes['maxscore'] . "-" . $attributes['minscore'] . ")/(@MAX-@MIN) + " . $attributes['minscore'] . ")/10) * 10) AS `score` " .
                          "FROM `tbx_video_tag` ORDER BY " . $attributes['sort'] . " LIMIT " . $attributes['amount'] ."');";
        }
        else
        {
            $queries = $attributes['var'] . " = \$DB->FetchAll('SELECT * FROM `tbx_video_tag` ORDER BY " . $attributes['sort'] . "');";
        }

        return self::PHP_START . String::NEWLINE_UNIX .
               '$DB = GetDB();' . String::NEWLINE_UNIX .
               $queries . String::NEWLINE_UNIX .
               ($attributes['alphabetize'] ? "usort(" . $attributes['var'] . ", create_function('\$a, \$b', 'return strcmp(\$a[\\'tag\\'], \$b[\\'tag\\']);'));". String::NEWLINE_UNIX : '') .
               self::PHP_END;
    }

    private static function CompileAssignTag($attributes)
    {
        self::VerifyHasAttributes($attributes, 'assign');
        self::VerifyRequiredAttributes(array('var'), $attributes, 'assign');
        self::VerifyVariableAttributes(array('var'), $attributes, 'assign');

        $attributes['var'] = self::ParseVars($attributes['var']);

        if( isset($attributes['value']) )
        {
            $translate = false;
            if( $attributes['value'][0] == self::TRANSLATION_TAG )
            {
                $translate = true;
                $attributes['value'] = substr($attributes['value'], 1);
            }

            $attributes['value'] = ($translate ? '_T(' : '') .
                                   self::ParseVarsInString(substr($attributes['value'], 1)) .
                                   ($translate ? ')' : '');
        }
        else
        {
            $attributes['value'] = self::ParseVars($attributes['code']);
        }

        return self::PHP_START . $attributes['var'] . ' = ' . $attributes['value'] .  ';' . self::PHP_END;
    }

    private static function CompileTemplateTag($attributes)
    {
        self::VerifyHasAttributes($attributes, 'template');
        self::VerifyRequiredAttributes(array('file'), $attributes, 'template');

        if( !is_file(TEMPLATES_DIR . '/' . $attributes['file']) )
        {
            throw new CompilerException("The template file '" . $attributes['file'] . "' does not exist");
        }

        $variables = '';
        foreach( $attributes as $name => $value )
        {
            if( $name != 'file' )
            {
                $translate = false;
                if( $value[0] == self::TRANSLATION_TAG )
                {
                    $translate = true;
                    $value = substr($value, 1);
                }

                $variables .= "\$this->vars['$name'] = " .
                              ($translate ? '_T(' : '') .
                              self::ParseVarsInString($value) .
                              ($translate ? ')' : '') .
                              ";" . String::NEWLINE_UNIX;
            }
        }

        return self::PHP_START . String::NEWLINE_UNIX .

               $variables .
               "include(TEMPLATE_COMPILE_DIR . '/" . $attributes['file'] . "');" . String::NEWLINE_UNIX .
               self::PHP_END;
    }

    private static function CompileDefineTag($attributes)
    {
        self::VerifyHasAttributes($attributes, 'define');
        self::VerifyRequiredAttributes(array('name', 'value'), $attributes, 'define');

        self::$defines[$attributes['name']] = self::ToBoolean($attributes['value']);
    }

    private static function CompileInsertTag($attributes)
    {
        self::VerifyHasAttributes($attributes, 'insert');
        self::VerifyRequiredAttributes(array('location', 'counter'), $attributes, 'insert');
        self::VerifyVariableAttributes(array('counter'), $attributes, 'insert');

        $attributes['location'] = self::ParseVars($attributes['location']);
        $attributes['counter'] = self::ParseVars($attributes['counter']);

        // Format: +5
        if( preg_match('~\+(\d+)~', $attributes['location'], $matches) )
        {
            return self::PHP_START . " if( " . $attributes['counter'] . " % " . $matches[1] . " == 0 " .
                   (isset($attributes['max']) && is_numeric($attributes['max']) ? "&& " . $attributes['counter'] . " <= " . $attributes['max'] . " " : '') .
                   "): " . self::PHP_END;
        }

        // Format: $var
        if( preg_match('~^\$~', $attributes['location']) )
        {
            return self::PHP_START . " if( " . $attributes['counter'] . " % " . $attributes['location'] . " == 0 " .
                   (isset($attributes['max']) && is_numeric($attributes['max']) ? "&& " . $attributes['counter'] . " <= " . $attributes['max'] . " " : '') .
                   "): " . self::PHP_END;
        }

        // Format: 5
        else if( is_numeric($attributes['location']) )
        {
            return self::PHP_START . " if( " . $attributes['counter'] . " == " . $attributes['location'] . " ): " . self::PHP_END;
        }

        // Format: 5,10,15
        else if( preg_match_all('~(\d+)\s*,?~', $attributes['location'], $matches) )
        {
            return self::PHP_START .
                   " if( strstr('," . join(',', $matches[1]) . ",', ','." . $attributes['counter'] . ".',') ): " . self::PHP_END;
        }
    }

    private static function CompileRangeStart($attributes)
    {
        self::VerifyHasAttributes($attributes, 'range');
        self::VerifyRequiredAttributes(array('start', 'end', 'counter'), $attributes, 'range');
        self::VerifyVariableAttributes(array('counter'), $attributes, 'range');

        $attributes['start'] = self::ParseVars($attributes['start']);
        $attributes['end'] = self::ParseVars($attributes['end']);
        $attributes['counter'] = self::ParseVars($attributes['counter']);

        return self::PHP_START .
               "foreach( range(" . $attributes['start'] . "," . $attributes['end'] . ") as " . $attributes['counter'] . " ):" .
               self::PHP_END;
    }

    private static function CompileForeachStart($attributes)
    {
        self::VerifyHasAttributes($attributes, 'foreach');

        // Set defaults
        $attributes = array_merge(array('counter' => null), $attributes);

        self::VerifyRequiredAttributes(array('from', 'var'), $attributes, 'foreach');
        self::VerifyVariableAttributes(array('from', 'var', 'counter'), $attributes, 'foreach');

        if( $attributes['from'] == $attributes['var'] )
        {
            throw new CompilerException("{foreach} tag 'var' and 'from' attributes cannot be set to the same value");
        }

        $attributes['from'] = self::ParseVars($attributes['from']);
        $attributes['var'] = self::ParseVars($attributes['var']);
        $attributes['counter'] = empty($attributes['counter']) ? null : self::ParseVars($attributes['counter']);

        return self::PHP_START . String::NEWLINE_UNIX .
               'if( is_array(' . $attributes['from'] . ') ):' . String::NEWLINE_UNIX .
               ($attributes['counter'] ? '    ' . $attributes['counter'] . ' = 0;' . String::NEWLINE_UNIX : '') .
               '    foreach( ' . $attributes['from'] . ' as ' . $attributes['var'] . ' ):' . String::NEWLINE_UNIX .
               ($attributes['counter'] ? '    ' . $attributes['counter'] . '++;' . String::NEWLINE_UNIX : '') .
               self::PHP_END;
    }

    private static function CheckForUnexpected($stack, $expected_tag, $current_tag)
    {
        if( end($stack) != $expected_tag )
        {
            throw new CompilerException('Unexpected {' . $current_tag . '}');
        }
    }

    private static function PushTag(&$stack, $tag)
    {
        $stack[] = $tag;
    }

    private static function PopTag(&$stack, $expected_tag)
    {
        $popped_tag = array_pop($stack);

        if( $popped_tag === null )
        {
            throw new CompilerException("Mismatched {/$expected_tag} tag");
        }
        else if( $popped_tag != $expected_tag )
        {
            throw new CompilerException("Unexpected {/$expected_tag} tag");
        }
    }

    private static function ParseTag($tag)
    {
        $parsed_tag = null;

        switch($tag[0])
        {
            case self::VARIABLE_TAG:
                $parsed_tag = array();
                $parsed_tag['tag'] = $tag;
                $parsed_tag['modifiers'] = null;

                // Check for tag modifiers
                if( preg_match('~([^|]+)\|(.*)$~s', $parsed_tag['tag'], $matches) )
                {
                    $parsed_tag['tag'] = $matches[1];
                    $parsed_tag['modifiers'] = isset($matches[2]) ? $matches[2] : null;
                }
                break;

            case self::STRING_TAG:
                $parsed_tag = array();

                if( $tag[1] == self::TRANSLATION_TAG )
                {
                    $parts = explode(',', $tag);
                    $parsed_tag['tag'] = 'translate';
                    $parsed_tag['term'] = self::DeQuote('"' . substr(array_shift($parts), 2));
                    $parsed_tag['args'] = join(',', array_map(array('self', 'ParseVarsInString'), $parts));

                    if( !empty($parsed_tag['args']) )
                    {
                        $parsed_tag['args'] = ',' . $parsed_tag['args'];
                    }
                }
                else
                {
                    $parsed_tag['tag'] = 'string';
                    $parsed_tag['term'] = self::DeQuote($tag);
                }
                break;

            default:
                // Separate the tag name from it's attributes
                if( preg_match('~([^\s]+)(\s+(.*))?$~s', $tag, $matches) )
                {
                    $parsed_tag = array();
                    $parsed_tag['tag'] = $matches[1];
                    $parsed_tag['attributes'] = isset($matches[3]) ? $matches[3] : array();

                    if( !empty($parsed_tag['attributes']) )
                    {
                        if( preg_match_all('~([a-z_ ]+=[^=].*?)(?=(?:\s+[a-z_]+\s*=)|$)~i', $parsed_tag['attributes'], $matches) )
                        {
                            $parsed_tag['attributes'] = array();

                            foreach( $matches[1] as $match )
                            {
                                $equals_pos = strpos($match, '=');
                                $attr_name = self::DeQuote(trim(substr($match, 0, $equals_pos)));
                                $attr_value = self::DeQuote(trim(substr($match, $equals_pos + 1)));

                                $parsed_tag['attributes'][strtolower($attr_name)] = $attr_value;
                            }
                        }
                    }
                }
        }

        return $parsed_tag;
    }

    private static function ParseVars($variable, $modifiers = null, $is_variable = false)
    {
        $parsed_var = preg_replace(array('~\$([a-z0-9_]+)~',
                                         '~(\$this->vars\[\'[a-z0-9_]+\'\])\.([a-z0-9_]+)~i',
                                         ),
                                   array("\$this->vars['\\1']",
                                         "\\1['\\2']"), $variable);


        if( $is_variable && (!isset(self::$defines['htmlspecialchars']) || self::$defines['htmlspecialchars'] === true) && !stristr($modifiers, 'rawhtml') && !stristr($modifiers, 'htmlspecialchars') )
        {
            $modifiers = (empty($modifiers) ? '' : "$modifiers|") . 'htmlspecialchars';
        }

        $modifiers = preg_replace('~\|?rawhtml~i', '', $modifiers);

        // Process modifiers
        if( !empty($modifiers) )
        {
            foreach( explode('|', $modifiers) as $modifier )
            {
                if( preg_match('~^([a-z0-9_\->\$]+)(\((.*?)\))?$~i', $modifier, $matches) )
                {
                    $function = $matches[1];
                    $arguments = isset($matches[3]) ? ',' . $matches[3] : '';

                    if( !empty($arguments) )
                    {
                        $arguments = self::ParseVars($arguments);
                    }

                    $parsed_var = "$function($parsed_var$arguments)";
                }
            }
        }

        return $parsed_var;
    }

    private static function ParseVarsInString($string)
    {
        return str_replace(array("'' . ", " . ''"), '', "'" . preg_replace('~(\$this->vars(\[.*?\])+)~', '\' . $1 . \'', self::ParseVars($string)) . "'");
    }

    private static function DeQuote($string)
    {
        if( ($string[0] == "'" || $string[0] == '"') && substr($string, -1) == $string[0] )
        {
            return substr($string, 1, -1);
        }
        else
        {
            return $string;
        }
    }

    private static function Quote($string, $quote = "'")
    {
        return $quote . str_replace($quote, "\\" . $quote, $string) . $quote;
    }

    public static function GetCurrentLine()
    {
        return self::$current_line;
    }

    public static function GetErrors()
    {
        return self::$errors;
    }

    private static function VerifyRequiredAttributes($required, &$attributes, $tag)
    {
        foreach( $required as $r )
        {
            if( !isset($attributes[$r]) || String::IsEmpty($attributes[$r]) )
            {
                throw new CompilerException("{".$tag."} tag is missing the '$r' attribute");
            }
        }
    }

    private static function VerifyVariableAttributes($variables, &$attributes, $tag)
    {
        foreach( $variables as $v )
        {
            if( isset($attributes[$v]) && !preg_match('~^\$[\w]+$~', $attributes[$v]) )
            {
                throw new CompilerException("{".$tag."} tag attribute '$v' must be set to a variable name");
            }
        }
    }

    private static function VerifyHasAttributes(&$attributes, $tag)
    {
        if( !is_array($attributes) || count($attributes) < 1 )
        {
            throw new CompilerException("{" . $tag . "} tag is missing it's attributes");
        }
    }

    private static function ToBoolean($input)
    {
        switch(strtolower($input))
        {
            case 'true':
                return true;

            case 'false':
                return false;
        }

        return $input;
    }
}

?>