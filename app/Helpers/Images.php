<?php

function LoadImage($imgname)
{
	$extensions = explode(".", $imgname);
	$extension = 'jpg';
	if (count($extensions) > 0) {
		$extension = $extensions[count($extensions) - 1];
	}
	
	try {
		if ($extension && $extension == 'png') {
			$im = imagecreatefrompng($imgname);
		} else {
			$im = imagecreatefromjpeg($imgname);
		}
		return $im;
	} catch (\Exception $e) {
		sendMessageTelegram($imgname. " Lỗi ghép ảnh - text ". $e->getMessage());
        Log::error($imgname . " Lỗi ghép ảnh - text ". $e->getMessage());
		$im = imagecreatefromjpeg('/home/codedao.jas.plus/public_html/public/image_generator/default.jpg');
		return $im;
	}    
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
    $img = LoadImage($originalImage);
    $white = imagecolorallocate($img, 255, 255, 255);
    $borderColor = imagecolorallocate($img, 255, 182, 193);
    $orig_width = imagesx($img);
    $orig_height = imagesy($img);
    $font_path = '/home/codedao.jas.plus/public_html/public/image_generator/SVN-Vandella.otf';

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

    //unlink($originalImage);
    return $fileName;
}
