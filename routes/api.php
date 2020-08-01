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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/bot/new', 'Api\BotController@new');
Route::get('/bots', 'Api\BotController@index');
Route::get('/bot/logs', 'Api\BotController@logs');
Route::get('/bot/delete', 'Api\BotController@delete');


Route::get('/logs', 'Api\LogController@logs');
