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

Route::get('proxies', function () {
    function getProxies()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://proxyaz.com/api/6fbGkqVxpKM2L8FvRZc1zy4d3mjCwJBD/getproxy",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET"
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

//    echo '<pre>';
    $getProxies = getProxies();
    foreach ($getProxies->list as $getProxy) {
        \App\Models\SystemProxies::updateOrCreate(
            [
                'proxy' => $getProxy->host . ':' . $getProxy->port
            ],
            [
                'expired' => $getProxy->unixtime_end
            ]);
        echo 'Update thành công ' . $getProxy->host . ':' . $getProxy->port . '<br>';
    }
});

Route::get('test', function () {
});
