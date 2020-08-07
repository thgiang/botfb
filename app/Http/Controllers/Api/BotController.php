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
            'name' => 'required',
            'frequency' => 'required|numeric',
            'proxy' => 'required',
            'comment_sticker_collection' => 'nullable',
            'comment_on' => 'required|boolean',
            'start_time' => 'required|numeric|min:0|max:23',
            'end_time' => 'required|numeric|min:0|max:23',
            'reaction_type' => 'required|numeric',
            'bot_target' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first(), 'errors' => [$validator->getMessageBag()->toArray()]]);
        }

        $bot = Bot::create($request->all());
        return response()->json(['status' => 'success', 'data' => $bot]);
    }

    public function index(Request $request)
    {
        return Bot::get();
    }

    public function delete(Request $request)
    {
        Bot::where('id', $request->id)->delete();
        return "Đã xóa bot ID " . $request->id;
    }
}
