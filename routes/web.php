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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/test2', 'HomeController@testJob')->name('test2');

Route::get('add', function () {
    return view('bots.add');
});

Route::get('test', function () {
    $text = "{icon}{icon} {name} {gio}/{phut} {icon}{icon}";
//    $parts = explode('{icon}', $text);
//    $text = "";
//    foreach ($parts as $part) {
//        $text .= $part . ' '. RandomEmotion();
//    }
//    $nowHour = date('H');
    $nowHour = 20;
    $bot = Bot::where('run_time', 'LIKE', '%' . $nowHour . '%')->first();
    return $bot;
});
