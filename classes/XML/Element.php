<?php
#-------------------------------------------------------------------#
# TubeX - Copyright � 2009 JMB Software, Inc. All Rights Reserved.  #
# This file may not be redistributed in whole or significant part.  #
# TubeX IS NOT FREE SOFTWARE                                        #
#-------------------------------------------------------------------#
# http://www.jmbsoft.com/           http://www.jmbsoft.com/license/ #
#-------------------------------------------------------------------#

class XML_Element extends SimpleXMLElement
{

    public function el($xpath)
    {
        $result = $this->xpath($xpath);

        if( count($result) )
        {
            return $result[0];
        }

        return null;
    }

    public function attrs()
    {
        $attrs = (array)$this->attributes();
        return $attrs['@attributes'];
    }

    public function val()
    {
        $string = (string)$this;

        switch(strtolower($string))
        {
            case 'true':
                return true;

            case 'false':
                return false;

            case 'null':
                return null;

            case 'mysql:now':
                return date('Y-m-d H:i:s');

            default:
                return $string;
        }
    }
}

?>
