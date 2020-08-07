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
            'cookie' => 'required|unique:bots,cookie',
            'name' => 'required',
            'proxy' => 'required',
            'comment_on' => 'required|boolean',
            'reaction_on' => 'required|boolean',
            'start_time' => 'required|numeric|min:0|max:23',
            'end_time' => 'required|numeric|min:0|max:23',
            'reaction_type' => 'required|numeric',
            'bot_target' => 'required'
        ], [
            'cookie.unique' => 'Cookie bạn vừa nhập đã tồn tại trong hệ thống, vui lòng kiểm tra lại!'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first(), 'errors' => [$validator->getMessageBag()->toArray()]]);
        }

        // Kiểm tra proxy hoạt động ok không
        $tryTestProxy = 0;
        do {
            if ($tryTestProxy >= 3) {
                return response()->json(['status' => 'error', 'message' => 'Proxy không hoạt động, vui lòng kiểm tra lại!']);
            }
            $checkProxy = checkProxy($request->proxy);
            $tryTestProxy++;
        } while ($checkProxy == false);


        $bot = Bot::create($request->all());
        return response()->json(['status' => 'success', 'data' => $bot, 'message' => 'Tạo bot thành công, ID: ' . $bot->id]);
    }

    public function index(Request $request)
    {
        return Bot::paginate(15);
    }

    public function delete(Request $request)
    {
        Bot::where('id', $request->id)->delete();
        return "Đã xóa bot ID " . $request->id;
    }
}
