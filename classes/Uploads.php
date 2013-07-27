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

class Uploads
{
    private static $uploads = array();

    private function __construct() { }

    public static function Exist()
    {
        return !empty($_FILES);
    }

    public static function Get($field)
    {
        return isset(self::$uploads[$field]) ? self::$uploads[$field] : null;
    }

    public static function GetAll()
    {
        return self::$uploads;
    }

    public static function ProcessNew($allowed_extensions = null)
    {
        $success = true;

        foreach( $_FILES as $field => $data )
        {
            self::$uploads[$field] = array('error' => null);

            if( $data['error'] > 0 )
            {
                self::$uploads[$field]['error'] = self::CodeToMessage($data['error']);

                if( $data['error'] === UPLOAD_ERR_NO_FILE )
                {
                    unset(self::$uploads[$field]);
                }
                else
                {
                    $success = false;
                }
                continue;
            }

            $DB = GetDB();
            $DB->Update('INSERT INTO `tbx_upload` VALUES (?,?,?)',
                        array(null,
                              '',
                              $data['name']));

            $upload_id = $DB->LastInsertId();

            try
            {
                $filename = self::GenerateFilename($upload_id, $data['name'], $allowed_extensions);
                move_uploaded_file($data['tmp_name'], $filename['path']);
                @chmod($filename['path'], 0666);

                $DB->Update('UPDATE `tbx_upload` SET `uri`=? WHERE `upload_id`=?',
                            array($filename['uri'],
                                  $upload_id));

                self::$uploads[$field]['upload_id'] = $upload_id;
                self::$uploads[$field]['uri'] = $filename['uri'];
                self::$uploads[$field]['path'] = $filename['path'];
            }
            catch( Exception $e )
            {
                $DB->Update('DELETE FROM `tbx_upload` WHERE `upload_id`=?', array($upload_id));
                self::$uploads[$field]['error'] = _T('Validation:Unable to process uploaded file', $e->getMessage());
                $success = false;
            }
        }

        return $success;
    }

    public static function RemoveCurrent()
    {
        $DB = GetDB();

        foreach( self::$uploads as $upload )
        {
            if( isset($upload['path']) && file_exists($upload['path']) )
            {
                @unlink($upload['path']);
            }

            $DB->Update('DELETE FROM `tbx_upload` WHERE `upload_id`=?', array($upload['upload_id']));
        }
    }

    public static function RemoveExisting($upload_id)
    {
        $DB = GetDB();
        $upload = $DB->Row('SELECT * FROM `tbx_upload` WHERE `upload_id`=?', array($upload_id));

        if( !empty($upload) )
        {
            $path = Config::Get('document_root') . $upload['uri'];

            if( file_exists($path) )
            {
                @unlink($path);
            }

            $DB->Update('DELETE FROM `tbx_upload` WHERE `upload_id`=?', array($upload_id));
        }
    }

    public static function GenerateFilename($id, $original, $allowed_extensions = null)
    {
        $extension = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $filename = preg_replace('~\.$~', '', sha1($id) . '.' . $extension);
        $directory = UPLOADS_DIR . DIRECTORY_SEPARATOR . $filename[0] . DIRECTORY_SEPARATOR . $filename[1];
        $path = $directory . DIRECTORY_SEPARATOR . $filename;
        $uri = str_replace(Config::Get('document_root'), '', $path);

        // Check if the file extension is allowed
        if( !empty($allowed_extensions) && !in_array($extension, explode(',', strtolower($allowed_extensions))) )
        {
            throw new BaseException(_T('Validation:The file extension of the uploaded file is not allowed'));
        }

        // Create the directory if necessary
        if( !file_exists($directory) )
        {
            Dir::Create($directory);
        }

        return array('path' => $path,
                     'uri' => $uri,
                     'directory' => $directory,
                     'filename' => $filename);
    }

    public static function CodeToMessage($code)
    {
        switch ($code)
        {
            case UPLOAD_ERR_INI_SIZE:
                return _T('Upload:Error ini size');
            case UPLOAD_ERR_FORM_SIZE:
                return _T('Upload:Error form size');
            case UPLOAD_ERR_PARTIAL:
                return _T('Upload:Error partial');
            case UPLOAD_ERR_NO_FILE:
                return _T('Upload:Error no file');
            case UPLOAD_ERR_NO_TMP_DIR:
                return _T('Upload:Error no tmp dir');
            case UPLOAD_ERR_CANT_WRITE:
                return _T('Upload:Error cant write');
            case UPLOAD_ERR_EXTENSION:
                return _T('Upload:Error extension');
            default:
                return _T('Upload:Error unknown');
        }
    }
}


?>