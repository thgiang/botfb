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
	$group_id = '2181665685234594';
     // Lấy 1 con bot đang coi group này là white_group với điều kiện nó phải join rồi để quét đc bài mới
        $bots = WhiteGroupIds::where('fb_id', $group_id)->get();

        $foundBot = null;
        foreach ($bots AS $bt) {
			$bot = Bot::where('id', $bt->id)->first();
			echo $bot->cookie .'<br><br>';
            //if ($bot && checkCookieJoinedGroup($bot->cookie, $group_id, $bot->proxy)) {
				$foundBot = $bot;
                break;
            //}
        }
        if ($foundBot == null) {
           echo "WARNING: Đang quét bài mới của group " . $group_id . " nhưng ko có bot nào quét đc bài viết của group này";
            return;
        } else {
			 $posts = getPostsFromGroup($bot->cookie, $group_id, $bot->proxy);
			 print_r($posts);
		}
		exit();
});
