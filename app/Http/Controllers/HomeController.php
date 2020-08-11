<?php

namespace App\Http\Controllers;

use App\Jobs\BotFacebook;
use App\Models\Bot;
use App\Models\BotLog;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function testJob() {
//		$bot = Bot::where('count_error', '<', config('bot.max_try_time'))->first();
//		$fbDtg = getFbDtsg($bot->cookie, $bot->proxy);
//		$photoId = uploadImageToFacebook("https://www.upsieutoc.com/images/2020/07/31/592ccac0a949b39f058a297fd1faa38e.md.jpg", $bot->cookie, $fbDtg, $bot->proxy);
//		if ($photoId) {
//			echo "Up ảnh ".$photoId." lên FB, dùng cookie ".$bot->cookie.", dtsg = ".$fbDtg." proxy: ".$bot->proxy;
//		} else {
//			echo 'Up ảnh ko thành công';
//		}
//		exit();
    }
}
