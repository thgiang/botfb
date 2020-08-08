<?php

namespace App\Http\Controllers;

use App\Jobs\BotFacebook;
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
        $allPostReactioned = BotLog::where('bot_id', 1)->pluck('post_id')->toArray();
        print_r($allPostReactioned);
    }
}
