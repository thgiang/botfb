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
//    $cookie = 'c_user=100048847779968;locale=vi_VN;wd=2195x1093;m_pixel_ratio=1.75;fr=1KxwZ6bAHsR693gwc.AWVGTsysEFcQU0WL2dAnc6zTrsQ.BfPqyr.HW.F9B.0.0.BfQjDW.AWXZ94S5;sb=q6w-X0IkJW12l8xCtTGVWWcR;datr=q6w-XxM0GBYm_44W-l9PkUV-;dpr=1.75;xs=23%3AWbZ_-Dc--LBChQ%3A2%3A1598173398%3A530%3A6194;spin=r.1002557702_b.trunk_t.1598173400_s.1_v.2_;presence=EDvF3EtimeF1598173402EuserFA21B48847779968A2EstateFDutF1598173402908CEchF_7bCC;=undefined;';
//    $proxy = "103.121.89.89:675";
//
//    $fb = getFbDtsg($cookie, $proxy);
//    if (!$fb) {
//        exit("Chet cookie");
//    }
//
//    print_r(reactionPostByCookie($cookie, $fb, "2639785719571784", 6, $proxy));


//    $test = new \App\Http\Controllers\Api\TokenController();
//    return $test->maintainSystemTokens();

    $accountsNotHaveProxy = Bot::where('is_active', true)->where('proxy', null)->get();
    return $accountsNotHaveProxy;
});
