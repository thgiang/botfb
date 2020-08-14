<?php

function LoadJpeg($imgname)
{
    $im = @imagecreatefromjpeg($imgname);
    if (!$im) {
        $im = imagecreatetruecolor(150, 30);
        $bgc = imagecolorallocate($im, 255, 255, 255);
        $tc = imagecolorallocate($im, 0, 0, 0);
        imagefilledrectangle($im, 0, 0, 150, 30, $bgc);
        imagestring($im, 1, 5, 5, 'Error loading ' . $imgname, $tc);
    }
    return $im;
}

function randomPic($dir = 'images')
{
    if (!is_dir($dir)) {
        return '';
    }

    $files = glob($dir . '/*.*');
    $file = array_rand($files);
    return $files[$file];
}

function writeTextToImage($originalImage, $text = "Hello")
{
    $img = LoadJpeg($originalImage);

    $white = imagecolorallocate($img, 255, 255, 255);
    $borderColor = imagecolorallocate($img, 255, 182, 193);
    $orig_width = imagesx($img);
    $orig_height = imagesy($img);
    $font_path = '/home/codedao.jas.plus/public_html/public/SVN-Vandella.otf';

    // Add text to image
    $font_size = $orig_width / 3 / mb_strlen($text) * 3;
    $bbox = imagettfbbox($font_size, 0, $font_path, $text);
    $textWidth = $bbox[2] - $bbox[0];
    $textHeight = ($bbox[1] - $bbox[5]) * 2;
    $xOffset = ($orig_width - $textWidth) / 2;
    $yOffset = $textHeight / 2;

    imagettftext($img, $font_size, 0, $xOffset + 1, $yOffset + 0, $borderColor, $font_path, $text);
    imagettftext($img, $font_size, 0, $xOffset - 1, $yOffset - 0, $borderColor, $font_path, $text);
    imagettftext($img, $font_size, 0, $xOffset + 0, $yOffset + 1, $borderColor, $font_path, $text);
    imagettftext($img, $font_size, 0, $xOffset - 0, $yOffset - 1, $borderColor, $font_path, $text);
    imagettftext($img, $font_size, 0, $xOffset, $yOffset, $white, $font_path, $text);

    // Random ra tên file, kiểm tra xem có file đó chưa, chưa có thì dùng tên này
    do {
        $fileName = 'img_' . rand(11111, 99999) . '.jpg';
    } while (file_exists($fileName));
    imagejpeg($img, $fileName);

//    unlink($originalImage);
    return $fileName;
}
