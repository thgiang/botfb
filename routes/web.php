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
    $check = checkCookieJoinedGroup("dpr=1.75;wd=2195x1093;datr=_-0rXxRS8lA7RaNFB3TO3zRI;locale=vi_VN;sb=qVwqX6vBbcNdmfDA9s8nM93j;spin=r.1002521727_b.trunk_t.1597465652_s.1_v.2_;c_user=100021067710865;xs=1%3Apfsj3Kqu1TjxSw%3A2%3A1597209680%3A10895%3A6243%3A%3AAcVGE9-vehjlKOtMob6i8CDmuts-y61G02JXL2IC4OE;fr=1Z57qNW2xLQ7ztNcu.AWXLIJCwXz0-LPtf9jS5aG2tBCA.BfKlyo.2O.F83.0.0.BfN3Dh.AWVc_1k3;presence=EDvF3EtimeF1597469044EuserFA21B21067710865A2EstateFDutF1597469044320CEchF_7bCC;", "772242516245607", "103.121.89.89:536");
    if ($check) {
        return "joined";
    } else {
        return "not";
    }
});
