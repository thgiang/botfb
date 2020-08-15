<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Jobs\BotFacebook;
use App\Models\Bot;
use Carbon\Carbon;
use App\Models\WhiteGroupIds;

Route::get('/', function () {
    return view('home');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/test2', 'HomeController@testJob')->name('test2');

Route::get('add', function () {
    return view('bots.add');
});

Route::get('test', function () {
	$imageURL = 'https://tienich.xyz/files/images/blog/5c72b16b70356-33364927_117543379126709_6905426441161146368_o.jpg';
	$parts = explode('.', $imageURL);
	$extension = '';
	if ($parts && count($parts) > 0) {
		$extension = $parts[count($parts) - 1];
	}
	if ($extension != 'jpg' && $extension != 'png') {
		return false;
	}
	
    $curlGetImage = curl_init($imageURL);
    $fileName = public_path() . '/image_generator/downloads/' . rand(0, 10000) . '.'.$extension;
//    $fileName = rand(0, 10000) . '.png';
    $fp = fopen($fileName, 'w+');
    //curl_setopt($curlGetImage, CURLOPT_PROXY, $proxy);
    curl_setopt($curlGetImage, CURLOPT_FILE, $fp);
    curl_setopt($curlGetImage, CURLOPT_HEADER, 0);
    curl_exec($curlGetImage);
    curl_close($curlGetImage);
    fclose($fp);

    // Nếu cần ghi text thì ghi lên và ghi đè biến $fileName, bên dưới sẽ gọi hàm unlink để xóa ảnh đi
	$text = "haha";
    if ($text != null) {
        $fileName = writeTextToImage($fileName, $text);
    }
	
	exit($fileName);
});
