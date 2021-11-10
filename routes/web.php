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

    // Những bot die 2 ngày mà chưa update, lấy list để hỏi thằng kia đống này còn chạy không
//    $bots = Bot::where('count_error', 10)->where('updated_at', '<=', Carbon::now()->subDays(1))->get();
//    foreach ($bots as $bot) {
//        echo $bot->facebook_uid . "|" . $bot->error_log . "|" . $bot->updated_at . "<br>";
//
//    }
//    return $bots;
//    print_r(checkProxy("171.240.11.154:41821"));

	function testIP($proxy) {
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://jas.plus/ip",
			CURLOPT_PROXY => $proxy,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
		));
		$response = curl_exec($curl);
		echo "Truyền vào proxy: ".$proxy." => Nhận về ".$response." <br><br>";
	}

	testIP("103.121.89.89:3128");

//    print_r(getFbDtsg("c_user=100004791424629;spin=r.1002592822_b.trunk_t.1598714009_s.1_v.2_;datr=mXBKX1Rrj8bs671_dHAH9yQR;sb=mXBKX8pwT432w983Ug0VHQnt;xs=5%3AwfCeKY0_PlxfMA%3A2%3A1597291926%3A8954%3A6157%3A%3AAcVJjAC6nI9tlu6JnEgMMM4qhOljaitG_WhDPaMaxA;fr=1p3VA0Use9E7DgOtZ.AWXMuOSfD7wGsfObE59_-4XFxCk.BezeKh.zD.F9K.0.0.BfSn_l.AWXUCmTY;wd=929x888;presence=EDvF3EtimeF1598717923EuserFA21B04791424629A2EstateFDutF1598717923866CEchF_7bCC;|Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.135 Safari/537.36", "172.104.62.206:564"));
});
