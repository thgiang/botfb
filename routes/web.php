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
//    $imageURL = 'https://www.upsieutoc.com/images/2020/08/14/Meo-tai-cp29551b74f7b9ecde.png';
//    $curlGetImage = curl_init($imageURL);
//    $text = 'Nguyễn Trung';
//    $fileName = public_path() . '/image_generator/downloads/' . rand(0, 10000) . '.png';
//    $fp = fopen($fileName, 'w+');
//    curl_setopt($curlGetImage, CURLOPT_FILE, $fp);
//    curl_setopt($curlGetImage, CURLOPT_HEADER, 0);
//    curl_exec($curlGetImage);
//    curl_close($curlGetImage);
//    fclose($fp);
//
//    // Nếu cần ghi text thì ghi lên và ghi đè biến $fileName, bên dưới sẽ gọi hàm unlink để xóa ảnh đi
//    if ($text != null) {
//        $fileName = writeTextToImage($fileName, $text);
//    }
//    echo 'https://codedao.jas.plus/' . $fileName;
//    exit();
});
