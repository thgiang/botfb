<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
use  App\Models\Bot;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/bots/save', 'Api\BotController@save');
Route::get('/bots', 'Api\BotController@index');
Route::get('/bots/logs', 'Api\BotController@logs');
Route::get('/bots/delete', 'Api\BotController@delete');
Route::post('/bots/check', 'Api\BotController@checkLiveCookie');

Route::get('test', function () {
    return 'test';
});
Route::get('systems-token', function (Request $request) {
    $token = $request->token;
    $addNewToken = \App\Models\SystemToken::updateOrCreate([
        'token' => $token
    ], [
        'is_live' => true
    ]);
    if ($addNewToken) {
        return response()->json([
            'status' => 'success',
            'message' => 'Thêm token mới thành công',
            'data' => $addNewToken
        ]);
    }
});

Route::get('/logs', 'Api\LogController@logs');


Route::get('/test', function() {
	$bot = Bot::where('id', 29)->first();
	$fbDtg = getFbDtsg($bot->cookie, $bot->proxy);
	$photoId = uploadImageToFacebook('https://codedao.jas.plus/image_generator/examples/her/19.jpg', $bot->cookie, $fbDtg, 'Hóa thú hok?', $bot->proxy);
	
	// Gửi comment
    $comment = commentPostByCookie($bot->cookie, $fbDtg, '165324818540387', 'Siêu đẹp nha', null, $photoId, $bot->proxy);
	print_r($comment);
	exit();
});