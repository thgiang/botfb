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
use App\Models\WhiteGroupId;
use App\Helpers\ZHelper;

Route::get('/', function () {
    return view('home');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/test2', 'HomeController@testJob')->name('test2');

Route::get('add', function () {
    return view('bots.add');
});

Route::get('proxies', 'Api\ProxyController@maintainProxies');

Route::get('test', function () {
    $cookie = 'c_user=100051886094905; datr=UcA-X25mBq9aneQWpeyGs6O-; sb=UcA-XxfRIs58p_eDVGJrQy-n; wd=1920x969; spin=r.1002557241_b.trunk_t.1598085774_s.1_v.2_; xs=6%3AxT6tWobOVKEp9A%3A2%3A1592403617%3A12830%3A16305%3A%3AAcX5tUijWGEKb1FyELB6Z-YRdjslf1yCwW44ncrPPIM; fr=14Psz8X8GNO9SKy9m.AWXAL4i4_s4ow-6NaCtmagXmo78.Be6iaL.Pv.F8-.0.0.BfQV6_.AWXldNZD; presence=EDvF3EtimeF1598119619EuserFA21B51886094905A2EstateFDutF1598119619718Et3F_5bDiFA2thread_3a4248094185265538A2ErF1EoF1EfF1C_5dElm3FA2thread_3a4248094185265538A2Eutc3F1598119619392CEchF_7bCC; useragent=TW96aWxsYS81LjAgKFdpbmRvd3MgTlQgMTAuMDsgV2luNjQ7IHg2NCkgQXBwbGVXZWJLaXQvNTM3LjM2IChLSFRNTCwgbGlrZSBHZWNrbykgQ2hyb21lLzg0LjAuNDE0Ny4xMzUgU2FmYXJpLzUzNy4zNg%3D%3D; _uafec=Mozilla%2F5.0%20(Windows%20NT%2010.0%3B%20Win64%3B%20x64)%20AppleWebKit%2F537.36%20(KHTML%2C%20like%20Gecko)%20Chrome%2F84.0.4147.135%20Safari%2F537.36;';
    $fb_id = "348314143204322";
    $proxy = "103.121.89.89:580";

    print_r(getPostsFromGroup($cookie, $fb_id, $proxy));
    exit();
});
