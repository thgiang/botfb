<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BotLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function logs() {
        $logs = BotLog::orderBy('id', 'DESC')->paginate(100);
        return response()->json(['status' => 'success', 'data' => $logs]);
    }
}
