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


class Video_Dir
{

    const CLIPS = 'clips';
    const THUMBS = 'thumbs';
    const TEMP = 'temp';
    const PROCESSING = 'processing';
    const ORIGINALS = 'originals';

    private $base_dir;

    private $originals_dir;

    private $clips_dir;

    private $thumbs_dir;

    private $temp_dir;

    private $processing_dir;

    public function __construct($directory = null, $mode = 0777)
    {
        if( empty($directory) )
        {
            $this->CreateTemporary($mode);
        }
        else
        {
            $this->CreateFromExisting($directory);
        }
    }

    public static function DirNameFromId($id)
    {
        $dirname = strrev(sprintf('%06s', base_convert($id, 10, 36)));
        $base_dir =  VIDEOS_DIR . '/' . $dirname[0] . '/' . $dirname[1];
        Dir::Create($base_dir);
        return $base_dir . '/' . $dirname;
    }

    private function AddFromFile($directory, $filename, $extension = null, $default_extension = 'tmp')
    {
        if( empty($extension) )
        {
            $extension = preg_match('~\.([^.]+)$~', $filename, $matches) ? $matches[1] : $default_extension;
        }

        $new_filename = $directory . '/' . $this->GetNext($directory, $extension) . '.' . $extension;

        if( is_uploaded_file($filename) )
        {
            move_uploaded_file($filename, $new_filename);
        }
        else
        {
            rename($filename, $new_filename);
        }

        @chmod($new_filename, 0666);

        return $new_filename;
    }

    private function AddFromVar($directory, $data, $extension)
    {
        $new_filename = $directory . '/' . $this->GetNext($directory, $extension) . '.' . $extension;

        file_put_contents($new_filename, $data);
        @chmod($new_filename, 0666);

        return $new_filename;
    }

    public function AddProcessingFromFile($filename, $extension = null)
    {
        return $this->AddFromFile($this->processing_dir, $filename, $extension, 'tmp');
    }

    public function AddProcessingFromVar($data, $extension)
    {
        return $this->AddFromVar($this->processing_dir, $data, $extension);
    }

    public function AddTempFromFile($filename, $extension = null)
    {
        return $this->AddFromFile($this->temp_dir, $filename, $extension, 'tmp');
    }

    public function AddTempFromVar($data, $extension)
    {
        return $this->AddFromVar($this->temp_dir, $data, $extension);
    }

    public function AddOriginalFromFile($filename, $extension = null)
    {
        return $this->AddFromFile($this->originals_dir, $filename, $extension, 'flv');
    }

    public function AddOriginalFromVar($data, $extension)
    {
        return $this->AddFromVar($this->originals_dir, $data, $extension);
    }

    public function AddClipFromFile($filename, $extension = null)
    {
        return $this->AddFromFile($this->clips_dir, $filename, $extension, 'flv');
    }

    public function AddClipFromVar($data, $extension)
    {
        return $this->AddFromVar($this->clips_dir, $data, $extension);
    }

    public function AddThumbFromFile($filename)
    {
        return $this->AddFromFile($this->thumbs_dir, $filename, JPG_EXTENSION, JPG_EXTENSION);
    }

    public function AddThumbFromVar($data)
    {
        return $this->AddFromVar($this->thumbs_dir, $data, JPG_EXTENSION);
    }

    public function MoveTo($directory)
    {
        if( file_exists($directory) )
        {
            Dir::Remove($directory, true);
        }
        
        rename($this->base_dir, $directory);
        $this->base_dir = $directory;
        $this->temp_dir = $directory . '/' . self::TEMP;
        $this->clips_dir = $directory . '/' . self::CLIPS;
        $this->thumbs_dir = $directory . '/' . self::THUMBS;
        $this->processing_dir = $directory . '/' . self::PROCESSING;
        $this->originals_dir = $directory . '/' . self::ORIGINALS;
    }

    public function MoveFiles($src, $dest, $extension)
    {
        $src = Dir::StripTrailingSlash($src);
        $dest = Dir::StripTrailingSlash($dest);
        $dest_files = array();
        $files = preg_grep('~\.' . $extension . '$~i', scandir($src));

        foreach( $files as $file )
        {
            rename("$src/$file", "$dest/$file");
            $dest_files[] = "$dest/$file";
        }

        return $dest_files;
    }

    private function GetNext($directory, $extension)
    {
        $current_largest = 0;

        foreach( scandir($directory) as $item )
        {
            if( preg_match('~^(\d+)\.' . $extension . '$~', $item, $matches) )
            {
                $intval = intval($matches[1]);
                if( $intval > $current_largest )
                {
                    $current_largest = $intval;
                }
            }
        }

        return sprintf('%08d', ++$current_largest);
    }

    public function GetThumbURIs()
    {
        $thumbs = array();
        foreach( glob($this->thumbs_dir . '/*.*') as $thumb )
        {
            $thumbs[] = str_replace(Config::Get('document_root'), '', $thumb);
        }

        return $thumbs;
    }

    public function GetClipURIs()
    {
        $clips = array();
        foreach( glob($this->clips_dir . '/*.*') as $clip )
        {
            $clips[] = str_replace(Config::Get('document_root'), '', $clip);
        }

        return $clips;
    }

    public function GetOriginalsDir()
    {
        return $this->originals_dir;
    }

    public function GetProcessingDir()
    {
        return $this->processing_dir;
    }

    public function GetTempDir()
    {
        return $this->temp_dir;
    }

    public function GetThumbsDir()
    {
        return $this->thumbs_dir;
    }

    public function GetClipsDir()
    {
        return $this->clips_dir;
    }

    public function GetBaseDir()
    {
        return $this->base_dir;
    }

    private function ClearDirectory($directory)
    {
        $contents = scandir($directory);

        foreach( $contents as $item )
        {
            if( is_file($directory . '/' . $item) )
            {
                unlink($directory . '/' . $item);
            }
        }
    }

    public function ClearTemp()
    {
        self::ClearDirectory($this->temp_dir);
    }

    public function ClearProcessing()
    {
        self::ClearDirectory($this->processing_dir);
    }

    public function Remove()
    {
        Dir::Remove($this->base_dir);
    }

    private function CreateFromExisting($directory)
    {
        $this->base_dir = $directory;

        $this->clips_dir = $directory . '/' . self::CLIPS;
        Dir::Create($this->clips_dir);

        $this->temp_dir = $directory . '/' . self::TEMP;
        Dir::Create($this->temp_dir);

        $this->processing_dir = $directory . '/' . self::PROCESSING;
        Dir::Create($this->processing_dir);

        $this->originals_dir = $directory . '/' . self::ORIGINALS;
        Dir::Create($this->originals_dir);

        $this->thumbs_dir = $directory . '/' . self::THUMBS;
        Dir::Create($this->thumbs_dir);
    }

    private function CreateTemporary($mode = 0777)
    {
        $directory = Dir::Temporary($mode);
        $this->CreateFromExisting($directory);
    }

    public function __toString()
    {
        $output = "\nVideo Directory\n" .
                  "===============\n" .
                  "Base: " . $this->base_dir . "\n" .
                  "Temp: " . $this->temp_dir . "\n" .
                  "Processing: " . $this->processing_dir . "\n" .
                  "Originals: " . $this->originals_dir . "\n" .
                  "Thumbs: " . $this->thumbs_dir . "\n";

        return $output;
    }
}

?>