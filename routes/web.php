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

//    $bots = Bot::where('error_log', 'LIKE', '%Proxy của tài khoản bị die, không lấy được proxy mới,%')->update([
//        'proxy' => null
//    ]);

//    $bots = Bot::all();
//    $botUnique = $bots->unique('proxy');
//    $botsDuplicate = $bots->diff($botUnique);
//    foreach ($botsDuplicate as $botDuplicate) {
//        Bot::where('id', $botDuplicate->id)->update([
//            'proxy' => null
//        ]);
//    }
//    return $botsDuplicate;

//    $allProxies = \App\Models\SystemProxy::get();
//    $freeProxies = $allProxies->where('bot_id', 0);

//    $proxiesAreUsingInProxyTable = \App\Models\SystemProxy::where('bot_id', '!=', 0)->pluck('proxy')->toArray();
//    $proxiesAreUsingInBotsTable = Bot::where('proxy', '!=', null)->pluck('proxy')->toArray();
//    $proxiesNotFreeButNotWorkInBotsTable = array_diff($proxiesAreUsingInProxyTable, $proxiesAreUsingInBotsTable);
//    echo "Có " . count($proxiesAreUsingInProxyTable) . " proxy đang làm việc trong bảng proxy <br>";
//    echo "Có " . count($proxiesAreUsingInBotsTable) . " proxy đang làm việc trong bảng bots <br>";

//    $errorBots = Bot::get();
//    return $errorBots->count();
    return getPostsFromGroup("sb=_Z1EX4dy3P0T-RuJ6DZuukWA; datr=_Z1EX2-WrVgSKj2a8foM7dZU; locale=vi_VN; c_user=100010388822828; xs=30%3AB0PJv-H1Nopp9A%3A2%3A1598336383%3A12879%3A6191; spin=r.1002564417_b.trunk_t.1598336384_s.1_v.2_; fr=1hfYUSRIR0YIBCNsd.AWU0Ks9eKhO1s1xIQ1ItNquPqP8.BfRJ39.Np.F9E.0.0.BfRK2G.AWULqfdc; wd=704x937; presence=EDvF3EtimeF1598336467EuserFA21B10388822828A2EstateFDutF1598336467603CEchF_7bCC", "1598779013494485", "103.121.89.89:680");
});
