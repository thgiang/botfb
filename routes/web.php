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
Route::get('/test', 'HomeController@testJob')->name('test');

Route::get('add', function () {
    return view('bots.add');
});

Route::get('test', function () {
    $bot = Bot::first();
    print_r(BotFacebook::dispatch($bot->id));
});
