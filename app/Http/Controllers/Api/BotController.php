<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BotController extends Controller
{
    public function new(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cookie' => 'required',
            'frequency' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first(), 'errors' => [$validator->getMessageBag()->toArray()]]);
        }

        $bot = new Bot();
        $bot->cookie = $request->post('cookie');
        $bot->frequency = $request->post('frequency');
        $bot->save();
        return response()->json(['status' => 'success', 'data' => $bot]);
    }
}
