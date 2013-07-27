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


class Video_Thumbnail
{

    public static function CanResize()
    {
        $si = ServerInfo::GetCached();
        return (!$si->shell_exec_disabled && $si->binaries[ServerInfo::BIN_CONVERT]) || $si->php_extensions[ServerInfo::EXT_GD];
    }

    public static function ResizeDirectory($src_dir, $dst_dir, $size, $quality)
    {
        $resized = array();
        $src_dir = Dir::StripTrailingSlash($src_dir);
        $src_dir_contents = preg_grep('~^[0-9]{8}.jpg$~i', scandir($src_dir));

        foreach( $src_dir_contents as $image_file )
        {
            $resized[] = self::Resize("$src_dir/$image_file", $size, $quality, $dst_dir);
        }

        return $resized;
    }

    public static function DiscardBlack($directory)
    {
        $si = ServerInfo::GetCached();

        $frames = glob($directory . '/*.' . JPG_EXTENSION);
        $discarded = array();
        $keepers = array();

        foreach( $frames as $frame )
        {
            if( $si->binaries[ServerInfo::BIN_CONVERT] )
            {
                $info = shell_exec($si->binaries[ServerInfo::BIN_CONVERT] . ' ' .
                                   escapeshellarg($frame) . ' ' .
                                   '-threshold 20% -verbose info:');

                if( preg_match('~Mean: ([0-9.e\-]+) \([0-9.e\-]+\)~i', $info, $matches) )
                {
                    $non_black =  round($matches[1] * 100);

                    if( $non_black <= 15 )
                    {
                        $discarded[] = $frame;
                    }
                    else
                    {
                        $keepers[] = $frame;
                    }
                }
                else
                {
                    $keepers[] = $frame;
                }
            }
            else
            {
                $img = imagecreatefromjpeg($frame);
                $total_pixels = imagesy($img) * imagesx($img);
                $black = 0;
                $non_black = 0;

                for( $y = 0; $y < imagesy($img); $y++ )
                {
                    for( $x = 0; $x < imagesx($img); $x++ )
                    {
                        imagecolorat($img, $x, $y) / 0xFFFFFF <= 0.20 ? $black++ : $non_black++;
                    }
                }

                if( round($non_black / $total_pixels * 100) <= 15 )
                {
                    $discarded[] = $frame;
                }
                else
                {
                    $keepers[] = $frame;
                }
            }
        }

        if( count($discarded) > 0 )
        {
            foreach( $discarded as $discard )
            {
                @unlink($discard);
            }

            foreach( $keepers as $i => $keeper )
            {
                $basename = basename($keeper);
                $expected = sprintf('%08d.jpg', $i + 1);

                if( $basename != $expected )
                {
                    rename($keeper, "$directory/$expected");
                    $keepers[$i] = "$directory/$expected";
                }
            }
        }

        return $keepers;
    }

    public static function Resize($image, $size, $quality, $directory, $coords = null)
    {
        $si = ServerInfo::GetCached();
        $directory = Dir::StripTrailingSlash($directory);
        $letterbox = Config::Get('flag_letterbox');
        $resized = $directory . '/' . basename($image);
        list($dst_width, $dst_height) = explode('x', $size);

        // See if image is already the correct size
        $imgsize = @getimagesize($image);
        if( $imgsize === false )
        {
            return null;
        }
        else if( empty($coords) && $imgsize[0] <= $dst_width && $imgsize[1] <= $dst_height )
        {
            copy($image, $resized);
            return $resized;
        }

        $src_width = $imgsize[0];
        $src_height = $imgsize[1];

        $src_aspect = $src_width / $src_height;
        $dst_aspect = $dst_width / $dst_height;

        // Get crop information if $coords was supplied
        // Generally only occurs when image is extracted from a HTML image map
        $crop = $crop_x = $crop_y = $crop_width = $crop_height = null;
        if( !empty($coords) )
        {
            $crop = explode(',', str_replace(' ', '', $coords));

            // Currently only supporting rect
            if( count($crop) == 4 )
            {
                $crop_x = $crop[0];
                $crop_y = $crop[1];
                $crop_width = $crop[2] - $crop_x;
                $crop_height = $crop[3] - $crop_y;
                $src_aspect = $crop_width / $crop_height;
            }
        }


        // Resize with ImageMagick (preferred)
        if( !$si->shell_exec_disabled && $si->binaries[ServerInfo::BIN_CONVERT] )
        {
            if( $letterbox )
            {
                shell_exec($si->binaries[ServerInfo::BIN_CONVERT] . ' ' .
                           escapeshellarg($image) . ' ' .
                           (!empty($crop) ? '-crop ' . escapeshellarg($crop_width . 'x' . $crop_height . '!+' . $crop_x . '+' . $crop_y) . ' ' : '') .
                           '-resize ' . $size . ' ' .
                           '-modulate 110,102,100 ' .
                           '-unsharp 1.5x1.2+1.0+0.10 ' .
                           '-enhance ' .
                           '-size ' . $size . ' ' .
                           'xc:black ' .
                           '+swap ' .
                           '-gravity center ' .
                           '-quality ' . $quality . ' ' .
                           '-format jpeg ' .
                           '-composite ' .
                           escapeshellarg($resized));
            }
            else
            {
                $recrop = null;
                if( $src_aspect > $dst_aspect )
                {
                    $recrop = '-gravity center -crop ' . escapeshellarg(round($src_height * $dst_aspect) . 'x' . $src_height . '+0+0') . ' ';
                }
                else if( $src_aspect < $dst_aspect )
                {
                    $recrop = '-gravity center -crop ' . escapeshellarg($src_width . 'x' . round($src_width / $dst_aspect) . '+0+0') . ' ';
                }

                shell_exec($si->binaries[ServerInfo::BIN_CONVERT] . ' ' .
                           escapeshellarg($image) . ' ' .
                           (!empty($crop) ? '-crop ' . escapeshellarg($crop_width . 'x' . $crop_height . '!+' . $crop_x . '+' . $crop_y) . ' ' : '') .
                           (!empty($recrop) ? $recrop : '') .
                           '-resize ' . $size . ' ' .
                           '-modulate 110,102,100 ' .
                           '-unsharp 1.5x1.2+1.0+0.10 ' .
                           '-enhance ' .
                           '-quality ' . $quality . ' ' .
                           '-format jpeg ' .
                           escapeshellarg($resized));
            }

            return $resized;
        }

        // Resize with GD
        else if( $si->php_extensions[ServerInfo::EXT_GD] )
        {
            if( $crop )
            {
                $temp_src = imagecreatefromjpeg($image);
                $src = imagecreatetruecolor($crop_width, $crop_height);
                imagecopy($src, $temp_src, 0, 0, $crop_x, $crop_y, $crop_width, $crop_height);
                imagedestroy($temp_src);
            }
            else
            {
                $src = imagecreatefromjpeg($image);
            }

            $new_width = $dst_width;
            $new_height = $dst_height;
            $dst_x = 0;
            $dst_y = 0;

            if( $src_aspect > $dst_aspect )
            {
                $new_height = round($dst_width * $src_height / $src_width);
                $dst_y = ($dst_height - $new_height) / 2;
            }
            else if( $src_aspect < $dst_aspect )
            {
                $new_width = round($dst_height * $src_width / $src_height);
                $dst_x = ($dst_width - $new_width) / 2;
            }

            $dst = imagecreatetruecolor($dst_width, $dst_height);
            $black = imagecolorallocate($dst, 0, 0, 0);
            imagefill($dst, 0, 0, 0);
            imagecopyresampled($dst, $src, $dst_x, $dst_y, 0, 0, $new_width, $new_height, $src_width, $src_height);
            imagejpeg($dst, $resized, $quality);
            imagedestroy($src);
            imagedestroy($dst);

            return $resized;
        }

        return $image;
    }
}

?>