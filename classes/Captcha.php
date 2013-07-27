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


class Captcha
{
    // Misc settings
    const COOKIE = 'tubexcaptcha';
    const TABLE = 'tbx_captcha';
    const EXPIRES = 300;
    const FONT = 'captcha.ttf';
    const FONT_SIZE = 30;
    const PADDING_TOP = 15;
    const PADDING_LEFT = 10;
    const CHAR_OFFSET = -4;

    private static $allowed_chars = array('A', 'B', 'C', 'D', 'E', 'F', 'H', 'J', 'K', 'M', 'N', 'P', 'Q', 'R', 'T', 'U', 'V', 'W', 'X', 'Y', '3', '4', '6', '7', '8', '9');


    private static $foreground_color = array(0x00, 0x00, 0x55);
    private static $background_color = array(0xFF, 0xFF, 0xFF);

    private function __construct() { }

    public static function Create()
    {
        // Initial setup
        $string = self::GenerateCode();
        $font_file = INCLUDES_DIR . '/' . self::FONT;
        $box = imagettfbboxextended(self::FONT_SIZE, 0, $font_file, $string);
        $width = $box['width'] + self::PADDING_LEFT * 2;
        $height = $box['height'] + self::PADDING_TOP * 2;


        // Generate colors
        //self::$foreground_color = array(rand(0,100),rand(0,100),rand(0,100));
        //self::$background_color = array(rand(200,255),rand(200,255),rand(200,255));


        // Setup the image
        $image = imagecreatetruecolor($width, $height);
        $foreground = imagecolorallocate($image, self::$foreground_color[0], self::$foreground_color[1], self::$foreground_color[2]);
        $background = imagecolorallocate($image, self::$background_color[0], self::$background_color[1], self::$background_color[2]);
        imagealphablending($image, true);
        imagefill($image, 0, 0, $background);


        // Draw characters
        $offset = 0;
        for( $i = 0; $i < strlen($string); $i++ )
        {
            $bb = imagettfbboxextended(self::FONT_SIZE, 0, $font_file, $string[$i]);
            imagettftext($image, self::FONT_SIZE, 0, $box['x'] + self::PADDING_LEFT + $offset, $box['y'] + self::PADDING_TOP + rand(-5,5), $foreground, $font_file, $string[$i]);
            $offset += $bb['width'] + self::CHAR_OFFSET;
        }


        // Warp the text
        $image = self::Warp($image, $width, $height);


        // Set CAPTCHA cookie
        $session = sha1(uniqid(rand(), true));
        setcookie(self::COOKIE, $session, time() + self::EXPIRES, Config::Get('cookie_path'), Config::Get('cookie_domain'));


        // Insert code into database
        $DB = GetDB();
        $DB->Update('DELETE FROM # WHERE `timestamp` < ?', array(self::TABLE, time() - self::EXPIRES));
        $DB->Update("INSERT INTO # VALUES (?,?,?)", array(self::TABLE, $session, $string, time()));


        // Output the image
        if( function_exists('imagepng') )
        {
            header('Content-type: image/png');
            imagepng($image);
        }
        else
        {
            header('Content-type: image/jpeg');
            imagejpeg($image, null, 95);
        }
    }

    public static function Verify()
    {
        // Retrieve
        $DB = GetDB();
        $captcha = $DB->Row('SELECT * FROM # WHERE `session`=?', array(self::TABLE, $_COOKIE[self::COOKIE]));

        // Validate
        $v = Validator::Create();
        $v->Register(!empty($captcha) && strtoupper($captcha['code']) == strtoupper(Request::Get('captcha')),
                     Validator_Type::IS_TRUE,
                     _T('Validation:Invalid Captcha'));

        // Remove
        $DB->Update('DELETE FROM # WHERE `session`=?', array(self::TABLE, $_COOKIE[self::COOKIE]));
        setcookie(self::COOKIE, null, time() - self::EXPIRES, Config::Get('cookie_path'), Config::Get('cookie_domain'));
    }

    private static function GenerateCode()
    {
        if( Config::Get('flag_captcha_words') )
        {
            $words = file(INCLUDES_DIR . '/words.php');

            // Remove first and last lines
            array_pop($words);
            array_shift($words);

            return strtolower(trim($words[array_rand($words)]));
        }
        else
        {
            $string = '';
            $length = rand(Config::Get('captcha_min_length'), Config::Get('captcha_max_length'));

            for($i = 1; $i <= $length; $i++ )
            {
                $string .= self::$allowed_chars[array_rand(self::$allowed_chars)];
            }

            return strtolower($string);
        }
    }

    private static function Warp($img, $width, $height)
    {
        $center = $width / 2;

        $img2 = imagecreatetruecolor($width, $height);
        $foreground = imagecolorallocate($img2, self::$foreground_color[0], self::$foreground_color[1], self::$foreground_color[2]);
        $background = imagecolorallocate($img2, self::$background_color[0], self::$background_color[1], self::$background_color[2]);
        imagealphablending($img2, true);
        imagefill($img2, 0, 0, $background);

        // periods
        $rand1 = mt_rand(750000,1200000)/10000000;
        $rand2 = mt_rand(750000,1200000)/10000000;
        $rand3 = mt_rand(750000,1200000)/10000000;
        $rand4 = mt_rand(750000,1200000)/10000000;

        // phases
        $rand5 = mt_rand(0,31415926)/10000000;
        $rand6 = mt_rand(0,31415926)/10000000;
        $rand7 = mt_rand(0,31415926)/10000000;
        $rand8 = mt_rand(0,31415926)/10000000;

        // amplitudes
        $rand9 = mt_rand(330,420)/110;
        $rand10 = mt_rand(330,450)/110;

        //wave distortion
        for( $x = 0; $x < $width; $x++ )
        {
            for( $y = 0; $y < $height; $y++ )
            {
                $sx=$x+(sin($x*$rand1+$rand5)+sin($y*$rand3+$rand6))*$rand9-$width/2+$center+1;
                $sy=$y+(sin($x*$rand2+$rand7)+sin($y*$rand4+$rand8))*$rand10;

                if( $sx<0 || $sy<0 || $sx>=$width-1 || $sy>=$height-1 )
                {
                    continue;
                }
                else
                {
                    $color=imagecolorat($img, $sx, $sy) & 0xFF;
                    $color_x=imagecolorat($img, $sx+1, $sy) & 0xFF;
                    $color_y=imagecolorat($img, $sx, $sy+1) & 0xFF;
                    $color_xy=imagecolorat($img, $sx+1, $sy+1) & 0xFF;
                }

                if( $color==255 && $color_x==255 && $color_y==255 && $color_xy==255 )
                {
                    continue;
                }
                else if($color==0 && $color_x==0 && $color_y==0 && $color_xy==0)
                {
                    $newred=self::$foreground_color[0];
                    $newgreen=self::$foreground_color[1];
                    $newblue=self::$foreground_color[2];
                }
                else
                {
                    $frsx=$sx-floor($sx);
                    $frsy=$sy-floor($sy);
                    $frsx1=1-$frsx;
                    $frsy1=1-$frsy;

                    $newcolor=($color*$frsx1*$frsy1+
                               $color_x*$frsx*$frsy1+
                               $color_y*$frsx1*$frsy+
                               $color_xy*$frsx*$frsy);

                    if( $newcolor > 255 )
                    {
                        $newcolor=255;
                    }
                    $newcolor=$newcolor/255;
                    $newcolor0=1-$newcolor;

                    $newred=$newcolor0* self::$foreground_color[0]+$newcolor*self::$background_color[0];
                    $newgreen=$newcolor0* self::$foreground_color[1]+$newcolor*self::$background_color[1];
                    $newblue=$newcolor0* self::$foreground_color[2]+$newcolor*self::$background_color[2];
                }

                imagesetpixel($img2, $x, $y, imagecolorallocate($img2, $newred, $newgreen, $newblue));
            }
        }

        imagedestroy($img);

        return $img2;
    }
}

function imagettfbboxextended($size, $angle, $fontfile, $text)
{
    $bbox = imagettfbbox($size, $angle, $fontfile, $text);

    //calculate x baseline
    if( $bbox[0] >= -1 )
    {
        $bbox['x'] = abs($bbox[0] + 1) * -1;
    }
    else
    {
        $bbox['x'] = abs($bbox[0] + 2);
    }

    //calculate actual text width
    $bbox['width'] = abs($bbox[2] - $bbox[0]);
    if( $bbox[0] < -1 )
    {
        $bbox['width'] = abs($bbox[2]) + abs($bbox[0]) - 1;
    }

    //calculate y baseline
    $bbox['y'] = abs($bbox[5] + 1);

    //calculate actual text height
    $bbox['height'] = abs($bbox[7]) - abs($bbox[1]);
    if( $bbox[3] > 0 )
    {
        $bbox['height'] = abs($bbox[7] - $bbox[1]) - 1;
    }

    return $bbox;
}

?>